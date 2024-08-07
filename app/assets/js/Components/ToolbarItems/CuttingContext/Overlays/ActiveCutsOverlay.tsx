import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { CutOverlayIds } from '../CuttingMenu'
import Overlay from '../../components/Overlay'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import CutListItem from './CutListItem'

const mapStateToProps = (state: AppState) => ({
    activeCutIds: selectors.selectCurrentCutIdsAtCursor(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ActiveCutsOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(CutOverlayIds.active)
    }

    return (
        <Overlay closeCallback={close} title="Aktive Schnitte">
            {props.activeCutIds.length > 0 ? (
                <ol className="video-editor__media-item-list-new">
                    {props.activeCutIds.map((id, index) => (
                        <CutListItem key={id} cutId={id} index={index} />
                    ))}
                </ol>
            ) : (
                <p>Keine Schnitte aktiv</p>
            )}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ActiveCutsOverlay))
