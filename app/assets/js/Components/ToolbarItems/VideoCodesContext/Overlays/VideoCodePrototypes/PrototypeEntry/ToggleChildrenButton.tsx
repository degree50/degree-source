import React from 'react'

type Props = {
    onClick: () => void
    showChildren: boolean
    title: string
}

const ToggleChildrenButton = ({ onClick, showChildren, title }: Props) => {
    return (
        <button
            type="button"
            className={'button button--type-outline-primary button--size-small'}
            onClick={onClick}
            title={title}
            aria-label={title}
        >
            <i className={showChildren ? 'fas fa-chevron-up' : 'fas fa-chevron-down'} />
        </button>
    )
}

export default React.memo(ToggleChildrenButton)
