import React, { memo } from 'react'
import { RadioProps } from '@react-types/radio'
import { RadioContext } from './RadioGroup'
import { useRadio } from '@react-aria/radio'
import { useFocusRing } from '@react-aria/focus'
import { VisuallyHidden } from '@react-aria/visually-hidden'

const Radio = (props: RadioProps) => {
    const { children } = props
    const state = React.useContext(RadioContext)
    const ref = React.useRef(null)
    const { inputProps } = useRadio(props, state, ref)
    const { isFocusVisible, focusProps } = useFocusRing()

    const isSelected = state.selectedValue === props.value
    const strokeWidth = isSelected ? 6 : 2

    // TODO: move colors ect to CSS to use the theme
    return (
        <label style={{ display: 'flex', alignItems: 'center' }}>
            <VisuallyHidden>
                <input {...inputProps} {...focusProps} ref={ref} />
            </VisuallyHidden>
            <svg width={24} height={24} aria-hidden="true" style={{ marginRight: 4 }}>
                <circle
                    cx={12}
                    cy={12}
                    r={8 - strokeWidth / 2}
                    fill="none"
                    stroke={isSelected ? '#639a00' : '#616161'}
                    strokeWidth={strokeWidth}
                />
                {isFocusVisible && <circle cx={12} cy={12} r={11} fill="none" stroke="#ca7406" strokeWidth={2} />}
            </svg>
            {children}
        </label>
    )
}

export default memo(Radio)
