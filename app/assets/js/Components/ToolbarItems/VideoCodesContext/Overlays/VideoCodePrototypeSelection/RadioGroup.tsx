import React from 'react'
import { useRadioGroupState } from '@react-stately/radio'
import { useRadioGroup } from '@react-aria/radio'
import { RadioGroupProps } from '@react-types/radio'

// Ignoring due to how react-aria handles the initialization
// @ts-ignore
export const RadioContext = React.createContext()

type Props = RadioGroupProps & {
    children: React.ReactNode
    className: string
    label?: string
}

const RadioGroup = (props: Props) => {
    const { children, label } = props
    const state = useRadioGroupState(props)
    const { radioGroupProps, labelProps } = useRadioGroup(props, state)

    return (
        <ul {...radioGroupProps} className={props.className}>
            {label && <span {...labelProps}>{label}</span>}
            <RadioContext.Provider value={state}>{children}</RadioContext.Provider>
        </ul>
    )
}

export default React.memo(RadioGroup)
