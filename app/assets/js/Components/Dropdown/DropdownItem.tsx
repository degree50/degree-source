import React, { useRef } from 'react'
import { useMenu, useMenuItem } from '@react-aria/menu'
import { mergeProps } from '@react-aria/utils'
import { useFocus } from '@react-aria/interactions'
import { TreeState } from '@react-stately/tree'
import { Item } from '@react-stately/collections'

type DropdownItemProps = {
    item: typeof Item.prototype
    state: TreeState<React.ReactNode>
    onAction: (key: React.Key) => void
    onClose: () => void
}

const DropdownItem = ({ item, state, onAction, onClose }: DropdownItemProps) => {
    // Get props for the menu item element
    const ref: React.RefObject<HTMLLIElement> = useRef(null)
    const { menuItemProps } = useMenuItem(
        {
            key: item.key,
            isDisabled: item.isDisabled,
            onAction,
            onClose,
        },
        state,
        ref
    )

    // Handle focus events, so we can apply highlighted
    // style to the focused menu item
    const [isFocused, setFocused] = React.useState(false)
    const { focusProps } = useFocus({ onFocusChange: setFocused })

    return (
        <li
            {...mergeProps(menuItemProps, focusProps)}
            className={'dropdown-item'}
            ref={ref}
            style={{
                background: isFocused ? 'gray' : '',
                color: isFocused ? 'white' : '',
            }}
        >
            {item.rendered}
        </li>
    )
}

export default DropdownItem
