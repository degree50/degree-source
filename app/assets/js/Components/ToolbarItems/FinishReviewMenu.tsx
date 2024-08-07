import Button from 'Components/Button/Button'
import React from 'react'
import { useAppDispatch, useAppSelector } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

const finishReviewConfirmText =
    'Nach Abschluss der Überprüfung kann diese Lösung nicht mehr angepasst werden.\n\n Fortfahren?'

const FinishReviewMenu = () => {
    const needsReview = useAppSelector(selectors.data.selectMaterialSolutionNeedsReview)
    const selectedSolutionId = useAppSelector(selectors.data.materialSolution.selectSelectedSolutionId)

    const dispatch = useAppDispatch()

    const finishReview = () => {
        if (selectedSolutionId) {
            if (window.confirm(finishReviewConfirmText)) {
                // @ts-ignore
                dispatch(actions.data.materialSolution.finishReview(selectedSolutionId))
            }
        }
    }

    return (
        <div className="video-editor-menu">
            <Button
                title="Material Lösung akzeptieren"
                className="button button--type-primary video-editor__toolbar__button"
                onPress={finishReview}
                isDisabled={!needsReview || !selectedSolutionId}
            >
                <i className="fa-regular fa-circle-check" />
            </Button>
        </div>
    )
}

export default React.memo(FinishReviewMenu)
