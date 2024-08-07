import Button from 'Components/Button/Button'
import React from 'react'

type Props = {
    moveUp: () => void
}

const MoveUpButton = ({ moveUp }: Props) => {
    return (
        <Button
            title="Schnitt nach vorn schieben"
            className="button button--type-primary button--size-small"
            onPress={moveUp}
        >
            <i className="fas fa-caret-up" />
        </Button>
    )
}

export default React.memo(MoveUpButton)
