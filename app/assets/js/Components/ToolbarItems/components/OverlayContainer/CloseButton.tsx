import React from 'react'

type Props = {
    onClick: () => void
    tabIndex?: number
}

const CloseButton = ({ onClick, tabIndex = 1 }: Props) => {
    return (
        <button
            aria-label="Schließen"
            title="Schließen"
            tabIndex={tabIndex}
            type="button"
            className="button button--type-outline-primary"
            onClick={onClick}
            data-focus-id="close-button"
        >
            <i className="fas fa-times" />
        </button>
    )
}

export default React.memo(CloseButton)
