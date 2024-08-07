import { clamp } from 'Components/VideoEditor/utils'
import {
    getHoursFromTimeSeconds,
    getMinutesFromTimeSeconds,
    getSecondsFromTimeSeconds,
} from 'Components/VideoEditor/utils/time'
import React, { ChangeEvent, FC, memo } from 'react'

type Props = {
    duration: number
    currentTime: number
    setCurrentTime: (time: number) => void
}

const ProgressControl: FC<Props> = (props) => {
    // TODO: compute step based on duration or use fixed value?
    const step = 1

    const handleProgressInputChange = (ev: ChangeEvent<HTMLInputElement>) => {
        props.setCurrentTime(parseFloat(ev.target.value))
    }

    const handleProgressKeyDown = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        // TODO: which combinations are _wanted_?
        const forwardsKeyCombination = ev.key === 'ArrowRight'
        const backwardsKeyCombination = ev.key === 'ArrowLeft'

        switch (true) {
            case forwardsKeyCombination:
                props.setCurrentTime(clamp(props.currentTime + step, 0, props.duration))
                ev.preventDefault()
                break
            case backwardsKeyCombination:
                props.setCurrentTime(clamp(props.currentTime - step, 0, props.duration))
                ev.preventDefault()
                break
            default:
            // ignore other keys
        }
    }

    const currentHours = getHoursFromTimeSeconds(props.currentTime)
    const currentMinutes = getMinutesFromTimeSeconds(props.currentTime)
    const currentSeconds = Math.floor(getSecondsFromTimeSeconds(props.currentTime))
    const durationHours = getHoursFromTimeSeconds(props.duration)
    const durationMinutes = getMinutesFromTimeSeconds(props.duration)
    const durationSeconds = Math.floor(getSecondsFromTimeSeconds(props.duration))
    const valueText = `
        ${currentHours > 0 ? currentHours + ' Stunden' : ''}
        ${currentMinutes > 0 ? currentMinutes + ' Minuten' : ''}
        ${currentSeconds + ' Sekunden'}
        von
        ${durationHours > 0 ? durationHours + ' Stunden' : ''}
        ${durationMinutes > 0 ? durationMinutes + ' Minuten' : ''}
        ${durationSeconds + ' Sekunden'}
    `

    return (
        <div className="video-context-player__control video-context-player__control__progress">
            <input
                type="range"
                title="Progress"
                min={0}
                step={0.001}
                max={props.duration}
                value={props.currentTime}
                aria-label="Progress"
                aria-valuemin={0}
                aria-valuemax={props.duration}
                aria-valuetext={valueText}
                onChange={handleProgressInputChange}
                onKeyDown={handleProgressKeyDown}
            />
        </div>
    )
}

export default memo(ProgressControl)
