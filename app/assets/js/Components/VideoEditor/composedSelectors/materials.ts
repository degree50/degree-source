import { createSelector } from '@reduxjs/toolkit'
import { selectors as solutionSelectors } from '../SolutionSlice'
import { selectors as materialSelectors } from 'StimulusControllers/ExerciseAndSolutionStore/MaterialsSlice'

const selectMaterialOfCurrentSolution = createSelector(
    [solutionSelectors.selectCurrentMaterialId, materialSelectors.byId],
    (currentMaterialId, byId) => (currentMaterialId ? byId[currentMaterialId] : undefined)
)

export const composedMaterialSelectors = {
    selectMaterialOfCurrentSolution,
}
