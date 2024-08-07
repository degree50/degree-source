import VideoJSPlayer from 'Components/VideoPlayer/VideoJSPlayer'
import React from 'react'
import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'

type Props = {
    videoConfig?: Video
}

const VideoCutSolutionVideo: React.FC<Props> = (props) => {
    if (props.videoConfig === undefined) {
        return <p>No solution, yet!</p>
    }

    // There are cut videos in mp4 format that have been created before we started to cut to hls as well.
    const hasVideoFile = props.videoConfig?.url?.hls ?? props.videoConfig?.url?.mp4 ?? false

    return hasVideoFile ? (
        <VideoJSPlayer
            videoJsOptions={{
                autoplay: false,
                controls: true,
            }}
            videoMap={props.videoConfig}
        />
    ) : (
        <p>Das geschnittene Video wird noch gespeichert</p>
    )
}

export default React.memo(VideoCutSolutionVideo)
