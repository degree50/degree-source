import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { CutOverlayIds } from '../CuttingMenu'
import Overlay from '../../components/Overlay'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import CutListItem from './CutListItem'

const mapStateToProps = (state: AppState) => ({
    cutList: selectors.data.solutions.selectCurrentCutIds(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CutlistOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(CutOverlayIds.allByCutOrder)
    }

    return (
        <Overlay closeCallback={close} title="Schnittreihenfolge">
            <ol className="video-editor__media-item-list-new">
                {props.cutList.map((id, index) => (
                    <CutListItem key={id} cutId={id} index={index} showPositionControls />
                ))}
            </ol>
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(CutlistOverlay))
