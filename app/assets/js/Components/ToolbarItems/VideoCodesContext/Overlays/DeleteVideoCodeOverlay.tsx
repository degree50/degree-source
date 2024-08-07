import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import Button from 'Components/Button/Button'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = (state: AppState) => ({
    currentlyEditedElementId: selectors.videoEditor.overlay.currentlyEditedElementId(state),
})

const mapDispatchToProps = {
    removeVideoCode: actions.data.videoCodes.remove,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const DeleteVideoCodeOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.remove)
    }

    const handleRemove = () => {
        if (props.currentlyEditedElementId !== undefined) {
            props.removeVideoCode(props.currentlyEditedElementId)
            props.syncSolution()
        }
        close()
    }

    return (
        <Overlay closeCallback={close} title="Codierung wirklich löschen?">
            <div className="button-group">
                <Button className="button button--type-grey" onPress={close} title="Löschvorgang Abbrechen">
                    <i className="fas fa-times" />
                    <span>Abbrechen</span>
                </Button>
                <Button className="button button--type-primary" onPress={handleRemove} title="Löschvorgang Bestätigen">
                    <i className="fas fa-check" />
                    <span>Löschen</span>
                </Button>
            </div>
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(DeleteVideoCodeOverlay))
