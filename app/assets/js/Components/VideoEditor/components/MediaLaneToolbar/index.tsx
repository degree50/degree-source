import React, { useCallback } from 'react'
import { Translate } from 'react-i18nify'
import { RenderConfig } from '../MediaLane/MediaTrack'
import { MEDIA_LANE_TOOLBAR_HEIGHT } from '../MediaLane/useMediaLaneRendering'
import MediaLaneToolbarItem from './MediaLaneToolbarItem'
import { secondsToTimeString } from 'Components/VideoEditor/utils/time'

type Props = {
    updateZoom: (value: number) => void
    videoDuration: number
    renderConfig: RenderConfig
    handleTimeLineAction: (clickTime: number) => void
    children?: React.ReactNode
}

const Toolbar = ({ updateZoom, videoDuration, renderConfig, handleTimeLineAction, children }: Props) => {
    const leftInteractionAreaIsDisabled = renderConfig.timelineStartTime === 0
    const rightInteractionAreaIsDisabled = renderConfig.timelineStartTime + renderConfig.duration >= videoDuration

    const onClick = (direction: string) => {
        if (direction === 'right') {
            const newTime = renderConfig.timelineStartTime + renderConfig.duration
            handleTimeLineAction(newTime)
        } else {
            const newTime = renderConfig.timelineStartTime - renderConfig.duration
            handleTimeLineAction(newTime)
        }
    }

    const zoomHandler = useCallback(
        (ev: React.ChangeEvent<HTMLInputElement>) => {
            updateZoom(parseInt(ev.currentTarget.value))
        },
        [updateZoom]
    )

    return (
        <div className="media-lane-toolbar" style={{ height: MEDIA_LANE_TOOLBAR_HEIGHT }}>
            <div className="media-lane-toolbar__item-group">
                <MediaLaneToolbarItem>
                    {secondsToTimeString(renderConfig.timelineStartTime)} -{' '}
                    {secondsToTimeString(renderConfig.timelineStartTime + renderConfig.duration)} /{' '}
                    {secondsToTimeString(videoDuration)}
                </MediaLaneToolbarItem>
                <MediaLaneToolbarItem>
                    <label htmlFor="timeline-zoom-handler">
                        {/* @ts-ignore */}
                        <Translate value="zoom" />
                    </label>
                    <input
                        name="timeline-zoom-handler"
                        id="timeline-zoom-handler"
                        value={renderConfig.zoom}
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        onChange={zoomHandler}
                    />
                </MediaLaneToolbarItem>
                <MediaLaneToolbarItem>
                    <button
                        className={'button button--type-primary button--size-small'}
                        disabled={leftInteractionAreaIsDisabled}
                        title={'Timeline nach links verschieben'}
                        onClick={() => {
                            onClick('left')
                        }}
                    >
                        <i className={'fas fa-chevron-left'} />
                    </button>
                </MediaLaneToolbarItem>
                <MediaLaneToolbarItem>
                    <button
                        className={'button button--type-primary button--size-small'}
                        disabled={rightInteractionAreaIsDisabled}
                        title={'Timeline nach rechts verschieben'}
                        onClick={() => {
                            onClick('right')
                        }}
                    >
                        <i className={'fas fa-chevron-right'} />
                    </button>
                </MediaLaneToolbarItem>
            </div>
            {children && <div className="media-lane-toolbar__item-group">{children}</div>}
        </div>
    )
}

export default React.memo(Toolbar)
