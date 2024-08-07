import React, { ChangeEvent, FC, memo } from 'react'

type Props = {
    volume: number
    onChange: (volume: number) => void
}

const VolumeControl: FC<Props> = (props) => {
    const handleVolumeChange = (ev: ChangeEvent<HTMLInputElement>) => {
        props.onChange(parseFloat(ev.target.value))
    }

    const volumeLabel = `${props.volume * 100} %`

    return (
        <div className="video-context-player__control video-context-player__control__volume">
            <input
                type="range"
                title={volumeLabel}
                min={0}
                max={1}
                step={0.05}
                value={props.volume}
                aria-label="Volume"
                aria-valuetext={volumeLabel}
                onChange={handleVolumeChange}
            />
        </div>
    )
}

export default memo(VolumeControl)
