import React, { useCallback, useRef, useEffect, MouseEventHandler } from 'react'
import { connect } from 'react-redux'
import {
    MediaItem as MediaItemClass,
    MediaItemType,
    MediaItemTypeEnum,
    MediaItemTypeWithTypeInformation,
} from '../../../../types'
import { RenderConfig } from '../../MediaTrack'
import { actions } from '../../../../PlayerSlice'
import { useModalHook } from 'Components/Modal/useModalHook'
import { clamp } from '../helpers'
import InfoButton from './MediaItemInfoButton'
import ItemHandle from './ItemHandle'
import Body from './Body'
import AnnotationListItem from 'Components/ToolbarItems/AnnotationsContext/Overlays/AnnotationListItem'
import CutListItem from 'Components/ToolbarItems/CuttingContext/Overlays/CutListItem'
import VideoCodeListItem from 'Components/ToolbarItems/VideoCodesContext/Overlays/VideoCodeListItem'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'

type OwnProps = {
    item: MediaItemClass<MediaItemTypeWithTypeInformation>
    id: number | string
    renderConfig: RenderConfig
    isPlayedBack?: boolean
    checkMediaItem: (item: MediaItemClass<any>) => boolean
    onItemMouseDown: (
        event: React.MouseEvent,
        item: MediaItemClass<MediaItemTypeWithTypeInformation>,
        side: HandleSide
    ) => void
    onItemTouchStart: (
        event: React.TouchEvent,
        item: MediaItemClass<MediaItemTypeWithTypeInformation>,
        side: HandleSide
    ) => void
    updateMediaItem: (
        item: MediaItemClass<MediaItemType>,
        updatedValues: { start?: string; end?: string; memo?: string },
        newStartTime: number
    ) => void
    amountOfLanes?: number
    showTextInMediaItems: boolean
}

const mapDispatchToProps = {
    setPause: actions.setPause,
    setPlayPosition: actions.setPlayPosition,
}

type Props = typeof mapDispatchToProps & OwnProps

export const MediaItem = ({
    item,
    id,
    renderConfig,
    checkMediaItem,
    onItemMouseDown,
    onItemTouchStart,
    isPlayedBack,
    amountOfLanes = 0,
    showTextInMediaItems = true,
    setPause,
    setPlayPosition,
}: Props) => {
    const itemRef = useRef<HTMLDivElement>(null)

    const handleMouseDown = useCallback(
        (event: React.MouseEvent, side: HandleSide) => {
            setPause(true)
            onItemMouseDown(event, item, side)
        },
        [item, onItemMouseDown, setPause]
    )

    const handleTouchStart = useCallback(
        (event: React.TouchEvent, side: HandleSide) => {
            setPause(true)
            onItemTouchStart(event, item, side)
        },
        [item, onItemTouchStart, setPause]
    )

    const mediaItemHeight = 100 / (amountOfLanes + 1)

    const positionLeft =
        renderConfig.padding * renderConfig.gridGap +
        (item.startTime - renderConfig.timelineStartTime) * renderConfig.gridGap * 10

    const { showModal: showMediaItemModal, RenderModal: RenderMediaItemModal } = useModalHook()

    const width = (item.endTime - item.startTime) * renderConfig.gridGap * 10

    // WHY:
    // This is a hack to make sure, that media items will always reflect the correct width if either their
    // DOM node or the item changes. This means, that even if just some part of item.originalData
    //  (e.g. the offset on a cutList item) has been changed, the width will be explicitly set to the DOM node.
    // This is necessary, because our useItemInteraction() hook directly messes with the DOM nodes of media items and
    // updating the width of the React virtual dom node won't take effect after some renders otherwise.
    //
    // This helps to prevent bugs like this: https://gitlab.sandstorm.de/degree-4.0/code/-/issues/59
    useEffect(() => {
        if (itemRef.current) {
            // eslint-disable-next-line
            itemRef.current.style.width = `${width}px`
        }
    }, [itemRef, item])

    // WHY: Clamp width of lane item drag handle between min and max value
    const laneItemHandleWidth = clamp(renderConfig.gridGap, 10, 20)

    const modalTitle = (() => {
        switch (item.originalData.type) {
            case MediaItemTypeEnum.annotation: {
                return 'Annotation'
            }

            case MediaItemTypeEnum.videoCode: {
                return 'Video Codierung'
            }

            case MediaItemTypeEnum.cut: {
                return 'Schnitt'
            }
        }
    })()

    const modalBody = (() => {
        if (item.originalData.id) {
            switch (item.originalData.type) {
                case MediaItemTypeEnum.annotation: {
                    return (
                        <AnnotationListItem key={item.originalData.id} annotationId={item.originalData.id} index={1} />
                    )
                }

                case MediaItemTypeEnum.videoCode: {
                    return <VideoCodeListItem key={item.originalData.id} videoCodeId={item.originalData.id} index={1} />
                }

                case MediaItemTypeEnum.cut: {
                    return <CutListItem key={item.originalData.id} cutId={item.originalData.id} index={1} />
                }
            }
        } else {
            return null
        }
    })()

    const handleItemHandleClick: MouseEventHandler<HTMLDivElement> = (ev) => {
        // Why: We stop event propagation here to prevent the click handler of MediaItem to overwrite the player time
        //      in order to be able to set the end time with the ItemHandle.
        ev.stopPropagation()
        setPause(true)
    }

    return (
        <div
            ref={itemRef}
            className={[
                'video-editor__media-items__item',
                isPlayedBack ? 'video-editor__media-items__item--highlight' : '',
                checkMediaItem(item) ? 'video-editor__media-items__item--illegal' : '',
            ]
                .join(' ')
                .trim()}
            key={id}
            title={item.text}
            style={{
                backgroundColor: item.color ? item.color : '',
                left: positionLeft,
                width,
                top: item.lane * mediaItemHeight + '%',
                height: mediaItemHeight + '%',
            }}
            onClick={() => {
                setPause(true)
                setPlayPosition(item.startTime + 0.001)
            }}
        >
            <InfoButton onClick={showMediaItemModal} />

            <ItemHandle
                side="left"
                width={laneItemHandleWidth}
                onMouseDown={handleMouseDown}
                onTouchStart={handleTouchStart}
                onClick={handleItemHandleClick}
            />

            <Body
                onMouseDown={handleMouseDown}
                onTouchStart={handleTouchStart}
                item={item}
                showTextInMediaItems={showTextInMediaItems}
            />

            <ItemHandle
                side="right"
                width={laneItemHandleWidth}
                onMouseDown={handleMouseDown}
                onTouchStart={handleTouchStart}
                onClick={handleItemHandleClick}
            />

            {modalBody && (
                <RenderMediaItemModal title={modalTitle}>
                    <ol className="video-editor__media-item-list-new">{modalBody}</ol>
                </RenderMediaItemModal>
            )}
        </div>
    )
}

export default connect(undefined, mapDispatchToProps)(React.memo(MediaItem))
