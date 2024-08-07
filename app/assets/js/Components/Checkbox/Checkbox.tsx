import React, { memo } from 'react'
import { VisuallyHidden } from '@react-aria/visually-hidden'
import { useFocusRing } from '@react-aria/focus'
import { useCheckbox } from '@react-aria/checkbox'
import { useToggleState } from '@react-stately/toggle'
import { AriaCheckboxProps, ToggleProps } from '@react-types/checkbox'

type Props = ToggleProps & AriaCheckboxProps

const Checkbox = (props: Props) => {
    const state = useToggleState(props)
    const ref = React.useRef<HTMLInputElement>(null)
    const { inputProps } = useCheckbox(props, state, ref)
    const { isFocusVisible, focusProps } = useFocusRing()

    const svgClassName = `checkbox__input ${isFocusVisible ? 'checkbox__input--has-focus' : ''} ${
        inputProps.checked ? 'checkbox__input--checked' : ''
    }`

    return (
        <label className="checkbox__label">
            <VisuallyHidden>
                <input {...inputProps} {...focusProps} ref={ref} />
            </VisuallyHidden>
            <svg className={svgClassName} aria-hidden="true">
                <rect className="checkbox__box" />
                {state.isSelected && (
                    <path
                        transform="translate(7 7)"
                        d={`M3.788 9A.999.999 0 0 1 3 8.615l-2.288-3a1 1 0 1 1 1.576-1.23l1.5 1.991 3.924-4.991a1 1 0 1 1 1.576 1.23l-4.712 6A.999.999 0 0 1 3.788 9z`}
                    />
                )}
                <rect className="checkbox__focus-box" />
            </svg>
            {props.children}
        </label>
    )
}

export default memo(Checkbox)
