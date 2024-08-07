import MenuButton from 'Components/VideoEditor/components/MenuButton'
import MenuItem from 'Components/VideoEditor/components/MenuItem'
import React, { FC, memo } from 'react'

type Props = {
    playbackRate: number
    setPlaybackRate: (playbackRate: number) => void
}

const PlaybackRateControl: FC<Props> = (props) => {
    const label = `${props.playbackRate}x`

    return (
        <MenuButton
            ariaLabel={`PlaybackRate ${label}`}
            icon={<i className="fas fa-tachometer-alt" />}
            label={label}
            small
        >
            <MenuItem ariaLabel="0.5x" label="0.5x" onClick={() => props.setPlaybackRate(0.5)} />
            <MenuItem ariaLabel="1x" label="1x" onClick={() => props.setPlaybackRate(1)} />
            <MenuItem ariaLabel="1.5x" label="1.5x" onClick={() => props.setPlaybackRate(1.5)} />
            <MenuItem ariaLabel="2x" label="2x" onClick={() => props.setPlaybackRate(2)} />
            <MenuItem ariaLabel="3x" label="3x" onClick={() => props.setPlaybackRate(3)} />
        </MenuButton>
    )
}

export default memo(PlaybackRateControl)
