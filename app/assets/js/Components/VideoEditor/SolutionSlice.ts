///////////
// STATE //
///////////

import { createSelector, createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Annotation, Cut, ExercisePhaseStatus, Solution, SolutionId, VideoCode, VideoCodePrototype } from './types'
import { setIn } from 'immutable'
import {
    ANNOTATIONS_API_PROPERTY,
    CUTLIST_API_PROPERTY,
    VIDEO_CODE_PROTOTYPE_API_PROPERTY,
    VIDEO_CODES_API_PROPERTY,
} from 'StimulusControllers/normalizeData'
import { initData } from './initData'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { actions as materialSolutionActions } from 'StimulusControllers/ExerciseAndSolutionStore/MaterialSolutionSlice'
import { annotationsSlice, AnnotationId } from 'Components/ToolbarItems/AnnotationsContext/AnnotationsSlice'
import { CutId, cuttingSlice } from 'Components/ToolbarItems/CuttingContext/CuttingSlice'
import {
    videoCodePrototypesSlice,
    VideoCodePrototypeId,
} from 'Components/ToolbarItems/VideoCodesContext/VideoCodePrototypesSlice'
import { videoCodesSlice, VideoCodeId } from 'Components/ToolbarItems/VideoCodesContext/VideoCodesSlice'

export type SolutionState = {
    allIds: Array<SolutionId>
    byId: Record<SolutionId, Solution>
    current?: SolutionId
    previous: SolutionId[]
}

export const initialState: SolutionState = {
    allIds: [],
    byId: {},
    current: undefined,
    previous: [],
}

/////////////
// REDUCER //
/////////////

export const SolutionSlice = createSlice({
    name: 'solutions',
    initialState,
    reducers: {
        // Cut position inside the cutlist is determined by the index of its id
        moveCutUp: (state, action: PayloadAction<CutId>) => {
            if (!state.current) {
                return state
            }

            const currentCutList = state.byId[state.current].solutionData.cutList
            const currentIndex = currentCutList.indexOf(action.payload)
            const newIndex = currentIndex - 1

            if (newIndex < 0) return state

            const newIds = currentCutList.map((cut, index, all) => {
                if (index === newIndex) {
                    return all[currentIndex]
                } else if (index === currentIndex) {
                    return all[newIndex]
                } else {
                    return cut
                }
            })

            return setIn(state, ['byId', state.current, 'solutionData', 'cutList'], newIds)
        },
        moveCutDown: (state, action: PayloadAction<CutId>) => {
            if (!state.current) {
                return state
            }

            const currentCutList = state.byId[state.current].solutionData.cutList
            const currentIndex = currentCutList.indexOf(action.payload)
            const newIndex = currentIndex + 1

            if (newIndex === currentCutList.length) return state

            const newIds = currentCutList.map((cut, index, all) => {
                if (index === newIndex) {
                    return all[currentIndex]
                } else if (index === currentIndex) {
                    return all[newIndex]
                } else {
                    return cut
                }
            })

            return setIn(state, ['byId', state.current, 'solutionData', 'cutList'], newIds)
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(initData, (_, action) => {
                return {
                    ...action.payload.solutions,
                    allIds: Object.keys(action.payload.solutions.byId),
                }
            })
            .addCase(materialSolutionActions.finishReview.fulfilled, (state, action) => {
                return {
                    ...state,
                    byId: {
                        ...state.byId,
                        [action.payload]: {
                            ...state.byId[action.payload],
                            status: ExercisePhaseStatus.BEENDET,
                        },
                    },
                }
            })
            .addCase(materialSolutionActions.setSelectedSolutionId, (state, action) => ({
                ...state,
                current: action.payload,
            }))
            .addCase(annotationsSlice.actions.append, (state, action: PayloadAction<Annotation>) => {
                if (!state.current) {
                    return state
                }

                const currentAnnotationIds = state.byId[state.current].solutionData.annotations
                const updatedAnnotations = [...currentAnnotationIds, action.payload.id]

                return setIn(
                    state,
                    ['byId', state.current, 'solutionData', ANNOTATIONS_API_PROPERTY],
                    updatedAnnotations
                )
            })
            .addCase(annotationsSlice.actions.remove, (state, action: PayloadAction<AnnotationId>) => {
                if (!state.current) {
                    return state
                }

                const currentAnnotationIds = state.byId[state.current].solutionData.annotations
                const updatedAnnotations = currentAnnotationIds.filter((id) => id !== action.payload)

                return setIn(
                    state,
                    ['byId', state.current, 'solutionData', ANNOTATIONS_API_PROPERTY],
                    updatedAnnotations
                )
            })
            .addCase(videoCodesSlice.actions.append, (state, action: PayloadAction<VideoCode>) => {
                if (!state.current) {
                    return state
                }

                const currentVideoCodeIds = state.byId[state.current].solutionData.videoCodes
                const updatedVideoCodes = [...currentVideoCodeIds, action.payload.id]

                return setIn(
                    state,
                    ['byId', state.current, 'solutionData', VIDEO_CODES_API_PROPERTY],
                    updatedVideoCodes
                )
            })
            .addCase(videoCodesSlice.actions.remove, (state, action: PayloadAction<VideoCodeId>) => {
                if (!state.current) {
                    return state
                }

                const currentVideoCodeIds = state.byId[state.current].solutionData.videoCodes
                const updatedVideoCodes = currentVideoCodeIds.filter((id) => id !== action.payload)

                return setIn(
                    state,
                    ['byId', state.current, 'solutionData', VIDEO_CODES_API_PROPERTY],
                    updatedVideoCodes
                )
            })
            .addCase(cuttingSlice.actions.append, (state, action: PayloadAction<Cut>) => {
                if (!state.current) {
                    return state
                }

                const currentCutIds = state.byId[state.current].solutionData.cutList
                const updatedCuts = [...currentCutIds, action.payload.id]

                return setIn(state, ['byId', state.current, 'solutionData', CUTLIST_API_PROPERTY], updatedCuts)
            })
            .addCase(cuttingSlice.actions.remove, (state, action: PayloadAction<CutId>) => {
                if (!state.current) {
                    return state
                }

                const currentCutIds = state.byId[state.current].solutionData.cutList
                const updatedCuts = currentCutIds.filter((id) => id !== action.payload)

                return setIn(state, ['byId', state.current, 'solutionData', CUTLIST_API_PROPERTY], updatedCuts)
            })
            .addCase(videoCodePrototypesSlice.actions.append, (state, action: PayloadAction<VideoCodePrototype>) => {
                if (!state.current) {
                    return state
                }

                const currentIds = state.byId[state.current].solutionData.videoCodePrototypes
                const updatedPrototypes = [...currentIds, action.payload.id]

                return setIn(
                    state,
                    ['byId', state.current, 'solutionData', VIDEO_CODE_PROTOTYPE_API_PROPERTY],
                    updatedPrototypes
                )
            })
            .addCase(
                videoCodePrototypesSlice.actions.remove,
                (
                    state,
                    action: PayloadAction<{
                        prototypeId: VideoCodePrototypeId
                        prototypeState: Record<VideoCodePrototypeId, VideoCodePrototype>
                    }>
                ) => {
                    if (!state.current) {
                        return state
                    }

                    const { prototypeId, prototypeState } = action.payload
                    const childIds = prototypeState[prototypeId].videoCodes
                    const allIdsToRemove = [prototypeId, ...childIds]

                    const currentIds = state.byId[state.current].solutionData.videoCodePrototypes
                    const updated = currentIds.filter((id) => !allIdsToRemove.includes(id))

                    return setIn(
                        state,
                        ['byId', state.current, 'solutionData', VIDEO_CODE_PROTOTYPE_API_PROPERTY],
                        updated
                    )
                }
            )
    },
})

///////////////
// SELECTORS //
///////////////
const selectAllIds = (state: AppState) => state.data.solutions.allIds
const selectById = (state: AppState) => state.data.solutions.byId
const selectSolutionById = (state: AppState, props: { solutionId: string }) =>
    state.data.solutions.byId[props.solutionId]
const selectCurrentId = (state: AppState) => state.data.solutions.current
const selectPreviousIds = (state: AppState) => state.data.solutions.previous
const selectCurrentSolutionStatus = (state: AppState) =>
    state.data.solutions.current ? state.data.solutions.byId[state.data.solutions.current]?.status : undefined

const selectIsCurrentSolution = (state: AppState, props: { solutionId?: string }) =>
    !!(props.solutionId && state.data.solutions.current === props.solutionId)

const selectPreviousSolutions = createSelector([selectById, selectPreviousIds], (byId, ids) =>
    ids.map((id) => byId[id])
)

const selectCurrentAnnotationIds = createSelector([selectById, selectCurrentId], (byId, currentId) =>
    currentId ? byId[currentId].solutionData.annotations : []
)

const selectCurrentVideoCodeIds = createSelector([selectById, selectCurrentId], (byId, currentId) =>
    currentId ? byId[currentId].solutionData.videoCodes : []
)

const selectCurrentCutIds = createSelector([selectById, selectCurrentId], (byId, currentId) =>
    currentId ? byId[currentId].solutionData.cutList : []
)

const selectCurrentSolutionVideoCodePrototypeIds = createSelector([selectById, selectCurrentId], (byId, currentId) =>
    currentId ? byId[currentId].solutionData.videoCodePrototypes : []
)

const selectPreviousSolutionsVideoCodePrototypeIds = createSelector(
    [selectById, selectPreviousIds],
    (byId, previousSolutionIds) => previousSolutionIds.flatMap((id) => byId[id].solutionData.videoCodePrototypes)
)

const selectCurrentSolutionOwner = createSelector([selectById, selectCurrentId], (byId, currentId) => {
    if (!currentId) return {}

    const solution = byId[currentId]
    return {
        userId: solution.userId,
        userName: solution.userName,
    }
})

const selectSolutionFromGroupPhase = createSelector([selectById, (_, id?: SolutionId) => id], (byId, solutionId) => {
    if (!solutionId) return false

    const solution = byId[solutionId]
    return solution.fromGroupPhase
})

const selectCurrentSolutionFromGroupPhase = createSelector([selectById, selectCurrentId], (byId, currentId) => {
    if (!currentId) return false

    const solution = byId[currentId]
    return solution.fromGroupPhase
})

const selectCurrentMaterialId = createSelector([selectById, selectCurrentId], (byId, currentId) =>
    currentId ? byId[currentId].solutionData.material : undefined
)

export const selectors = {
    selectAllIds,
    selectById,
    selectSolutionById,
    selectCurrentSolutionStatus,
    selectCurrentId,
    selectPreviousIds,
    selectIsCurrentSolution,
    selectPreviousSolutions,
    selectCurrentAnnotationIds,
    selectCurrentVideoCodeIds,
    selectCurrentCutIds,
    selectCurrentSolutionVideoCodePrototypeIds,
    selectPreviousSolutionsVideoCodePrototypeIds,
    selectCurrentSolutionOwner,
    selectSolutionFromGroupPhase,
    selectCurrentSolutionFromGroupPhase,
    selectCurrentMaterialId,
}
