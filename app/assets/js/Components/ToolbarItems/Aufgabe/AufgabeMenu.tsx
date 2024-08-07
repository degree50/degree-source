import Button from 'Components/Button/Button'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { AUFGABE_OVERLAY_ID } from './AufgabeOverlay'

const mapDispatchToProps = {
    setOverlay: actions.overlay.setOverlay,
}

type Props = typeof mapDispatchToProps

const AufgabeMenu: FC<Props> = (props: Props) => {
    const handleClick = () => {
        props.setOverlay({ overlayId: AUFGABE_OVERLAY_ID, closeOthers: true })
    }

    return (
        <div className="video-editor-menu">
            <Button
                title="Aufgabe"
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handleClick}
            >
                <i className="fas fa-clipboard" />
            </Button>
        </div>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(AufgabeMenu))
