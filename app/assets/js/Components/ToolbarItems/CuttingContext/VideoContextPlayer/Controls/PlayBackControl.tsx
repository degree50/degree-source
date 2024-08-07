import Button from 'Components/Button/Button'
import React, { FC, memo, useCallback } from 'react'

type Props = {
    isPaused: boolean
    toggleIsPaused: () => void
}

const PlaybackControl: FC<Props> = (props) => {
    const playbackButtonLabel = props.isPaused ? 'Play' : 'Pause'
    const playbackButtonIcon = `fas fa-${props.isPaused ? 'play' : 'pause'}`
    const handleClick = useCallback(() => props.toggleIsPaused(), [props.toggleIsPaused])

    return (
        <Button
            className="button button--type-grey menu-button button--size-small"
            title={playbackButtonLabel}
            onPress={handleClick}
        >
            <i className={playbackButtonIcon} />
        </Button>
    )
}

export default memo(PlaybackControl)
