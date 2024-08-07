import { useLayoutEffect, useEffect } from 'react'
import { RenderConfig } from './MediaTrack'
import { useDebouncedResizeObserver } from '../../utils/useDebouncedResizeObserver'
import { calculateTimelineStartTime } from './helpers'

/**
 * The initial zoom value in %.
 */
export const INITIAL_ZOOM = 0
export const MEDIA_LANE_HEIGHT = 350
export const MEDIA_LANE_TOOLBAR_HEIGHT = 40

const getDurationForRenderConfig = (durationInPercentage: number, videoDuration: number) => {
    return Math.max(5, Math.round((videoDuration / 100) * (100 - durationInPercentage)))
}

export const useMediaLaneRendering = ({
    $mediaTrackRef,
    currentTime,
    videoDuration,
    renderConfig,
    setRenderConfig,
}: {
    $mediaTrackRef?: React.RefObject<HTMLDivElement>
    currentTime: number
    videoDuration: number
    renderConfig: RenderConfig
    setRenderConfig: (newRenderConfig: RenderConfig) => void
}) => {
    const { width: containerWidth, height: containerHeight, ref } = useDebouncedResizeObserver($mediaTrackRef, 500)

    const updateRenderConfigOnResize = (containerWidth: number) => {
        const newGridGap = containerWidth / renderConfig.gridNum

        setRenderConfig({
            ...renderConfig,
            gridGap: newGridGap,
        })
    }

    const setRenderConfigForZoom = (zoomInPercent: number) => {
        const newRenderedDuration = getDurationForRenderConfig(zoomInPercent, videoDuration)
        const newGridNum = newRenderedDuration * 10 + renderConfig.padding * 2
        const newGridGap = containerWidth / newGridNum

        const newTimelineStartTime = calculateTimelineStartTime(currentTime, newRenderedDuration, videoDuration)

        setRenderConfig({
            ...renderConfig,
            duration: newRenderedDuration,
            gridNum: newGridNum,
            gridGap: newGridGap,
            timelineStartTime: newTimelineStartTime,
            zoom: zoomInPercent,
        })
    }

    // Update after initial rendering
    useLayoutEffect(() => {
        updateRenderConfigOnResize(containerWidth)
    }, [])

    // Update after resize
    useEffect(() => {
        updateRenderConfigOnResize(containerWidth)
    }, [containerWidth])

    // Update when the player plays (and therefore currentTime changes)
    useEffect(() => {
        const newTimelineStartTime =
            currentTime > 0 ? Math.floor(currentTime / renderConfig.duration) * renderConfig.duration : 0

        setRenderConfig({
            ...renderConfig,
            currentTime: currentTime,
            timelineStartTime: newTimelineStartTime,
        })
    }, [currentTime])

    useEffect(() => {
        setRenderConfigForZoom(renderConfig.zoom)
    }, [containerWidth, videoDuration, renderConfig.zoom])

    return {
        containerWidth,
        containerHeight,
        renderConfig,
        setRender: setRenderConfig,
        ref,
    }
}
