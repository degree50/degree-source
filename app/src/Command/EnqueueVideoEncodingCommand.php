<?php

namespace App\Command;

use App\Domain\Video\Repository\VideoRepository;
use App\VideoEncoding\Message\WebEncodingTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class EnqueueVideoEncodingCommand extends Command
{
    protected static $defaultName = 'app:enqueue-video-encoding';

    public function __construct(
        private readonly MessageBusInterface $messageBusInterface,
        private readonly VideoRepository     $videoRepository
    )
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Enqueue a video for encoding')
            ->addArgument('video', InputArgument::REQUIRED, 'The Video UUID to queue for encoding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $videoUuid = $input->getArgument('video');

        $video = $this->videoRepository->find($videoUuid);

        if (!$video) {
            $io->error('The video with UUID ' . $videoUuid . ' cannot be found.');
            return 1;
        }

        $this->messageBusInterface->dispatch(new WebEncodingTask($videoUuid, $video->getEncodedVideoDirectory()));
        $io->success('Video dispatched for encoding. Now you need to run the worker!');

        return 0;
    }
}
