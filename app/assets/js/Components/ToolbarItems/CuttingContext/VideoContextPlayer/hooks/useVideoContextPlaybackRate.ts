import { useEffect, useState } from 'react'
import VideoContext from 'videocontext'

const useVideoContextPlaybackRate = (videoContext?: VideoContext) => {
    const [playbackRate, setPlaybackRate] = useState<number>(1)

    useEffect(() => {
        if (videoContext) {
            // eslint-disable-next-line functional/immutable-data
            videoContext.playbackRate = playbackRate
        }
    }, [videoContext, playbackRate])

    return { playbackRate, setPlaybackRate }
}

export default useVideoContextPlaybackRate
