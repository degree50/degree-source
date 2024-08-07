import React from 'react'
import Button from 'Components/Button/Button'

type Props = {
    onClick: () => void
}

const InfoButton = ({ onClick }: Props) => {
    return (
        <Button
            onPress={onClick}
            className={'video-editor__media-items__memo-toggle'}
            data-testid="media-item-info-button"
        >
            <i className={'fas fa-info'} />
        </Button>
    )
}

export default React.memo(InfoButton)
