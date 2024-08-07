import React from 'react'
import Button from 'Components/Button/Button'

type Props = {
    isFullHeight: boolean
    toggleFullHeight: () => void
}

const MediaLaneFullHeightToggle = (props: Props) => {
    const label = props.isFullHeight ? 'Vollbildmodus verlassen' : 'Vollbildmodus aktivieren'
    const iconName = props.isFullHeight ? 'minimize' : 'maximize'

    return (
        <Button
            className="button button--type-primary menu-button media-lane-toolbar__menu-button"
            onPress={() => props.toggleFullHeight()}
        >
            <i className={`far fa-${iconName}`}></i> {label}
        </Button>
    )
}

export default React.memo(MediaLaneFullHeightToggle)
