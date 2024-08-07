import Button from 'Components/Button/Button'
import React, { FC, memo } from 'react'

type Props = {
    isMuted: boolean
    toggleIsMuted: () => void
}

const MuteControl: FC<Props> = (props) => {
    const muteButtonLabel = props.isMuted ? 'Unmute' : 'Mute'
    const muteButtonIcon = `fas fa-${props.isMuted ? 'volume-mute' : 'volume-up'}`

    return (
        <Button
            className="button button--type-grey menu-button button--size-small"
            title={muteButtonLabel}
            onPress={props.toggleIsMuted}
        >
            <i className={muteButtonIcon} />
        </Button>
    )
}

export default memo(MuteControl)
