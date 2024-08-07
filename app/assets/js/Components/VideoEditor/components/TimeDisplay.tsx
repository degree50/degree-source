import React, { FC, memo } from 'react'
import {
    getHoursFromTimeSeconds,
    getMinutesFromTimeSeconds,
    getSecondsFromTimeSeconds,
    HoursStringFormatter,
    MinutesStringFormatter,
    SecondsStringFormatter,
} from '../utils/time'

type Props = {
    timeInSeconds: number
    className?: string
    forceHours?: boolean
}

const TimeDisplay: FC<Props> = (props) => {
    const seconds = SecondsStringFormatter.format(getSecondsFromTimeSeconds(props.timeInSeconds))
    const minutes = MinutesStringFormatter.format(getMinutesFromTimeSeconds(props.timeInSeconds))
    const hours = HoursStringFormatter.format(getHoursFromTimeSeconds(props.timeInSeconds))

    const showHours = hours !== '00' || props.forceHours

    const className = `time-display ${props.className ?? ''}`

    return (
        <span className={className}>
            {showHours && (
                <>
                    <span>{hours}</span>
                    <span>:</span>
                </>
            )}
            <span>{minutes}</span>
            <span>:</span>
            <span>{seconds}</span>
        </span>
    )
}

export default memo(TimeDisplay)
