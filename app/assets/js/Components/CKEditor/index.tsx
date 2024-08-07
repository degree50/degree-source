import React from 'react'
import { CKEditor as CKE5React } from '@ckeditor/ckeditor5-react'
import CustomCKEditor from 'Components/CKEditor/CustomCKEditor'

type Props = {
    value: string
    onChange?: (value: string) => void
    onReady?: (editor: CustomCKEditor) => void
    readonly?: boolean
}

const CKEditor = (props: Props) => {
    const handleChange = (_ev: any, editor: CustomCKEditor) => {
        if (props.onChange) {
            props.onChange(editor.getData())
        }
    }

    return (
        // @ts-ignore
        <CKE5React
            editor={CustomCKEditor}
            data={props.value}
            onChange={handleChange}
            onReady={props.onReady}
            disabled={props.readonly ?? false}
        />
    )
}

export default React.memo(CKEditor)
