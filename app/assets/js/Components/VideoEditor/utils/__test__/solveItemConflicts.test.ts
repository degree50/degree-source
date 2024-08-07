import { solveConflicts, findNonOverlappingItemsRecursively } from '../solveItemConflicts'
import { MediaItem } from '../../types'
import { MediaItemType } from 'Components/VideoEditor/types'

describe('findNonOverlappingItems()', () => {
    it('should return all items, because non overlap', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:11:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsWithIds = [
            { id: 0, item: itemA },
            { id: 1, item: itemB },
            { id: 2, item: itemC },
        ]

        const result = findNonOverlappingItemsRecursively(mediaItemsWithIds)

        expect(result).toEqual(mediaItemsWithIds)
    })

    it('should return A + C, but not B', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:08:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsWithIds = [
            { id: 0, item: itemA },
            { id: 1, item: itemB },
            { id: 2, item: itemC },
        ]

        const result = findNonOverlappingItemsRecursively(mediaItemsWithIds)

        expect(result).toEqual([
            { id: 0, item: itemA },
            { id: 2, item: itemC },
        ])
    })

    it('should return A + C + D, but not B', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:08:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemD = new MediaItem<MediaItemType>({
            start: '00:16:00',
            end: '00:30:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsWithIds = [
            { id: 0, item: itemA },
            { id: 1, item: itemB },
            { id: 2, item: itemC },
            { id: 3, item: itemD },
        ]

        expect(findNonOverlappingItemsRecursively(mediaItemsWithIds)).toEqual([
            { id: 0, item: itemA },
            { id: 2, item: itemC },
            { id: 3, item: itemD },
        ])
    })

    it('should return A + C + D, but not B', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:04:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:08:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemD = new MediaItem<MediaItemType>({
            start: '00:16:00',
            end: '00:30:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsWithIds = [
            { id: 0, item: itemA },
            { id: 1, item: itemB },
            { id: 2, item: itemC },
            { id: 3, item: itemD },
        ]

        expect(findNonOverlappingItemsRecursively(mediaItemsWithIds)).toEqual([
            { id: 1, item: itemB },
            { id: 2, item: itemC },
            { id: 3, item: itemD },
        ])
    })

    it('should return A but not B because of conflict with additional item', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const additionalItem = {
            id: 3,
            item: new MediaItem<MediaItemType>({
                start: '00:06:00',
                end: '00:10:00',
                text: '',
                color: null,
                lane: 0,
                // we don't actually need this for our tests
                // @ts-ignore disable-line
                originalData: {},
            }),
        }

        const mediaItemsWithIds = [
            { id: 0, item: itemA },
            { id: 1, item: itemB },
        ]

        expect(findNonOverlappingItemsRecursively(mediaItemsWithIds, additionalItem)).toEqual([{ id: 0, item: itemA }])
    })
})

describe('solveConflicts()', () => {
    it('should add no additional lane for non-overlapping items', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(0)

        const mediaItemsReversed = [itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(0)
    })

    it('should add 1 additional lane for two overlapping items', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(1)

        const mediaItemsReversed = [itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(1)
    })

    it('should add 2 additional lanes for three overlapping items', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // @ts-ignore disable-line
            _id: 'B',
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB, itemC]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(1)
        expect(result[2].lane).toBe(2)

        const mediaItemsReversed = [itemC, itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(1)
        expect(resultForReversed[2].lane).toBe(2)
    })

    it('should add 4 additional lanes for four overlapping items', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const itemD = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB, itemC, itemD]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(1)
        expect(result[2].lane).toBe(2)
        expect(result[3].lane).toBe(3)

        const mediaItemsReversed = [itemD, itemC, itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(1)
        expect(resultForReversed[2].lane).toBe(2)
        expect(resultForReversed[3].lane).toBe(3)
    })

    it('should handle overlapping and non-overlapping items', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:11:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB, itemC]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(0)
        expect(result[2].lane).toBe(1)
    })

    it('should handle overlapping and non-overlapping items REVERSED', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:11:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsReversed = [itemC, itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(0)
        expect(resultForReversed[2].lane).toBe(1)
    })

    it('should handle different overlapps of sub-items (e.g. A overlaps B and C overlaps B, but not A)', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB, itemC]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(0)
        expect(result[2].lane).toBe(1)
    })

    it('should handle different overlapps of sub-items (e.g. A overlaps B and C overlaps B, but not A) REVERSED', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:02:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsReversed = [itemC, itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        expect(resultForReversed[0].lane).toBe(0)
        expect(resultForReversed[1].lane).toBe(0)
        expect(resultForReversed[2].lane).toBe(1)
    })

    it('should handle different overlapps of sub-items (e.g. A overlaps B and C overlaps B, but not A, D overlaps A, but not B)', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:04:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemD = new MediaItem<MediaItemType>({
            start: '00:01:00',
            end: '00:03:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItems = [itemA, itemB, itemC, itemD]

        const result = solveConflicts(mediaItems)

        expect(result[0].lane).toBe(0)
        expect(result[1].lane).toBe(0)
        expect(result[2].lane).toBe(1)
        expect(result[3].lane).toBe(1)
    })

    it('should handle different overlapps of sub-items (e.g. A overlaps B and C overlaps B, but not A, D overlaps A, but not B) REVERSED', () => {
        const itemA = new MediaItem<MediaItemType>({
            start: '00:00:01',
            end: '00:05:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemB = new MediaItem<MediaItemType>({
            start: '00:04:00',
            end: '00:10:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemC = new MediaItem<MediaItemType>({
            start: '00:06:00',
            end: '00:15:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })
        const itemD = new MediaItem<MediaItemType>({
            start: '00:01:00',
            end: '00:03:00',
            text: '',
            color: null,
            lane: 0,
            // we don't actually need this for our tests
            // @ts-ignore disable-line
            originalData: {},
        })

        const mediaItemsReversed = [itemD, itemC, itemB, itemA]

        const resultForReversed = solveConflicts(mediaItemsReversed)

        // Item D
        // overlaps with A
        // First item => 0
        expect(resultForReversed[0].lane).toBe(0)

        // Item C
        // overlaps with B
        // no overlap with D
        // => 0
        expect(resultForReversed[1].lane).toBe(0)

        // Item B
        // overlaps with A
        // overlaps with C
        // no overlap with D
        // => 1
        expect(resultForReversed[2].lane).toBe(1)

        // Item A
        // overlaps with B
        // overlaps with D
        // no overlap with C
        // => 2
        expect(resultForReversed[3].lane).toBe(1)
    })
})
