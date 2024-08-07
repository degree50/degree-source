import React, { useState, useCallback, useEffect } from 'react'
import { d2t } from 'duration-time-conversion'
import { MediaItem } from '../../../types'
import { RenderConfig } from '../MediaTrack'
import { itemIsVisible } from './helpers'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'

const getVisibleItems = <T>(items: MediaItem<T>[], timelineStartTime: number, duration: number): MediaItem<T>[] => {
    return items.filter((item) => itemIsVisible(item, timelineStartTime, duration))
}

export const useItemInteraction = <T>(
    mediaItems: MediaItem<T>[],
    renderConfig: RenderConfig,
    $mediaItemsRef: React.RefObject<HTMLDivElement>,
    updateMediaItem: (
        item: MediaItem<T>,
        updatedValues: { start?: string; end?: string },
        newStartTime: number,
        newEndTime?: number
    ) => void
) => {
    const [isDraging, setIsDraging] = useState(false)
    const [lastClickedItem, setLastClickedItem] = useState<MediaItem<T> | undefined>(undefined)
    const [lastClickedItemIndex, setLastClickedItemIndex] = useState<number>(0)
    const [lastClickedItemSide, setLastClickedItemSide] = useState('center')
    const [lastTargetNode, setLastTargetNode] = useState<HTMLElement | undefined>(undefined)
    const [lastTargetNodeWidth, setLastTargetNodeWidth] = useState<number>(0)
    const [lastPageX, setLastPageX] = useState<number>(0)
    const [lastDiffX, setLastDiffX] = useState<number>(0)

    const onItemMouseDownOrTouchStart = useCallback(
        (pageX: number, item: MediaItem<T>, side: HandleSide) => {
            setIsDraging(true)
            setLastClickedItem(item)
            setLastClickedItemSide(side)
            setLastPageX(pageX)

            const currentlyVisibleItems = getVisibleItems(
                mediaItems,
                renderConfig.timelineStartTime,
                renderConfig.duration
            )

            const itemIndex = currentlyVisibleItems.indexOf(item)

            setLastClickedItemIndex(itemIndex)

            const $mediaItemsWrapper = $mediaItemsRef.current

            if ($mediaItemsWrapper) {
                const targetNode = $mediaItemsWrapper.children[itemIndex] as HTMLElement

                setLastTargetNode(targetNode)
                setLastTargetNodeWidth(parseFloat(targetNode.style.width))
            }
        },
        [
            setIsDraging,
            setLastClickedItem,
            setLastClickedItemSide,
            setLastClickedItemIndex,
            setLastPageX,
            setLastTargetNode,
            setLastTargetNodeWidth,
            renderConfig,
            mediaItems,
            $mediaItemsRef,
        ]
    )

    const onItemTouchStart = useCallback(
        (event: React.TouchEvent, item: MediaItem<T>, side: HandleSide) => {
            onItemMouseDownOrTouchStart(event.touches[0].pageX, item, side)
        },
        [onItemMouseDownOrTouchStart]
    )

    const onItemMouseDown = useCallback(
        (event: React.MouseEvent, item: MediaItem<T>, side: HandleSide) => {
            onItemMouseDownOrTouchStart(event.pageX, item, side)
        },
        [onItemMouseDownOrTouchStart]
    )

    const onMouseMove = useCallback(
        (event) => {
            if (isDraging && lastTargetNode) {
                const pageX = event.type === 'touchmove' ? event.touches[0].pageX : event.pageX
                const lastDiffX = pageX - lastPageX
                setLastDiffX(lastDiffX)

                if (lastClickedItemSide === 'left') {
                    // eslint-disable-next-line
                    lastTargetNode.style.width = `${lastTargetNodeWidth - lastDiffX}px`
                    // eslint-disable-next-line
                    lastTargetNode.style.transform = `translate(${lastDiffX}px)`
                } else if (lastClickedItemSide === 'right') {
                    // eslint-disable-next-line
                    lastTargetNode.style.width = `${lastTargetNodeWidth + lastDiffX}px`
                } else {
                    // eslint-disable-next-line
                    lastTargetNode.style.transform = `translate(${lastDiffX}px)`
                }
            }
        },
        [isDraging, lastTargetNode, lastPageX, lastClickedItemSide, lastTargetNode, lastTargetNodeWidth, setLastDiffX]
    )

    const onMouseUp = useCallback(() => {
        if (isDraging && lastTargetNode && lastDiffX && lastClickedItem) {
            const timeDiff = lastDiffX / renderConfig.gridGap / 10
            const newStartTime = (lastClickedItem.startTime || 0) + timeDiff
            const newEndTime = (lastClickedItem.endTime || 0) + timeDiff

            if (lastClickedItemSide === 'left') {
                // drag left handle
                if (newStartTime >= 0 && newStartTime < (lastClickedItem.endTime || 0)) {
                    const start = d2t(newStartTime.toFixed(3))

                    updateMediaItem(lastClickedItem, { start }, newStartTime)
                } else {
                    // eslint-disable-next-line
                    lastTargetNode.style.width = `${lastTargetNodeWidth}px`
                }
            } else if (lastClickedItemSide === 'right') {
                // drag right handle
                if (newEndTime >= 0 && newEndTime > (lastClickedItem?.startTime || 0)) {
                    const end = d2t(newEndTime.toFixed(3))

                    updateMediaItem(
                        lastClickedItem,
                        { end },
                        lastClickedItem.startTime, // start time should not've changed here
                        newEndTime
                    )
                } else {
                    // eslint-disable-next-line
                    lastTargetNode.style.width = `${lastTargetNodeWidth}px`
                }
            } else {
                // drag item itself
                if (newStartTime > 0 && newEndTime > 0 && newEndTime > newStartTime) {
                    const start = d2t(newStartTime.toFixed(3))
                    const end = d2t(newEndTime.toFixed(3))

                    updateMediaItem(
                        lastClickedItem,
                        {
                            start,
                            end,
                        },
                        newStartTime
                    )
                } else {
                    // eslint-disable-next-line
                    lastTargetNode.style.width = `${lastTargetNodeWidth}px`
                }
            }

            // eslint-disable-next-line
            lastTargetNode.style.transform = `translate(0)`
        }

        setLastClickedItemSide('center')
        setLastPageX(0)
        setLastDiffX(0)
        setIsDraging(false)
        setLastClickedItem(undefined)
        setLastClickedItemIndex(0)
        setLastTargetNode(undefined)
        setLastTargetNodeWidth(0)
    }, [
        isDraging,
        mediaItems,
        updateMediaItem,
        lastTargetNode,
        lastDiffX,
        lastClickedItem,
        lastClickedItemIndex,
        lastTargetNodeWidth,
        setLastClickedItemSide,
        setLastPageX,
        setLastDiffX,
        setIsDraging,
        setLastClickedItem,
        setLastClickedItemIndex,
        setLastTargetNode,
        setLastTargetNodeWidth,
    ])

    useEffect(() => {
        document.addEventListener('mousemove', onMouseMove)
        document.addEventListener('touchmove', onMouseMove)
        document.addEventListener('mouseup', onMouseUp)
        document.addEventListener('touchend', onMouseUp)

        return () => {
            document.removeEventListener('mousemove', onMouseMove)
            document.removeEventListener('touchmove', onMouseMove)
            document.removeEventListener('mouseup', onMouseUp)
            document.removeEventListener('touchend', onMouseUp)
        }
    }, [onMouseMove, onMouseUp])

    return {
        onItemTouchStart,
        onItemMouseDown,
    }
}
