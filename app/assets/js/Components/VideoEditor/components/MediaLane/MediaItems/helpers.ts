import { MediaItem } from '../../../types'

export const hasConflictWithItem = (
    itemA: { startTime: number; endTime: number },
    itemB: { startTime: number; endTime: number }
): boolean => itemA.startTime <= itemB.endTime && itemA.endTime >= itemB.startTime

export const itemIsVisible = <T>(
    item: MediaItem<T>,
    currentTimelineClippingStart: number,
    timelineClippingDuration: number
) => {
    const timelineClippingEnd = currentTimelineClippingStart + timelineClippingDuration

    return item.startTime <= timelineClippingEnd && item.endTime >= currentTimelineClippingStart
}

export const getContextYPosition = ({
    mediaItemsHeight,
    phaseHeight,
    pageY,
    contentMenuItemHeight,
}: {
    mediaItemsHeight: number
    phaseHeight: number
    pageY: number
    contentMenuItemHeight: number
}) => {
    const baseY = mediaItemsHeight - (phaseHeight - pageY)

    // WHY: Adjusts the menu to be above the cursor, if the user clicks
    // into the second half of the item
    return baseY >= mediaItemsHeight / 2 ? baseY - 3 * contentMenuItemHeight : baseY
}

export const clamp = (value: number, min: number, max: number) => Math.min(Math.max(min, value), max)
