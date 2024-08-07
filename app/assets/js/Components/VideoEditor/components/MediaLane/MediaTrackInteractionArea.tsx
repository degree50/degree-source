import React, { useCallback } from 'react'
import { RenderConfig } from './MediaTrack'

type Props = {
    renderConfig: RenderConfig
    clickCallback: (clickTime: number) => void
}

const InteractionArea = ({ renderConfig, clickCallback }: Props) => {
    const getEventTime = useCallback(
        (event) => {
            const xPosition = event.pageX - event.target.getBoundingClientRect().left
            return (
                (xPosition - renderConfig.padding * renderConfig.gridGap) / renderConfig.gridGap / 10 +
                renderConfig.timelineStartTime
            )
        },
        [renderConfig]
    )

    const onMouseDown = useCallback(
        (event) => {
            const clickTime = getEventTime(event)
            clickCallback(clickTime)
        },
        [getEventTime]
    )

    return (
        <div className="video-editor__interaction-area" onMouseDown={onMouseDown}>
            <div className="video-editor__media-item__template" />
        </div>
    )
}

export default React.memo(InteractionArea)
