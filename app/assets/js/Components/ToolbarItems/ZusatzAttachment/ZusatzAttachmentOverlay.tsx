import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import AttachmentViewer from 'StimulusControllers/ExercisePhaseApp/Components/AttachmentViewer/AttachmentViewer'
import Overlay from '../components/Overlay'

export const ZUSATZ_ATTACHMENTS_OVERLAY_ID = 'overlay/zusatz-anhang'

const mapDispatchToProps = {
    unsetOverlay: actions.overlay.unsetOverlay,
}

type Props = typeof mapDispatchToProps

const ZusatzAttachmentOverlay: FC<Props> = (props) => {
    const handleClose = () => {
        props.unsetOverlay(ZUSATZ_ATTACHMENTS_OVERLAY_ID)
    }

    return (
        <Overlay title="Zusätzliche Anhänge" closeCallback={handleClose} fullWidth>
            <AttachmentViewer />
        </Overlay>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(ZusatzAttachmentOverlay))
