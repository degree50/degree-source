import React, { memo, useCallback } from 'react'

type Props = {
    label: string
    onClick: () => void
    ariaLabel: string
    disabled?: boolean
    isActive?: boolean
}

const MenuItem = (props: Props) => {
    const className = [
        'menu__item',
        ...(props.disabled ? ['menu__item--disabled'] : []),
        ...(props.isActive ? ['menu__item--active'] : []),
    ].join(' ')

    const handleClick = useCallback(() => {
        if (!props.disabled) {
            props.onClick()
        }
    }, [props.onClick, props.disabled])

    const ariaLabel = props.disabled ? props.ariaLabel + '-deaktiviert' : props.ariaLabel

    return (
        <button
            className={className}
            onClick={handleClick}
            aria-label={ariaLabel}
            autoFocus={props.isActive}
            disabled={props.disabled}
        >
            {props.label}
        </button>
    )
}

export default memo(MenuItem)
