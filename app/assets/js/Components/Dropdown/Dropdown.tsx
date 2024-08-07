import React, { useRef } from 'react'
import { useMenuTriggerState } from '@react-stately/menu'
import { useMenuTrigger } from '@react-aria/menu'
import { useButton } from '@react-aria/button'
import DropdownPopup from './DropdownPopup'

// TODO fix typing for children. ReactAria requires a 'CollectionChildren<T>' but this type is not exposed
// in the @react-stately/collection library
type DropdownProps = {
    children: any
    onAction: (key: React.Key) => void
    ariaLabel: string
}

const Dropdown = (props: DropdownProps) => {
    const { ariaLabel } = props

    // Create state based on the incoming props
    const state = useMenuTriggerState({
        align: 'start',
        direction: 'bottom',
        closeOnSelect: true,
    })

    // Get props for the menu trigger and menu elements
    // @ts-ignore
    const buttonRef: React.RefObject<HTMLButtonElement> = useRef()
    const { menuTriggerProps, menuProps } = useMenuTrigger({}, state, buttonRef)

    // Get props for the button based on the trigger props from useMenuTrigger
    const { buttonProps } = useButton(menuTriggerProps, buttonRef)

    return (
        <div className={'dropdown'}>
            <button
                {...buttonProps}
                ref={buttonRef}
                className={'button button--type-link'}
                aria-haspopup="true"
                aria-expanded={state.isOpen}
                aria-label={ariaLabel}
            >
                <i className={'fas fa-ellipsis-h'} />
            </button>
            {state.isOpen && (
                <DropdownPopup
                    children={props.children}
                    onAction={props.onAction}
                    domProps={menuProps}
                    autoFocus={state.focusStrategy}
                    onClose={() => state.close()}
                />
            )}
        </div>
    )
}

export default Dropdown
