import { timeStringToTimeValues, TimeValues, timeValuesToTimeString } from '../time'

describe('Conversion to and from TimeString (hh:mm:ss.ms)', () => {
    it('should convert properly from TimeString to TimeValues', () => {
        const timeString = '23:05:00.042'
        const expected: TimeValues = {
            hours: 23,
            minutes: 5,
            seconds: 0,
            ms: 42,
        }

        expect(timeStringToTimeValues(timeString)).toEqual(expected)
    })

    it('should convert properly from TimeValues to TimeString', () => {
        const timeValues: TimeValues = {
            hours: 55,
            minutes: 0,
            seconds: 42,
            ms: 23,
        }
        const expected = '55:00:42.023'

        expect(timeValuesToTimeString(timeValues)).toEqual(expected)
    })
})
