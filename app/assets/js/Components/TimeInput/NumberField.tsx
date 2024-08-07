import React, { memo, useRef } from 'react'
import { NumberFieldStateProps, useNumberFieldState } from '@react-stately/numberfield'
import { useNumberField } from '@react-aria/numberfield'
import { useButton } from '@react-aria/button'
import { useLocale } from '@react-aria/i18n'

type Props = Omit<NumberFieldStateProps, 'locale'> & {
    'aria-label'?: string
}

const NumberField = (props: Props) => {
    const { locale } = useLocale()
    const state = useNumberFieldState({ ...props, locale })
    const inputRef = useRef<HTMLInputElement>(null)
    const incrRef = useRef<HTMLButtonElement>(null)
    const decRef = useRef<HTMLButtonElement>(null)

    const { incrementButtonProps, decrementButtonProps, groupProps, inputProps } = useNumberField(
        { 'aria-label': props['aria-label'] },
        state,
        inputRef
    )

    const { buttonProps: incrementProps } = useButton(incrementButtonProps, incrRef)
    const { buttonProps: decrementProps } = useButton(decrementButtonProps, decRef)

    const handleFocus = (e: React.FocusEvent<HTMLInputElement>) => {
        e.target.select()
    }

    return (
        <div className="number-field">
            <div className="group" {...groupProps}>
                <button className="number-field__inc-button" {...incrementProps} ref={incrRef}>
                    <i className="far fa-angle-up" />
                </button>
                <input {...inputProps} ref={inputRef} onFocus={handleFocus} />
                <button className="number-field__dec-button" {...decrementProps} ref={decRef}>
                    <i className="far fa-angle-down" />
                </button>
            </div>
        </div>
    )
}

export default memo(NumberField)
