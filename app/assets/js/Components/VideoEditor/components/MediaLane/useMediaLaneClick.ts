import { useCallback } from 'react'
import { RenderConfig } from './MediaTrack'

export const useMediaLaneClick = (
    renderConfig: RenderConfig,
    setRenderConfig: (config: RenderConfig) => void,
    setPlayPosition: (time: number) => void
) => {
    const handleMediaLaneClick = useCallback(
        (clickTime: number) => {
            const newCurrentTime = clickTime >= 0 ? clickTime : 0

            setPlayPosition(newCurrentTime)

            const newTimelineStartTime = Math.floor(newCurrentTime / renderConfig.duration) * renderConfig.duration

            setRenderConfig({
                ...renderConfig,
                currentTime: newCurrentTime,
                timelineStartTime: newTimelineStartTime,
            })
        },
        [renderConfig, setPlayPosition, setRenderConfig]
    )

    return {
        handleMediaLaneClick,
    }
}
