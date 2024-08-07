import { createAsyncThunk, createSelector, createSlice, PayloadAction } from '@reduxjs/toolkit'
import { SolutionId } from 'Components/VideoEditor/types'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { selectors as videoEditorSelectors } from 'Components/VideoEditor/VideoEditorSlice'
import { selectors as configSelectors } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import { filterSlice } from 'Components/ToolbarItems/FilterContext/FilterSlice'

export type MaterialSolutionState = {
    selectedSolutionId?: SolutionId
    comparedSolutionId?: SolutionId
    // TODO: Naming
    shouldCompare: boolean
    // TODO: Right place, correct naming?
    isReadonly: boolean
}

const initialState: MaterialSolutionState = {
    isReadonly: true,
    shouldCompare: false,
}

const finishReview = createAsyncThunk('MaterialSolution/finishReview', async (solutionId: string) => {
    await fetch(`/exercise-phase-solution/finish-review/${solutionId}`, {
        method: 'POST',
    })

    return solutionId
})

const MaterialSolutionSlice = createSlice({
    name: 'MaterialSolution',
    initialState,
    reducers: {
        setSelectedSolutionId: (state, action: PayloadAction<SolutionId | undefined>) => ({
            ...state,
            selectedSolutionId: action.payload,
        }),
        setComparedSolutionId: (state, action: PayloadAction<SolutionId | undefined>) => ({
            ...state,
            comparedSolutionId: action.payload,
        }),
        toggleIsReadonly: (state) => ({
            ...state,
            isReadonly: !state.isReadonly,
        }),
        setShouldCompare: (state, action: PayloadAction<boolean>) => ({
            ...state,
            shouldCompare: action.payload,
        }),
    },
    extraReducers: (builder) =>
        builder
            .addCase(filterSlice.actions.setGlobalSolutionFilter, (state, action) => {
                return {
                    ...state,
                    selectedSolutionId: action.payload === 'none' ? undefined : state.selectedSolutionId,
                    comparedSolutionId: action.payload === 'none' ? undefined : state.comparedSolutionId,
                }
            })
            .addCase(finishReview.fulfilled, (state) => {
                return {
                    ...state,
                    isReadonly: true,
                }
            })
            .addCase(filterSlice.actions.setPreviousSolution, (state, action) => {
                const selectedSolutionIdHasBeenToggled = action.payload.id === state.selectedSolutionId
                const comparedSolutionIdHasBeenToggled = action.payload.id === state.comparedSolutionId

                return {
                    ...state,
                    selectedSolutionId:
                        selectedSolutionIdHasBeenToggled && !action.payload.isVisible
                            ? undefined
                            : state.selectedSolutionId,
                    comparedSolutionId:
                        comparedSolutionIdHasBeenToggled && !action.payload.isVisible
                            ? undefined
                            : state.comparedSolutionId,
                }
            }),
})

const selectSelectedSolutionId = (state: AppState) => state.data.materialSolution.selectedSolutionId
const selectComparedSolutionId = (state: AppState) => state.data.materialSolution.comparedSolutionId
const selectIsReadonly = (state: AppState) => state.data.materialSolution.isReadonly
const selectShouldCompare = (state: AppState) => state.data.materialSolution.shouldCompare

function isNotLastElementInArray<T>(array: Array<T>, element: T): boolean {
    return array.indexOf(element) < array.length - 1
}

function isNotFirstElementInArray<T>(array: Array<T>, element: T): boolean {
    return array.indexOf(element) > 0
}

const selectCanSelectNextSolution = createSelector(
    [videoEditorSelectors.filter.selectVisiblePreviousSolutionIds, selectSelectedSolutionId, selectIsReadonly],
    (solutionIds, selectedSolutionId, isReadonly) =>
        Boolean(
            selectedSolutionId && isNotLastElementInArray(solutionIds, selectedSolutionId)
            // WHY
            // If the selected solution is not readonly, it can currently be edited.
            // The user should not be able to change the selected solution during editing!
        ) && isReadonly
)

const selectCanCompareNextSolution = createSelector(
    [videoEditorSelectors.filter.selectVisiblePreviousSolutionIds, selectComparedSolutionId],
    (solutionIds, comparedSolutionId) =>
        Boolean(comparedSolutionId && isNotLastElementInArray(solutionIds, comparedSolutionId))
)

const selectCanSelectPreviousSolution = createSelector(
    [videoEditorSelectors.filter.selectVisiblePreviousSolutionIds, selectSelectedSolutionId, selectIsReadonly],
    (solutionIds, selectedSolutionId, isReadonly) =>
        Boolean(
            selectedSolutionId && isNotFirstElementInArray(solutionIds, selectedSolutionId)
            // WHY
            // If the selected solution is not readonly, it can currently be edited.
            // The user should not be able to change the selected solution during editing!
        ) && isReadonly
)

const selectCanComparePreviousSolution = createSelector(
    [videoEditorSelectors.filter.selectVisiblePreviousSolutionIds, selectComparedSolutionId],
    (solutionIds, comparedSolutionId) =>
        Boolean(comparedSolutionId && isNotFirstElementInArray(solutionIds, comparedSolutionId))
)

export const selectors = {
    selectSelectedSolutionId,
    selectComparedSolutionId,
    selectIsReadonly,
    selectCanSelectNextSolution,
    selectCanSelectPreviousSolution,
    selectCanCompareNextSolution,
    selectCanComparePreviousSolution,
    selectShouldCompare,
}

export const actions = {
    ...MaterialSolutionSlice.actions,
    finishReview,
}
export default MaterialSolutionSlice
