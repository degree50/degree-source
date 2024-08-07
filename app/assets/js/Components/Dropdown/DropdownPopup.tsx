import React, { useRef } from 'react'
import { useMenu } from '@react-aria/menu'
import { FocusScope } from '@react-aria/focus'
import { mergeProps } from '@react-aria/utils'
import { useOverlay, DismissButton } from '@react-aria/overlays'
import { useTreeState, TreeState } from '@react-stately/tree'
import DropdownItem from './DropdownItem'

type DropdownPopupProps = {
    children: any
    onAction: (key: React.Key) => void
    domProps: React.HTMLAttributes<HTMLElement>
    autoFocus: boolean | 'first' | 'last' | undefined
    onClose: () => void
}

const DropdownPopup = (props: DropdownPopupProps) => {
    // Create menu state based on the incoming props
    const state: TreeState<React.ReactNode> = useTreeState({
        ...props,
        selectionMode: 'none',
    })

    // Get props for the menu element
    const ref: React.RefObject<HTMLUListElement> = useRef(null)
    const { menuProps } = useMenu(props, state, ref)

    // Handle events that should cause the menu to close,
    // e.g. blur, clicking outside, or pressing the escape key.
    const overlayRef: React.RefObject<HTMLDivElement> = useRef(null)
    const { overlayProps } = useOverlay(
        {
            onClose: props.onClose,
            shouldCloseOnBlur: true,
            isOpen: true,
            isDismissable: true,
        },
        overlayRef
    )

    // Wrap in <FocusScope> so that focus is restored back to the
    // trigger when the menu is closed. In addition, add hidden
    // <DismissButton> components at the start and end of the list
    // to allow screen reader users to dismiss the popup easily.
    return (
        <FocusScope restoreFocus>
            <div {...overlayProps} ref={overlayRef} className={'dropdown-menu show'}>
                <DismissButton onDismiss={props.onClose} />
                <ul {...mergeProps(menuProps, props.domProps)} ref={ref}>
                    {[...state.collection].map((item) => (
                        <DropdownItem
                            key={item.key}
                            item={item}
                            state={state}
                            onAction={props.onAction}
                            onClose={props.onClose}
                        />
                    ))}
                </ul>
                <DismissButton onDismiss={props.onClose} />
            </div>
        </FocusScope>
    )
}

export default DropdownPopup
