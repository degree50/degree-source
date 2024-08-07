import DT from 'duration-time-conversion'
import { clamp } from '.'

export const DEFAULT_SECOND_STEP = 1

export function sleep(ms = 0) {
    return new Promise((resolve) => setTimeout(resolve, ms))
}

export function secondToTime(seconds = 0) {
    return DT.d2t(seconds.toFixed(3))
}

export function timeToSecond(time: string): number {
    return DT.t2d(time)
}

export const getSecondsFromTimeSeconds = (seconds: number) => seconds % 60
export const getMinutesFromTimeSeconds = (seconds: number) => Math.floor(seconds / 60) % 60
export const getHoursFromTimeSeconds = (seconds: number) => Math.floor(seconds / 60 / 60)

export const timeNumberFormat = {
    minimumIntegerDigits: 2,
    maximumFractionDigits: 0,
    useGrouping: false,
}

/**
 * Formatter for number of hours to string representation.
 *
 * Example:
 *  HoursStringFormatter.format(5) -> "05"
 */
export const HoursStringFormatter = Intl.NumberFormat('de-DE', timeNumberFormat)

/**
 * Formatter for number of minutes to string representation.
 *
 * Example:
 *  MinutesStringFormatter.format(2) -> "02"
 */
export const MinutesStringFormatter = Intl.NumberFormat('de-DE', timeNumberFormat)

/**
 * Formatter for number of seconds to string representation.
 *
 * Example:
 *  SecondsStringFormatter.format(1) -> "01"
 */
export const SecondsStringFormatter = Intl.NumberFormat('de-DE', timeNumberFormat)

/**
 * Formatter for number of seconds to string representation.
 * This representation is including the milliseconds as fraction digits.
 *
 * Example:
 *  SecondsStringFormatter.format(23) -> "23.000"
 *  SecondsStringFormatter.format(5.23) -> "05.230"
 */
export const SecondsWithMillisecondsStringFormatter = Intl.NumberFormat('de-DE', {
    ...timeNumberFormat,
    minimumFractionDigits: 3,
    maximumFractionDigits: 3,
})

export const MillisecondsStringFormatter = Intl.NumberFormat('de-DE', {
    minimumIntegerDigits: 3,
    maximumFractionDigits: 0,
})

export type TimeValues = {
    hours: number
    minutes: number
    seconds: number
    ms: number
}

export const secondsToTimeString = (timeInSeconds: number): string => {
    return [
        HoursStringFormatter.format(getHoursFromTimeSeconds(timeInSeconds)),
        MinutesStringFormatter.format(getMinutesFromTimeSeconds(timeInSeconds)),
        SecondsStringFormatter.format(getSecondsFromTimeSeconds(timeInSeconds)),
    ].join(':')
}

/**
 * Get TimeString in format 'hh:mm:ss.ms' from hours, minutes and seconds values.
 */
export const timeValuesToTimeString = ({ hours, minutes, seconds, ms }: TimeValues): string => {
    return [
        HoursStringFormatter.format(hours),
        MinutesStringFormatter.format(minutes),
        SecondsStringFormatter.format(seconds),
    ]
        .join(':')
        .concat('.', MillisecondsStringFormatter.format(ms))
}

/**
 * Get hours, minutes and seconds from TimeString in format 'hh:mm:ss.ms'
 */
export const timeStringToTimeValues = (timeString: string): TimeValues => {
    const [hours, minutes, seconds] = timeString.split(':')
    return {
        hours: parseInt(hours),
        minutes: parseInt(minutes),
        seconds: parseInt(seconds),
        ms: (parseFloat(seconds) * 1000) % 1000,
    }
}

export const sortByStartTime = <T extends { id: string; start: string }>(entities: T[]): T[] =>
    [...entities].sort((a, b) => {
        if (a.start < b.start) {
            return -1
        } else if (a.start > b.start) {
            return 1
        } else {
            return 0
        }
    })

export const adjustEndTimeByStart = (startTime: string, endTime: string, duration: number) => {
    return secondToTime(clamp(timeToSecond(endTime), timeToSecond(startTime) + DEFAULT_SECOND_STEP, duration))
}
