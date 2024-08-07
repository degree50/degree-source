import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import Overlay from '../../components/Overlay'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import AnnotationListItem from './AnnotationListItem'

const mapStateToProps = (state: AppState) => ({
    activeAnnotationIds: selectors.selectAllActiveAnnotationIdsAtCursor(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ActiveAnnotationsOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(AnnotationOverlayIds.active)
    }

    return (
        <Overlay closeCallback={close} title="Aktive Annotationen">
            {props.activeAnnotationIds.length > 0 ? (
                <ol className="video-editor__media-item-list-new">
                    {props.activeAnnotationIds.map((id, index) => (
                        <AnnotationListItem key={id} annotationId={id} index={index} />
                    ))}
                </ol>
            ) : (
                <p>Keine Annotationen aktiv</p>
            )}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ActiveAnnotationsOverlay))
