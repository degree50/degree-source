import { memo } from 'react'
import CKEditor from 'Components/CKEditor'
import MaterialEditorToolbar from 'Components/MaterialEditor/Toolbar/MaterialEditorToolbar'
import OverlayContainer from 'Components/ToolbarItems/components/OverlayContainer'

type Props = {
    material?: string
    onChange?: (value: string) => void
    readonly?: boolean
}

const MaterialEditor = (props: Props) => {
    const { material, onChange, readonly } = props

    return (
        <div className="material-editor" data-test-id="materialEditor">
            {material !== undefined ? (
                <CKEditor readonly={readonly} value={material} onChange={onChange} />
            ) : (
                <div>Es ist kein Material hinterlegt...</div>
            )}
            <MaterialEditorToolbar />
            <OverlayContainer />
        </div>
    )
}

export default memo(MaterialEditor)
