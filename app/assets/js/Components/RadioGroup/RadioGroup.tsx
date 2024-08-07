import React, { FC, memo, ReactNode } from 'react'
import { useRadioGroup } from '@react-aria/radio'
import { RadioGroupState, useRadioGroupState } from '@react-stately/radio'
import { RadioGroupProps } from '@react-types/radio'

// WHY: Radio state does not accept null as Context
export const RadioContext = React.createContext<RadioGroupState | null>(null) as React.Context<RadioGroupState>

type Props = RadioGroupProps & {
    children: ReactNode
}

/**
 * Accessible radio group with react-aria.
 * @see https://react-spectrum.adobe.com/react-aria/useRadioGroup.html
 */
const RadioGroup = (props: Props) => {
    const { children, label } = props
    const state = useRadioGroupState(props)
    const { radioGroupProps, labelProps } = useRadioGroup(props, state)

    return (
        <div {...radioGroupProps}>
            <span {...labelProps}>{label}</span>
            <RadioContext.Provider value={state}>{children}</RadioContext.Provider>
        </div>
    )
}

export default memo(RadioGroup)
