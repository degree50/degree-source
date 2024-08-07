import { useCallback, useEffect, useState } from 'react'
import VideoContext from 'videocontext'

const useVideoContextPlayback = (videoContext?: VideoContext) => {
    const [isPaused, setIsPaused] = useState<boolean>(true)

    // register "on ended" callback
    useEffect(() => {
        if (videoContext) {
            videoContext.registerCallback(VideoContext.EVENTS.ENDED, function () {
                setIsPaused(true)
            })
        }
    }, [videoContext])

    useEffect(() => {
        if (videoContext) {
            if (isPaused) {
                videoContext.pause()
            } else {
                videoContext.play()
            }
        }
    }, [videoContext, isPaused])

    const toggleIsPaused = useCallback(() => {
        if (videoContext) {
            setIsPaused(!isPaused)
        } else {
            // prevent change before VideoContext is available
            setIsPaused(true)
        }
    }, [videoContext, isPaused, setIsPaused])

    return { isPaused, setIsPaused, toggleIsPaused }
}

export default useVideoContextPlayback
