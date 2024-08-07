import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { CutOverlayIds } from '../CuttingMenu'
import Overlay from '../../components/Overlay'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import CutListItem from './CutListItem'

const mapStateToProps = (state: AppState) => ({
    cutIdsByStartTime: selectors.selectAllCutIdsByStartTime(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const AllCutsOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(CutOverlayIds.all)
    }

    return (
        <Overlay closeCallback={close} title="Alle Schnitte">
            <ol className="video-editor__media-item-list-new">
                {props.cutIdsByStartTime.map((id, index) => (
                    <CutListItem key={id} cutId={id} index={index} />
                ))}
            </ol>
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(AllCutsOverlay))
