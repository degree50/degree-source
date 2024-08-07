import { MediaItem, MediaItemType } from '../types'
import { hasConflictWithItem } from '../components/MediaLane/MediaItems/helpers'

type ItemWithId = {
    id: number
    item: MediaItem<MediaItemType>
}

/**
 * JS does not support proper tail call optimization.
 * This trampoline function prevents us from exceeding the tail stack when doing tail recursion.
 */
// eslint-disable-next-line
const trampoline = (f: Function) => {
    return function trampolined(...args: unknown[]) {
        // eslint-disable-next-line
        let result = f.bind(null, ...args)

        while (typeof result === 'function') result = result()

        return result
    }
}

/**
 * Finds all items in a list that do have no overlap any other item of the list or with an additional item which can optionally be given as another argument.
 */
export const findNonOverlappingItemsRecursively = trampoline(
    (mediaItemsWithIds: ItemWithId[], additionalItemToCompareWith?: ItemWithId, acc: ItemWithId[] = []) => {
        if (mediaItemsWithIds.length < 1) {
            return acc
        }

        const [head, ...tail] = mediaItemsWithIds

        const hasConflictWithAdditionalItem =
            additionalItemToCompareWith && head
                ? hasConflictWithItem(additionalItemToCompareWith.item, head.item)
                : false

        const hasConflictWithFollowingItem = tail.reduce((acc, item) => {
            return acc || hasConflictWithItem(head.item, item.item)
        }, false)

        const hasConflict = hasConflictWithFollowingItem || hasConflictWithAdditionalItem

        const newAcc = [...acc, ...(hasConflict ? [] : [head])]

        return () => findNonOverlappingItemsRecursively(tail, additionalItemToCompareWith, newAcc)
    }
)

/**
 * Given a list of mediaItems this function checks if items have conflicts with one another and if so assigns them to a different lane.
 * HOW:
 * * we start at lane 0
 * * the current item will be placed on the current lane as well as all other items which do not have any conflicts with each other or the current item
 * * as soon as items have a lane assigned, we won't touch them anymore (they will no longer be part of the items to process)
 *
 * NOTE: This still does not work perfectly, as the way items are placed is not transparent to the user and feels random (bad UX)
 */
export const solveConflicts = (mediaItems: MediaItem<MediaItemType>[]): MediaItem<MediaItemType>[] => {
    const sortedMediaItems = [...mediaItems].sort((a: MediaItem<MediaItemType>, b: MediaItem<MediaItemType>) => {
        if (a.startTime < b.startTime) {
            return -1
        } else if (a.startTime > b.startTime) {
            return 1
        }
        return 0
    })

    // FIXME as soon as all items have an id by default, remove the temporary ids and use
    // the actual ones
    const itemsWithTemporaryIds = sortedMediaItems.map((item, index) => ({
        id: index,
        item,
    }))

    const assignmentResult = itemsWithTemporaryIds.reduce(
        (acc, _, index) => {
            if (!acc.itemsToProcess.length) {
                return acc
            }

            const [head, ...tail] = acc.itemsToProcess

            const nonOverlappingItems = [head, ...findNonOverlappingItemsRecursively(tail, head)]

            const newAssignedItems = nonOverlappingItems.map((item) => ({
                ...item,
                item: new MediaItem({
                    ...item.item,
                    lane: index,
                }),
            }))

            const remainingItemsToProcess = acc.itemsToProcess.filter(
                (item) => !newAssignedItems.find((assigned) => assigned.id === item.id)
            )

            return {
                assignedItems: [...acc.assignedItems, ...newAssignedItems],
                itemsToProcess: remainingItemsToProcess,
            }
        },
        { assignedItems: [] as ItemWithId[], itemsToProcess: itemsWithTemporaryIds }
    )

    return assignmentResult.assignedItems.map((item) => item.item)
}
