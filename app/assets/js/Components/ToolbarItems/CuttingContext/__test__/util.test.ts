/**
 * @jest-environment jsdom
 */

import { MediaItem } from 'Components/VideoEditor/types'
import { adjustItemTimelinePositionInList } from '../util'

const itemA = new MediaItem({
    start: '00:00:00.000',
    end: '00:00:05.000',
    text: 'Test',
    memo: 'Some test memo...',
    originalData: {},
    lane: 0,
})

const itemB = new MediaItem({
    start: '00:00:03.000',
    end: '00:00:07.000',
    text: 'Test',
    memo: 'Some test memo...',
    originalData: {},
    lane: 0,
})

const itemC = new MediaItem({
    start: '00:00:08.000',
    end: '00:00:15.000',
    text: 'Test',
    memo: 'Some test memo...',
    originalData: {},
    lane: 0,
})

describe('adjustItemTimelinePositionInList()', () => {
    it('should correctly adjust startTime and endTime of item', () => {
        const index = 1
        const result = adjustItemTimelinePositionInList([itemA, itemB, itemC], itemB, index, '00:00:05.000')

        expect(result).toEqual([itemA, expect.any(MediaItem), itemC])

        expect(result[index].start).toBe('00:00:05.000')
        expect(result[index].end).toBe('00:00:09.000')
    })
})
