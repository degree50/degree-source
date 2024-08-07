import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import VideoCodeListItem from './VideoCodeListItem'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = (state: AppState) => ({
    videoCodeIdsByStartTime: selectors.selectAllVideoCodeIdsByStartTime(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const AllVideoCodesOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.all)
    }

    return (
        <Overlay closeCallback={close} title="Alle Codierungen">
            {props.videoCodeIdsByStartTime.length > 0 ? (
                <ol className="video-editor__media-item-list-new">
                    {props.videoCodeIdsByStartTime.map((id, index) => (
                        <VideoCodeListItem key={id} videoCodeId={id} index={index} />
                    ))}
                </ol>
            ) : (
                <p tabIndex={0}>Keine VideoCodes vorhanden</p>
            )}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(AllVideoCodesOverlay))
