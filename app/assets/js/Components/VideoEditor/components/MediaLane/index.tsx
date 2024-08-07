import React, { useCallback } from 'react'
import MediaItems from './MediaItems'
import { MediaItem } from '../../types'
import MediaTrack from './MediaTrack'
import MediaTrackInteractionArea from './MediaTrackInteractionArea'
import { useMediaLaneRendering } from './useMediaLaneRendering'
import { defaultMediaTrackConfig } from './MediaTrack/helpers'
import { actions, selectors as videoEditorSelectors, VideoEditorState } from '../../VideoEditorSlice'
import { connect } from 'react-redux'
import { useMediaLaneClick } from './useMediaLaneClick'
import {
    selectors as configSelectors,
    ConfigStateSlice,
} from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'

type OwnProps = {
    mediaItems: MediaItem<any>[]
    updateMediaItem: (item: MediaItem<any>, updatedValues: Record<string, unknown>) => void // FIXME refine key
    showTextInMediaItems?: boolean
    readOnly?: boolean
}

const mapStateToProps = (state: VideoEditorState & ConfigStateSlice) => ({
    videos: configSelectors.selectVideos(state),
    playerSyncPlayPosition: videoEditorSelectors.player.selectSyncPlayPosition(state),
    mediaLaneRenderConfig: videoEditorSelectors.mediaLaneRenderConfig.selectRenderConfig(state.videoEditor),
    currentTime: videoEditorSelectors.player.selectSyncPlayPosition(state),
})

const mapDispatchToProps = {
    setPlayPosition: actions.player.setPlayPosition,
    setRenderConfig: actions.mediaLaneRenderConfig.setRenderConfig,
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const MediaLane = (props: Props) => {
    const {
        mediaItems,
        currentTime,
        updateMediaItem,
        setPlayPosition,
        showTextInMediaItems = true,
        mediaLaneRenderConfig,
        setRenderConfig,
    } = props

    const firstVideoDuration = props.videos[0].duration

    const checkMediaItem = useCallback(() => {
        // false means no conflict => item is legal
        // true means conflict => item is illegal
        //
        // WHY: this hard coded check?
        // We currently do not yet have defined conditions under which annotations
        // are considered to be illegal.
        // Because they may also overlap etc., we do not use the checkMediaItem() function
        // provided by useMediaItemHandling().
        return false
    }, [])

    const amountOfLanes = Math.max(
        0,
        ...mediaItems.map((item: MediaItem<any>) => {
            return item.lane
        })
    )

    const { handleMediaLaneClick } = useMediaLaneClick(mediaLaneRenderConfig, setRenderConfig, setPlayPosition)

    const {
        containerWidth,
        containerHeight,
        ref: $mediaTrackRef,
    } = useMediaLaneRendering({
        currentTime: props.playerSyncPlayPosition,
        videoDuration: firstVideoDuration,
        renderConfig: props.mediaLaneRenderConfig,
        setRenderConfig: props.setRenderConfig,
    })

    const handleMediaItemUpdate = useCallback(
        (
            item: MediaItem<any>,
            updatedValues: { start?: string; end?: string; memo?: string },
            newStartTime: number,
            newEndTime?: number
        ) => {
            updateMediaItem(item, updatedValues)

            if (updatedValues.start !== undefined) {
                setPlayPosition(newStartTime + 0.001)
            } else if (updatedValues.end !== undefined && newEndTime !== undefined) {
                // WHY '-0.001': This makes sure the item is still marked as active item in player
                setPlayPosition(newEndTime - 0.001)
            }
        },
        [updateMediaItem]
    )

    const mediaTrackConfig = {
        ...defaultMediaTrackConfig,
        render: mediaLaneRenderConfig,
    }

    const baseHeight = 275
    const entryHeight = mediaLaneRenderConfig.heightModifier * baseHeight

    return (
        <>
            <div className="video-editor-timeline__entry" style={{ height: entryHeight }}>
                <div ref={$mediaTrackRef} className="media-track">
                    <MediaTrack
                        mediaTrackConfig={mediaTrackConfig}
                        containerHeight={containerHeight}
                        containerWidth={containerWidth}
                    />
                </div>
                <MediaTrackInteractionArea renderConfig={mediaLaneRenderConfig} clickCallback={handleMediaLaneClick} />
                <MediaItems
                    currentTime={currentTime}
                    renderConfig={mediaLaneRenderConfig}
                    mediaItems={mediaItems}
                    updateMediaItem={handleMediaItemUpdate}
                    checkMediaItem={checkMediaItem}
                    amountOfLanes={amountOfLanes}
                    showTextInMediaItems={showTextInMediaItems}
                    height={entryHeight - mediaTrackConfig.rulerHeight}
                    readOnly={props.readOnly}
                />
            </div>
        </>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(MediaLane))
