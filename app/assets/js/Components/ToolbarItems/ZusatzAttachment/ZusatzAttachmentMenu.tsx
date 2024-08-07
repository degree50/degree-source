import Button from 'Components/Button/Button'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { ZUSATZ_ATTACHMENTS_OVERLAY_ID } from './ZusatzAttachmentOverlay'

const mapStateToProps = () => ({})

const mapDispatchToProps = {
    setOverlay: actions.overlay.setOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ZusatzAttachmentMenu: FC<Props> = (props) => {
    const handleClick = () => {
        props.setOverlay({
            overlayId: ZUSATZ_ATTACHMENTS_OVERLAY_ID,
            closeOthers: true,
        })
    }

    return (
        <div className="video-editor-menu">
            <Button
                title="Zusätzliche Anhänge"
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handleClick}
            >
                <i className="fas fa-folder-open" />
            </Button>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ZusatzAttachmentMenu))
