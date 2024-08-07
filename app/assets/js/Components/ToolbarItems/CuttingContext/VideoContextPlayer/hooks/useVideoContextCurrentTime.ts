import { useCallback, useEffect, useState } from 'react'
import VideoContext from 'videocontext'

const useVideoContextCurrentTime = (videoContext?: VideoContext) => {
    const [internalCurrentTime, setInternalCurrentTime] = useState<number>(0)

    // WHY: source of truth is VideoContext
    useEffect(() => {
        if (videoContext) {
            setInternalCurrentTime(videoContext.currentTime)

            // this callback sets internal currentTime on every VideoContext UPDATE event
            videoContext.registerCallback(VideoContext.EVENTS.UPDATE, function () {
                setInternalCurrentTime(videoContext.currentTime)
            })

            videoContext.registerCallback(VideoContext.EVENTS.ENDED, function () {
                // eslint-disable-next-line functional/immutable-data
                videoContext.currentTime = 0
            })
        }
    }, [videoContext, setInternalCurrentTime])

    const setCurrentTime = useCallback(
        (value: number) => {
            if (videoContext) {
                // eslint-disable-next-line functional/immutable-data
                videoContext.currentTime = value
            }
        },
        [videoContext]
    )

    return { currentTime: internalCurrentTime, setCurrentTime }
}

export default useVideoContextCurrentTime
