import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import Overlay from '../../components/Overlay'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import AnnotationListItem from './AnnotationListItem'

const mapStateToProps = (state: AppState) => ({
    annotationIdsByStartTime: selectors.selectAllAnnotationIdsByStartTime(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const AllAnnotationsOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(AnnotationOverlayIds.all)
    }

    return (
        <Overlay closeCallback={close} title="Alle Annotationen">
            {props.annotationIdsByStartTime.length > 0 ? (
                <ol className="video-editor__media-item-list-new">
                    {props.annotationIdsByStartTime.map((id, index) => (
                        <AnnotationListItem key={id} annotationId={id} index={index} />
                    ))}
                </ol>
            ) : (
                <p>Keine Annotationen vorhanden</p>
            )}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(AllAnnotationsOverlay))
