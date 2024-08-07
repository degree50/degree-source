import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import React, { FC, memo } from 'react'
import { connect } from 'react-redux'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import Overlay from '../../components/Overlay'
import Button from 'Components/Button/Button'
import { remove } from 'immutable'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => ({
    currentlyEditedElementId: selectors.videoEditor.overlay.currentlyEditedElementId(state),
    annotationIds: selectors.data.solutions.selectCurrentAnnotationIds(state),
})

const mapDispatchToProps = {
    removeAnnotation: actions.data.annotations.remove,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const getFocusNextElement = (annotationIds: Array<string>, deletedAnnotationId: string) => {
    if (annotationIds.length > 1) {
        const deletedAnnotationIndex = annotationIds.findIndex((id) => id === deletedAnnotationId)
        const annotationIdsWithoutDeletedElement = remove(annotationIds, deletedAnnotationIndex)

        // The next annotation should be that takes the place (index) of the deleted one
        // If the last element is deleted the element "before" it will be focused
        const nextAnnotationIdToFocus =
            annotationIdsWithoutDeletedElement[
                Math.min(deletedAnnotationIndex, annotationIdsWithoutDeletedElement.length - 1)
            ]

        return () => {
            document.querySelector<HTMLElement>(`[data-focus-id="${nextAnnotationIdToFocus}"]`)?.focus()
        }
    } else {
        return () => {
            document.querySelector<HTMLElement>(`[data-focus-id="close-button"]`)?.focus()
        }
    }
}

const DeleteAnnotationOverlay: FC<Props> = (props) => {
    const close = () => {
        props.closeOverlay(AnnotationOverlayIds.remove)
    }

    const handleRemove = () => {
        if (props.currentlyEditedElementId !== undefined) {
            const setNewFocus = getFocusNextElement(props.annotationIds, props.currentlyEditedElementId)
            props.removeAnnotation(props.currentlyEditedElementId)
            props.syncSolution()
            close()
            // focus the next plausible DOM element
            setNewFocus()
        } else {
            close()
        }
    }

    return (
        <Overlay closeCallback={close} title="Annotation wirklich löschen?">
            <div className="button-group">
                <Button className="button button--type-grey" onPress={close} title="Löschvorgang Abbrechen">
                    <i className="fas fa-times" />
                    <span>Abbrechen</span>
                </Button>
                <Button className="button button--type-primary" onPress={handleRemove} title="Löschvorgang Löschen">
                    <i className="fas fa-check" />
                    <span>Löschen</span>
                </Button>
            </div>
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(DeleteAnnotationOverlay))
