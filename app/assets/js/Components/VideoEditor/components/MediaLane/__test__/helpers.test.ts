import { calculateTimelineStartTime } from '../helpers'

describe('calculateTimelineStartTime()', () => {
    it('should return regular result', () => {
        const result = calculateTimelineStartTime(10, 10, 20)

        expect(result).toBe(5)
    })

    it.todo('should return result adjusted for the right bound')

    it.todo('should return result adjusted for the left bound')
})
