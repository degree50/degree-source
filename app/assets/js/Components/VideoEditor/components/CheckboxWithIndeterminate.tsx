import React, { FC, memo, useEffect, useRef, ChangeEvent, AriaAttributes } from 'react'

export enum CheckboxValue {
    CHECKED = 'checked',
    UNCHECKED = 'unchecked',
    INDETERMINATE = 'indeterminate',
}

const mapCheckboxValueToChecked = (value: CheckboxValue): boolean => {
    switch (value) {
        case CheckboxValue.CHECKED:
            return true
        case CheckboxValue.UNCHECKED:
            return false
        case CheckboxValue.INDETERMINATE:
            // WHY: setting checked to undefined will make React register the input as uncontrolled
            return false
    }
}

const mapCheckedToCheckboxValue = (value: boolean): CheckboxValue => {
    switch (value) {
        case true:
            return CheckboxValue.CHECKED
        case false:
            return CheckboxValue.UNCHECKED
    }
}

type Props = {
    value: CheckboxValue
    handleChange: (value: CheckboxValue) => void
    id?: string
    // WHY: We could also use an intersection of Props & AriaAttributes
    // but we only forward those below for now so that seems the more pragmatic option
    // for now
    'aria-label'?: AriaAttributes['aria-label']
    'aria-describedby'?: AriaAttributes['aria-describedby']
}

// TODO: Remove in favor of Components/Checkbox
const CheckboxWithIndeterminate: FC<Props> = (props) => {
    const checked = mapCheckboxValueToChecked(props.value)
    const ref = useRef<HTMLInputElement>(null)
    const handleChange = (ev: ChangeEvent<HTMLInputElement>) => {
        props.handleChange(mapCheckedToCheckboxValue(ev.target.checked))
    }

    useEffect(() => {
        if (ref.current) {
            if (props.value === CheckboxValue.INDETERMINATE) {
                // WHY: this is not possible with just HTML, so we have to set it like this
                // Link: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/checkbox#indeterminate_state_checkboxes
                ref.current.indeterminate = true // eslint-disable-line
            } else {
                // WHY: see above
                ref.current.indeterminate = false // eslint-disable-line
            }
        }
    }, [props.value])

    return (
        <input
            ref={ref}
            id={props.id}
            type="checkbox"
            checked={checked}
            onChange={handleChange}
            aria-label={props['aria-label']}
            aria-describedby={props['aria-describedby']}
        />
    )
}

export default memo(CheckboxWithIndeterminate)
