import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { ConfigStateSlice, selectors } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import Overlay from '../components/Overlay'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'

export const AUFGABE_OVERLAY_ID = 'overlay/aufgabe'

const mapStateToProps = (state: ConfigStateSlice) => ({
    title: selectors.selectTitle(state),
    description: selectors.selectDescription(state),
})

const mapDispatchToProps = {
    unsetOverlay: actions.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const AufgabeOverlay: FC<Props> = (props) => {
    const handleClose = () => {
        props.unsetOverlay(AUFGABE_OVERLAY_ID)
    }

    return (
        <Overlay title="Aufgabe" closeCallback={handleClose}>
            <h3>{props.title}</h3>
            <div dangerouslySetInnerHTML={{ __html: props.description }} />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(AufgabeOverlay))
