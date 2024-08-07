import TimeDisplay from 'Components/VideoEditor/components/TimeDisplay'
import { FC, memo } from 'react'

type Props = {
    currentTime: number
    duration: number
}

const TimeInfo: FC<Props> = (props) => {
    // Why use Math.floor: We ignore milliseconds and can reduce re-rendering of currentTime TimeDisplay to once per second
    return (
        <div className="video-context-player__control video-context-player__time-info">
            <TimeDisplay className="current-time" timeInSeconds={Math.floor(props.currentTime)} />
            <span>/</span>
            <TimeDisplay className="duration" timeInSeconds={Math.floor(props.duration)} />
        </div>
    )
}

export default memo(TimeInfo)
