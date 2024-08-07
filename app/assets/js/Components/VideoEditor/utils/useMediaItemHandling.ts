import { useCallback, useState, useEffect, useMemo } from 'react'
import { t } from 'react-i18nify'
import isEqual from 'lodash/isEqual'

import { notify } from '.'
import { secondToTime } from './time'
import { MediaItem } from '../types'

export const getNewMediaItemStartAndEnd = (currentTime: number, duration: number) => {
    const start = secondToTime(currentTime)
    const end = secondToTime(Math.ceil(currentTime + duration / 10))

    return { start, end }
}

// TODO refactor this and:
// * Remove crud operations and move the into editor store slices where necessary/possible
// * Make sure that all editors use store store slices
// * Remove Slice.actions.set for each slice if possible
// NOTE: it could be that this is not possible for the subtitle editor, depending on how
// closely its wired with the worker (or at least the worker update would have to be refactored as well).
// However it's currently unclear if we are going to keep the subtitle-editor anyway or if the customer
// is rather going to upload subtitle files themselves.

export const useMediaItemHandling = <T>({
    mediaItems,
    setMediaItems,
    updateCallback,
    updateCondition,
    worker,
}: {
    mediaItems: Array<MediaItem<T>>
    setMediaItems: (mediaItems: Array<T>) => void
    updateCallback: () => void
    updateCondition: boolean
    worker?: Worker
}) => {
    // MediaItem currently playing index
    const [currentIndex, setCurrentIndex] = useState(-1)

    // MediaItem currently playing time
    const [currentTimeForMediaItems, setCurrentTimeForMediaItems] = useState(0)

    // Only way to update all mediaItems
    const updateMediaItems = (items: Array<MediaItem<T>>, force = false) => {
        const notEqualToPreviousItems = !isEqual(items, mediaItems)

        if (force || (updateCondition && notEqualToPreviousItems)) {
            const updatedItems = JSON.parse(JSON.stringify(items))

            // This makes sure that all properties from the original item will be written back to the store
            // E.g. the 'url' property of a cut
            const convertedToOriginalStructure = updatedItems.map((item: MediaItem<T>): T => {
                const { originalData, ...rest } = item

                return {
                    ...originalData,
                    ...rest,
                }
            })

            setMediaItems(convertedToOriginalStructure)
            updateCallback()

            if (worker) {
                worker.postMessage(items)
            }
        }
    }

    useEffect(() => {
        setCurrentIndex(
            mediaItems.findIndex(
                (item) => item.startTime <= currentTimeForMediaItems && item.endTime > currentTimeForMediaItems
            )
        )

        if (worker) {
            // Takes care of the mediaItems which overlay the video
            worker.postMessage(mediaItems)
        }
    }, [worker])

    // Update current index from current time
    useMemo(() => {
        setCurrentIndex(
            mediaItems.findIndex(
                (item) => item.startTime <= currentTimeForMediaItems && item.endTime > currentTimeForMediaItems
            )
        )
    }, [currentTimeForMediaItems, setCurrentIndex])

    // Detect if the mediaItem exists (referential check)
    const hasMediaItem = useCallback((item) => mediaItems.indexOf(item), [mediaItems])

    // Copy all mediaItems
    const copyMediaItems = useCallback(() => mediaItems.map((item) => item.clone), [mediaItems])

    /**
     * @deprecated
     * update a single mediaItem
     */
    const updateMediaItem = useCallback(
        (item: MediaItem<T>, updatedValues: Record<string, unknown>) => {
            const index = hasMediaItem(item)

            if (index < 0) return

            const copiedItems = copyMediaItems()
            const { clone } = item

            // eslint-disable-next-line
            Object.assign(clone, updatedValues)

            if (clone.check) {
                updateMediaItems([...copiedItems.slice(0, index), clone, ...copiedItems.slice(index + 1)])
            } else {
                notify(t('parameter-error'), 'error')
            }
        },
        [hasMediaItem, copyMediaItems, updateMediaItems]
    )

    return {
        currentIndex,
        currentTimeForMediaItems,
        setCurrentIndex,
        setCurrentTimeForMediaItems,
        updateMediaItem,
        hasMediaItem,
        updateMediaItems,
        copyMediaItems,
    }
}
