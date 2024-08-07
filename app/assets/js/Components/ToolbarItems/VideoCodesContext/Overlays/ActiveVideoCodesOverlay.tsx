import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import VideoCodeListItem from './VideoCodeListItem'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = (state: AppState) => ({
    activeVideoCodeIds: selectors.selectAllActiveVideoCodeIdsAtCursor(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ActiveVideoCodesOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.active)
    }

    return (
        <Overlay closeCallback={close} title="Aktive Codierungen">
            {props.activeVideoCodeIds.length > 0 ? (
                <ol className="video-editor__media-item-list-new">
                    {props.activeVideoCodeIds.map((id, index) => (
                        <VideoCodeListItem key={id} videoCodeId={id} index={index} />
                    ))}
                </ol>
            ) : (
                <p tabIndex={0}>Keine Codierungen aktiv</p>
            )}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ActiveVideoCodesOverlay))
