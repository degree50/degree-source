<?php

namespace App\VideoEncoding\Service;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use App\Domain\Video\Model\Video;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\FileSystem\FileSystemService;
use App\VideoEncoding\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This service handles the encoding of video files.
 *
 * For now this service is pretty specific on what (few) codecs and formats it supports.
 */
class EncodingService
{
    const array CONFIG = [
        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
        'ffprobe.binaries' => '/usr/bin/ffprobe',
        'timeout' => 3600 * 3, // The timeout for the underlying process -> 3 hours
        'ffmpeg.threads' => 12,   // The number of threads that FFmpeg should use
    ];

    private FFMpeg $ffmpeg;
    private FFProbe $ffprobe;

    public function __construct(
        private readonly LoggerInterface       $logger,
        private readonly ParameterBagInterface $parameterBag,
        private readonly FileSystemService     $fileSystemService
    )
    {
        $this->ffmpeg = FFMpeg::create(self::CONFIG, $this->logger);
        $this->ffprobe = FFProbe::create(self::CONFIG, $logger);
    }

    /**
     * This is the initial encoding we create, when a video is uploaded in the Mediathek.
     *
     * We encode the video to a baseline MP4 file with two audio streams.
     *   - The first audio stream is the original audio OR a silent audio if no audio was present in the original video
     *   - The second audio stream (if available) is the audio description
     */
    public function encodeMP4WithAudioDescription(Video $video, string $localOutputDirectory): void
    {
        $inputVideoFileName = $this->fileSystemService->localPath($video->getUploadedVideoFile());
        $inputAudioDescriptionFileName = empty($video->getUploadedAudioDescriptionFile()->getVirtualPathAndFilename())
            ? null
            : $this->fileSystemService->localPath($video->getUploadedAudioDescriptionFile());

        $this->logger->info("Start encoding MP4 of file <$inputVideoFileName>.");

        /**
         * Build ffmpeg command.
         * Why:
         *   We use a custom ffmpeg command, because we are unable to model our needs with the \FFMpeg\FFMpeg package.
         *   We build the command as an array, because we want to thoroughly comment the options.
         *
         * The command will be built and executed by the Driver\FFMpegDriver.
         */

        $inputs = [
            '-y', // overwrite previous outputs
            '-i', "$inputVideoFileName",
        ];

        // mappings look like this "-map x:y:z"
        // x: input file index
        // y: stream type (v: video, a: audio)
        // z: stream index in the input file
        $inputMapping = [
            '-map', '0:v'
        ];

        $hasOriginalAudio = $this->hasAudio($inputVideoFileName);

        if ($hasOriginalAudio) {
            // map original audio stream
            $inputMapping[] = '-map';
            $inputMapping[] = '0:a:0';
        } else {
            // add silent audio stream as input
            $inputs[] = '-f';
            $inputs[] = 'lavfi';
            $inputs[] = '-i';
            $inputs[] = 'anullsrc';

            // map silent audio stream
            $inputMapping[] = '-map';
            $inputMapping[] = '1:a:0';
        }

        // add audio description stream if available
        if ($inputAudioDescriptionFileName) {
            $this->logger->info("Adding AudioDescriptions of file <$inputAudioDescriptionFileName> to MP4");

            // add audio description stream as input
            $inputs[] = '-i';
            $inputs[] = $inputAudioDescriptionFileName;

            // map audio description stream
            $inputMapping[] = '-map';
            // if original video has no original audio, we have a additional input file (silent audio)
            // so the added Audio Descriptions are at input file index 2, because now the silent audio is at index 1
            $inputFileIndex = $hasOriginalAudio ? 1 : 2;
            $inputMapping[] = "$inputFileIndex:a:0";
        }

        $conversion = [
            '-c:v', 'libx264', // convert video stream to h264
            // WHY: We use aac over libmp3lame as the latter produced unplayable audio streams for videoJS (m4a).
            // Error msg: "VIDEOJS: ERROR: DOMException: Failed to execute 'addSourceBuffer' on 'MediaSource': The type provided ('audio/mp4;codecs="mp4a.40.34"') is unsupported."
            '-c:a', 'aac', // convert all audio streams to AAC
            '-shortest', // the new duration will be determined by the shortest input
        ];

        $outputTarget = [
            "$localOutputDirectory/x264.mp4"
        ];

        $finalCommand = array_merge($inputs, $inputMapping, $conversion, $outputTarget);
        $this->ffmpeg->getFFMpegDriver()->command($finalCommand);
        $this->logger->info("Finished encoding MP4 of file <$inputVideoFileName> to $localOutputDirectory/x264.mp4");
    }

    /**
     * @param $cutList ServerSideCut[]
     * @return string[]
     */
    public function createTemporaryMp4ClipsFromCutList(VirtualizedFile $directory, array $cutList, string $originalVideoFilePath): array
    {
        $tempDirectory = $this->fileSystemService->localPath($directory);

        return array_map(function (ServerSideCut $cut) use ($tempDirectory, $originalVideoFilePath) {
            $clipUuid = Uuid::uuid4()->toString();
            $this->logger->info("Creating new intermediate clip with ID $clipUuid");

            $clipPath = "$tempDirectory/$clipUuid.mp4";

            // ffmpeg can work with milliseconds and seeks to the nearest available frame
            $offset = round($cut->offset, 3);
            $videoDurationInSeconds = $cut->getDuration();

            $duration = max(1, $videoDurationInSeconds);

            $this->logger->info('url: ' . $originalVideoFilePath);
            $this->logger->info('offset: ' . TimeCode::fromSeconds($offset)->toTimeString());
            $this->logger->info('duration: ' . TimeCode::fromSeconds($duration)->toTimeString());
            $this->logger->info('clipPath: ' . $clipPath);

            $command = [
                '-y', // overwrite previous outputs
                '-ss', "$offset", // seek to start offset (seconds) (see https://trac.ffmpeg.org/wiki/Seeking)
                '-i', "$originalVideoFilePath", // input video
                '-t', "$duration", // duration of cut in seconds
                '-map', 'v', // map all video streams
                '-map', 'a', // map all audio streams
                "$clipPath",
            ];

            $finalCommand = array_merge($command);
            $this->ffmpeg->getFFMpegDriver()->command($finalCommand);
            $this->logger->info("Finished encoding MP4 clip <$clipUuid> to $tempDirectory");

            return $clipPath;

        }, $cutList);
    }

    /**
     * Concatenate mp4 video clips to a new mp4 file
     * using the "playlist file method" described here (https://trac.ffmpeg.org/wiki/Concatenate).
     *
     * @param array $clipPaths clip file paths
     * @param string $outputDirectory
     * @return string The result video file path
     */
    public function concatMp4Clips(array $clipPaths, string $outputDirectory): string
    {
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }

        $mp4Url = "$outputDirectory/x264.mp4";

        $tmpPlaylistPath = "$outputDirectory/_clips.txt";
        $tmpPlaylistContent = array_reduce($clipPaths, function ($playlist, $clipPath) {
            return $playlist . "file '$clipPath'\n";
        });

        $fileSystem = new Filesystem();
        file_put_contents($tmpPlaylistPath, $tmpPlaylistContent);

        $concatCommand = [
            '-y',
            '-f', 'concat', // use concat via file
            '-safe', '0', // allow unsafe file names (we use absolute paths and uuids)
            '-i', "$tmpPlaylistPath", // input playlist as text file
            '-map', 'v', // map all video streams
            '-map', 'a', // map all audio streams
            $mp4Url,
        ];

        $finalCommand = array_merge($concatCommand);

        $this->ffmpeg->getFFMpegDriver()->command($finalCommand);

        $this->logger->info("Finished concatenating MP4 clips to '$mp4Url'");

        // remove temporary playlist file
        if ($fileSystem->exists($tmpPlaylistPath)) {
            $fileSystem->remove($tmpPlaylistPath);
        }

        return $mp4Url;
    }

    public function encodeHLS(string $inputVideoFileName, string $outputDirectory): void
    {
        $this->logger->info("Start encoding HLS of file <$inputVideoFileName>");

        /**
         * We count the input video (generated mp4 @see $encodeMP4WithAudioDescription) to determine
         * whether we have just a single audio stream or both original audio and audio description streams.
         */
        $hasAudioDescriptions = $this->hasAudioDescriptions($inputVideoFileName);

        $this->logger->info("Found audio description stream in '$inputVideoFileName': " . $hasAudioDescriptions ? "Yes" : "No");

        /**
         * Build ffmpeg command.
         * Why: @see encodeMP4WithAudioDescription
         */

        $inputMapping = [
            '-y', // overwrite previous outputs
            '-i', "$inputVideoFileName", // video input (contains the prepared video with two audio streams)
            '-map', '0:v', // map video to video stream #0
            '-map', '0:v', // map video to video stream #1
            '-map', '0:v', // map video to video stream #2
            '-map', '0:a:0', // map first audio to audio stream #0
        ];

        // WHY: conditional parameters depending on presence of additional audio (description) stream
        if ($hasAudioDescriptions) {
            // map second audio to audio stream #1 IF it is available\
            $inputMapping[] = '-map';
            $inputMapping[] = '0:a:1';
            // This map describes the structure of the streams.
            // We have 3 different video stream that use the audio of audio_group "audio" and two audio streams in that audio group.
            // Note: unfortunately we can not set metadata like TITLE here. The name:XXX prop is only used for file naming.\
            $inputMapping[] = '-var_stream_map';
            $inputMapping[] = 'v:0,name:360p,agroup:audio v:1,agroup:audio,name:720p v:2,agroup:audio,name:1080p a:0,agroup:audio,name:Original,language:DE,default:YES a:1,agroup:audio,name:Descriptions,language:DE,default:NO';
        } else {
            // This map describes the structure of the streams.
            // We have 3 different video stream that use the audio of audio_group "audio" and one audio stream in that audio group.
            // Note: unfortunately we can not set metadata like TITLE here. The name:XXX prop is only used for file naming.\
            $inputMapping[] = '-var_stream_map';
            $inputMapping[] = 'v:0,agroup:audio,name:360p v:1,agroup:audio,name:720p v:2,agroup:audio,name:1080p a:0,agroup:audio,name:Original,language:DE,default:YES';
        }

        // convert streams to wanted format.
        $encoding = [
            // encode videos to h264 (again) (this has to be done for the "-filter:v:x" option to work)
            '-c:v:0', 'libx264',
            '-c:v:1', 'libx264',
            '-c:v:2', 'libx264',
            '-c:a', 'copy', // copy codec of any audio stream (we assume all audio streams already are encoded correctly because we create the baseline mp4 @see encodeMP4WithAudioDescription
        ];

        $preConversionHlsOptions = [
            '-bf', '1',
            '-g', '48',
            '-keyint_min', '48',
            '-sc_threshold', '0',
            '-hls_list_size', '0',
            '-hls_time', '10',
            '-hls_allow_cache', '1',
        ];

        $lowResStream = [
            '-b:v:0', '1M',
            '-filter:v:0', 'scale=640:360:force_original_aspect_ratio=decrease,pad=640:360:(ow-iw)/2:(oh-ih)/2',
        ];

        // medium res video stream rendition
        $mediumResStream = [
            '-b:v:1', '2M',
            '-filter:v:1', 'scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2',
        ];

        // high res video stream rendition
        $highResStream = [
            '-b:v:2', '4M',
            '-filter:v:2', 'scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2',
        ];

        $generalHlsOptions = [
            '-strict', '-2',
            '-master_pl_name', 'hls.m3u8', // set the name of the master playlist
            '-hls_playlist_type', 'vod', // set playlist type to video on demand
            '-hls_segment_filename', "$outputDirectory/hls_%v_%04d.ts",
            "$outputDirectory/hls_%v.m3u8",
        ];

        $finalCommand = array_merge($inputMapping, $encoding, $preConversionHlsOptions, $lowResStream, $mediumResStream, $highResStream, $generalHlsOptions);

        $this->ffmpeg->getFFMpegDriver()->command($finalCommand);

        /**
         * WHY: In order to enable the videoJS player to show the correct names of the audio streams
         *      we have to change them in the master playlist file (hls.m3u8) as we are currently
         *      unable to set them correctly with ffmpeg.
         *
         *      We read the generated master playlist file into memory and search/replace the names
         *      from "audio_3" and "audio_4" to "Original" and "Deskription" respectively.
         *
         *      As of now we are unsure if the auto generated names by ffmpeg are consistent so we
         *      use a regex to accommodate for potential changes of the digit (e.g. "audio_1" or "audio23").
         *
         *      We also add the "CHARACTERISTICS" metadata "public.accessibility.describes-video" to the
         *      audio descriptions stream to indicate it's purpose to the videoJS player and thus to the user.
         */
        $masterPlaylist = file_get_contents("$outputDirectory/hls.m3u8");

        $masterPlaylist = preg_replace('/NAME="audio_\d+"/', 'NAME="Original"', $masterPlaylist, 1);
        $masterPlaylist = preg_replace('/NAME="audio_\d+"/', 'NAME="Deskription"', $masterPlaylist, 1);
        $masterPlaylist = preg_replace('/hls_Descriptions.m3u8"/', 'hls_Descriptions.m3u8",CHARACTERISTICS="public.accessibility.describes-video"', $masterPlaylist, 1);

        file_put_contents("$outputDirectory/hls.m3u8", $masterPlaylist);

        $this->logger->info("Finished encoding HLS of file <$inputVideoFileName>");
    }

    public function createPreviewImage(string $inputVideoFilename, string $localOutputDirectory, float $videoDuration): void
    {
        $this->logger->info("Create preview image for <$inputVideoFilename>");

        $ffmpegVideo = $this->ffmpeg->open($inputVideoFilename);
        $frame = $ffmpegVideo->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($videoDuration * 0.25));
        $frame->save("$localOutputDirectory/thumbnail.jpg");

        $this->logger->info("Finished creation of preview image for <$inputVideoFilename>");
    }

    public function probeForVideoDuration(string $filePath)
    {
        return $this->ffprobe
            ->format($filePath) // extracts file information
            ->get('duration');
    }

    private function countAudioStreams(string $filePath): int
    {
        return $this->ffprobe
            ->streams($filePath)
            ->audios()
            ->count();
    }

    private function hasAudio(string $filePath): bool
    {
        return $this->countAudioStreams($filePath) > 0;
    }

    private function hasAudioDescriptions(string $filePath): bool
    {
        // if there are 2 audio streams, we have audio descriptions
        return $this->countAudioStreams($filePath) > 1;
    }
}
