import React, { useRef } from 'react'
import { useButton } from '@react-aria/button'

type ButtonProps = {
    onPress?: () => void
    children: React.ReactNode
    isDisabled?: boolean
    className: string
    id?: string
    title?: string
}

const Button = (props: ButtonProps) => {
    const buttonRef: React.RefObject<HTMLButtonElement> = useRef(null)
    const { buttonProps } = useButton(props, buttonRef)
    const { children } = props

    return (
        <button
            {...buttonProps}
            aria-label={props.title}
            title={props.title}
            id={props.id}
            className={props.className}
            disabled={props.isDisabled}
            ref={buttonRef}
        >
            {children}
        </button>
    )
}

export default Button
