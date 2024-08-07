import React, { memo, useState } from 'react'
import CKEditor from '.'
import CustomCKEditor from 'Components/CKEditor/CustomCKEditor'

type Props = {
    initialValue?: string
    onChange: (value: string) => void
    onReady: (editor: CustomCKEditor) => void
}

/**
 * This is a standalone version of the CKEditor Component that handles its own state internally.
 */
const CKEditorStandalone = (props: Props) => {
    const [value, setValue] = useState(props.initialValue ?? '')

    const handleChange = (value: string) => {
        setValue(value)
        props.onChange(value)
    }

    return <CKEditor value={value} onChange={handleChange} onReady={props.onReady} />
}

export default memo(CKEditorStandalone)
