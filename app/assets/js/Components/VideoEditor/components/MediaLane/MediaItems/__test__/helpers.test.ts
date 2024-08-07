import { clamp, hasConflictWithItem } from '../helpers'

describe('hasConflictWithItem()', () => {
    it('should return true if itemA.startTime < itemB.endTime and itemA.endTime > itemB.startTime', () => {
        const itemA = { startTime: 0, endTime: 5 }
        const itemB = { startTime: 0, endTime: 10 }
        const result = hasConflictWithItem(itemA, itemB)

        expect(result).toBe(true)
    })

    it('should return false if itemA.startTime > itemB.endTime', () => {
        const itemA = { startTime: 11, endTime: 5 }
        const itemB = { startTime: 0, endTime: 10 }
        const result = hasConflictWithItem(itemA, itemB)

        expect(result).toBe(false)
    })

    it('should return false if ItemA.endTime < itemB.startTime', () => {
        const itemA = { startTime: 0, endTime: 5 }
        const itemB = { startTime: 6, endTime: 10 }
        const result = hasConflictWithItem(itemA, itemB)

        expect(result).toBe(false)
    })
})

describe('clamp()', () => {
    it('should clamp to the min if value is below min', () => {
        expect(clamp(2, 5, 10)).toBe(5)
    })

    it('should return the value if value is between min & max', () => {
        expect(clamp(5, 2, 10)).toBe(5)
    })

    it('should clamp to the max if value is above max', () => {
        expect(clamp(10, 2, 5)).toBe(5)
    })
})
