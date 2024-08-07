import React, { memo, MouseEvent, useCallback, useMemo } from 'react'
import NumberField from './NumberField'
import { timeStringToTimeValues, timeValuesToTimeString } from '../VideoEditor/utils/time'

export const defaultTimeInputFormatOptions: Intl.NumberFormatOptions = {
    style: 'unit',
    unitDisplay: 'short',
}

type Props = {
    label: string

    /**
     * Time in format 'h:m:s.ms'
     */
    value: string
    max?: string
    min?: string

    hoursLabel?: string
    minutesLabel?: string
    secondsLabel?: string
    msLabel?: string

    showMs?: boolean

    onChange: (value: string) => void

    formatOptions?: Intl.NumberFormatOptions
}

const TimeInput = (props: Props) => {
    // WHY: Simulate label onClick behavior
    const focusFirstInput = useCallback((ev: MouseEvent<HTMLLabelElement | HTMLDivElement>) => {
        // WHY: type issue
        const eventTarget = ev.target as HTMLElement
        eventTarget.parentElement?.querySelector('input')?.focus()
    }, [])

    // NumberFormatOptions
    const hourFormatOpts = useMemo(
        () => ({
            ...(props.formatOptions ?? defaultTimeInputFormatOptions),
            unit: 'hour',
        }),
        [props.formatOptions]
    )
    const minuteFormatOpts = useMemo(
        () => ({
            ...(props.formatOptions ?? defaultTimeInputFormatOptions),
            unit: 'minute',
        }),
        [props.formatOptions]
    )
    const secondFormatOpts = useMemo(
        () => ({
            ...(props.formatOptions ?? defaultTimeInputFormatOptions),
            unit: 'second',
        }),
        [props.formatOptions]
    )
    const msFormatOpts = useMemo(
        () => ({
            ...(props.formatOptions ?? defaultTimeInputFormatOptions),
            unit: 'millisecond',
        }),
        [props.formatOptions]
    )

    const timeValues = timeStringToTimeValues(props.value)
    // NOTE: negative time handling not planned and shaped yet
    // TODO: validate that min < max ?
    const minTimeValues = timeStringToTimeValues(props.min ?? '00:00:00.000')
    const maxTimeValues = timeStringToTimeValues(props.max ?? '99:59:59.999')

    // TODO: maybe extract functions for this and write tests
    // special handling of max values because if '1h 1m 30s' is maximum, then '0h 20m 55s' is a valid value
    const maxHours = maxTimeValues.hours
    const maxMinutes = timeValues.hours < maxHours ? 59 : maxTimeValues.minutes
    const maxSeconds = timeValues.hours < maxHours || timeValues.minutes < maxMinutes ? 59 : maxTimeValues.seconds
    const maxMs =
        timeValues.hours < maxHours || timeValues.minutes < maxMinutes || timeValues.seconds < maxSeconds
            ? 999
            : maxTimeValues.ms

    const minHours = minTimeValues.hours
    const minMinutes = timeValues.hours > minHours ? 0 : minTimeValues.minutes
    const minSeconds = timeValues.hours > minHours || timeValues.minutes > minMinutes ? 0 : minTimeValues.seconds
    const minMs =
        timeValues.hours > minHours || timeValues.minutes > minMinutes || timeValues.seconds > minSeconds
            ? 0
            : minTimeValues.ms

    // TODO: Why not use clamp?
    const hours = Math.max(Math.min(timeValues.hours, maxHours), minHours)
    const minutes = Math.max(Math.min(timeValues.minutes, maxMinutes), minMinutes)
    const seconds = Math.max(Math.min(timeValues.seconds, maxSeconds), minSeconds)
    const ms = Math.max(Math.min(timeValues.ms, maxMs), minMs)

    const showMs = props.showMs ?? false

    const onChangeHours = (newHours: number) => {
        props.onChange(
            timeValuesToTimeString({
                hours: newHours,
                minutes,
                seconds,
                ms,
            })
        )
    }

    const onChangeMinutes = (newMinutes: number) => {
        props.onChange(
            timeValuesToTimeString({
                hours,
                minutes: newMinutes,
                seconds,
                ms,
            })
        )
    }

    const onChangeSeconds = (newSeconds: number) => {
        props.onChange(
            timeValuesToTimeString({
                hours,
                minutes,
                seconds: newSeconds,
                ms,
            })
        )
    }

    const onChangeMs = (newMs: number) => {
        props.onChange(
            timeValuesToTimeString({
                hours,
                minutes,
                seconds,
                ms: newMs,
            })
        )
    }

    return (
        <div className="time-input">
            <label className="time-input__label" onClick={focusFirstInput}>
                {props.label}
            </label>
            <div role="group" className="input" onClick={focusFirstInput}>
                <NumberField
                    value={hours}
                    defaultValue={0}
                    minValue={minHours}
                    maxValue={maxHours}
                    onChange={onChangeHours}
                    aria-label={props.hoursLabel}
                    formatOptions={hourFormatOpts}
                />
                <NumberField
                    value={minutes}
                    defaultValue={0}
                    minValue={minMinutes}
                    maxValue={maxMinutes}
                    onChange={onChangeMinutes}
                    aria-label={props.minutesLabel}
                    formatOptions={minuteFormatOpts}
                />
                <NumberField
                    value={seconds}
                    defaultValue={0}
                    minValue={minSeconds}
                    maxValue={maxSeconds}
                    onChange={onChangeSeconds}
                    aria-label={props.secondsLabel}
                    formatOptions={secondFormatOpts}
                />
                {showMs && (
                    <NumberField
                        value={ms}
                        defaultValue={0}
                        minValue={minMs}
                        maxValue={maxMs}
                        onChange={onChangeMs}
                        aria-label={props.msLabel}
                        formatOptions={msFormatOpts}
                    />
                )}
            </div>
        </div>
    )
}

export default memo(TimeInput)
