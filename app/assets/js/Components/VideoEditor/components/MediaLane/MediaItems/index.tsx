import React, { useRef } from 'react'
import MediaItem from './MediaItem'
import {
    MediaItem as MediaItemClass,
    MediaItemType,
    MediaItemTypeEnum,
    MediaItemTypeWithTypeInformation,
} from '../../../types'
import { RenderConfig } from '../MediaTrack'
import { useItemInteraction } from './useItemInteraction'
import { itemIsVisible } from './helpers'
import ReadOnlyMediaItem from './ReadOnlyMediaItem'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'

const renderItems = (config: {
    mediaItems: MediaItemClass<MediaItemTypeWithTypeInformation>[]
    renderConfig: RenderConfig
    activeItemIndex: number
    checkMediaItem: (item: MediaItemClass<any>) => boolean
    handlers: {
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
    }
    updateMediaItem: (
        item: MediaItemClass<MediaItemType>,
        updatedValues: { start?: string; end?: string; memo?: string },
        newStartTime: number
    ) => void
    showTextInMediaItems: boolean
    amountOfLanes?: number
}) =>
    config.mediaItems.map((item, index) => {
        if (!itemIsVisible(item, config.renderConfig.timelineStartTime, config.renderConfig.duration)) {
            return null
        }

        return (
            <MediaItem
                key={item.originalData?.id ?? index}
                id={item.originalData?.id ?? index}
                item={item}
                renderConfig={config.renderConfig}
                checkMediaItem={config.checkMediaItem}
                isPlayedBack={config.activeItemIndex === index}
                amountOfLanes={config.amountOfLanes}
                updateMediaItem={config.updateMediaItem}
                showTextInMediaItems={config.showTextInMediaItems}
                {...config.handlers}
            />
        )
    })

const renderReadOnlyItems = (config: {
    mediaItems: MediaItemClass<MediaItemType>[]
    showTextInMediaItems: boolean
    renderConfig: RenderConfig
    amountOfLanes?: number
}) =>
    config.mediaItems.map((item, index) => (
        <ReadOnlyMediaItem
            key={item.originalData?.id ?? index}
            id={item.originalData?.id ?? index}
            item={item}
            showTextInMediaItems={config.showTextInMediaItems}
            renderConfig={config.renderConfig}
            amountOfLanes={config.amountOfLanes}
        />
    ))

type Props = {
    mediaItems: MediaItemClass<MediaItemType & { type: MediaItemTypeEnum }>[]
    renderConfig: RenderConfig
    currentTime: number
    updateMediaItem: (
        item: MediaItemClass<MediaItemType>,
        updatedValues: { start?: string; end?: string; memo?: string },
        newStartTime: number
    ) => void
    checkMediaItem: (item: MediaItemClass<MediaItemType>) => boolean
    showTextInMediaItems: boolean
    height: number
    amountOfLanes?: number
    readOnly?: boolean
}

const MediaItems = (props: Props) => {
    const {
        mediaItems,
        renderConfig,
        currentTime,
        updateMediaItem,
        checkMediaItem,
        showTextInMediaItems,
        amountOfLanes,
        height,
    } = props

    const $mediaItemsRef: React.RefObject<HTMLDivElement> = useRef(null)
    const { onItemMouseDown, onItemTouchStart } = useItemInteraction(
        mediaItems,
        renderConfig,
        $mediaItemsRef,
        updateMediaItem
    )
    const activeItemIndex = mediaItems.findIndex((item) => item.startTime <= currentTime && item.endTime > currentTime)

    return (
        <div className="video-editor__media-items" style={{ height }}>
            <div ref={$mediaItemsRef}>
                {props.readOnly
                    ? renderReadOnlyItems({
                          mediaItems,
                          showTextInMediaItems,
                          renderConfig,
                          amountOfLanes,
                      })
                    : renderItems({
                          mediaItems,
                          renderConfig,
                          activeItemIndex,
                          checkMediaItem,
                          handlers: {
                              onItemMouseDown,
                              onItemTouchStart,
                          },
                          updateMediaItem,
                          showTextInMediaItems,
                          amountOfLanes,
                      })}
            </div>
            <div id={'media-item-context-menu'} />
        </div>
    )
}

export default React.memo(MediaItems)
