import Button from 'Components/Button/Button'
import React from 'react'

type Props = {
    onClick: () => void
}

const RemoveButton = ({ onClick }: Props) => {
    return (
        <Button
            className={'button button--type-outline-danger button--size-small'}
            title={'Code löschen'}
            onPress={onClick}
        >
            <i className={'fas fa-trash'} />
        </Button>
    )
}

export default React.memo(RemoveButton)
