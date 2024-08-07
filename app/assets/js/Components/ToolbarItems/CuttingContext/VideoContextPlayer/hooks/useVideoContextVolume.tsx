import { useCallback, useEffect, useState } from 'react'
import VideoContext from 'videocontext'

const useVideoContextVolume = (videoContext?: VideoContext, initialVolume = 1) => {
    const [volume, setVolume] = useState<number>(initialVolume)
    const [isMuted, setIsMuted] = useState<boolean>(false)

    // The side effect to set the volume of the VideoContext
    useEffect(() => {
        if (videoContext) {
            const newVolume = isMuted ? 0 : volume
            // eslint-disable-next-line functional/immutable-data
            videoContext.volume = newVolume
        }
    }, [volume, isMuted, videoContext])

    const toggleIsMuted = useCallback(() => {
        setIsMuted(!isMuted)
    }, [isMuted])

    return { volume, setVolume, isMuted, setIsMuted, toggleIsMuted }
}

export default useVideoContextVolume
