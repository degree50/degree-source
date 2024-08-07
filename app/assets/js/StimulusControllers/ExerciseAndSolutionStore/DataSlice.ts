import { combineReducers, createSelector } from '@reduxjs/toolkit'
import {
    annotationsSlice,
    AnnotationsState,
    selectors as annotationSelectors,
} from 'Components/ToolbarItems/AnnotationsContext/AnnotationsSlice'
import {
    CutsState,
    cuttingSlice,
    selectors as cutsSelectors,
} from 'Components/ToolbarItems/CuttingContext/CuttingSlice'
import {
    selectors as videoCodePrototypeSelectors,
    videoCodePrototypesSlice,
    VideoCodePrototypesState,
} from 'Components/ToolbarItems/VideoCodesContext/VideoCodePrototypesSlice'
import {
    selectors as videoCodeSelectors,
    videoCodesSlice,
    VideoCodesState,
} from 'Components/ToolbarItems/VideoCodesContext/VideoCodesSlice'

import MaterialsSlice, { MaterialsState, selectors as materialSelectors } from './MaterialsSlice'
import { composedMaterialSelectors } from 'Components/VideoEditor/composedSelectors/materials'
import MaterialSolutionSlice, {
    MaterialSolutionState,
    actions as materialSolutionActions,
    selectors as materialSolutionSelectors,
} from 'StimulusControllers/ExerciseAndSolutionStore/MaterialSolutionSlice'
import { composedAnnotationSelectors } from 'Components/VideoEditor/composedSelectors/annotations'
import { composedCutSelectors } from 'Components/VideoEditor/composedSelectors/cuts'
import { composedPrototypeSelectors } from 'Components/VideoEditor/composedSelectors/prototypes'
import { composedVideoCodeSelectors } from 'Components/VideoEditor/composedSelectors/videoCodes'
import { selectors as solutionSelectors, SolutionState, SolutionSlice } from 'Components/VideoEditor/SolutionSlice'
import { ExercisePhaseStatus } from 'Components/VideoEditor/types'

export type DataState = {
    solutions: SolutionState
    annotations: AnnotationsState
    videoCodes: VideoCodesState
    videoCodePrototypes: VideoCodePrototypesState
    cuts: CutsState
    materials: MaterialsState
    materialSolution: MaterialSolutionState
}

export default combineReducers({
    solutions: SolutionSlice.reducer,
    annotations: annotationsSlice.reducer,
    videoCodes: videoCodesSlice.reducer,
    videoCodePrototypes: videoCodePrototypesSlice.reducer,
    cuts: cuttingSlice.reducer,
    materials: MaterialsSlice.reducer,
    materialSolution: MaterialSolutionSlice.reducer,
})

export const actions = {
    solutions: SolutionSlice.actions,
    annotations: annotationsSlice.actions,
    videoCodes: videoCodesSlice.actions,
    videoCodePrototypes: videoCodePrototypesSlice.actions,
    cuts: cuttingSlice.actions,
    materials: MaterialsSlice.actions,
    materialSolution: materialSolutionActions,
}

const selectMaterialSolutionNeedsReview = createSelector(
    [solutionSelectors.selectById, materialSolutionSelectors.selectSelectedSolutionId],
    (solutionsById, selectedMaterialSolutionId) =>
        selectedMaterialSolutionId
            ? solutionsById[selectedMaterialSolutionId]?.status === ExercisePhaseStatus.IN_REVIEW
            : false
)

export const selectors = {
    solutions: solutionSelectors,
    annotations: annotationSelectors,
    videoCodes: videoCodeSelectors,
    videoCodePrototypes: videoCodePrototypeSelectors,
    cuts: cutsSelectors,
    materials: materialSelectors,
    materialSolution: materialSolutionSelectors,
    selectMaterialSolutionNeedsReview,

    ...composedVideoCodeSelectors,
    ...composedAnnotationSelectors,
    ...composedCutSelectors,
    ...composedPrototypeSelectors,
    ...composedMaterialSelectors,
}
