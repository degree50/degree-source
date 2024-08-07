///////////
// STATE //
///////////

import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { initData } from 'Components/VideoEditor/initData'
import { VideoCodePrototype } from 'Components/VideoEditor/types'
import { set, removeIn } from 'immutable'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

export type VideoCodePrototypeId = string

export type VideoCodePrototypesState = {
    byId: Record<VideoCodePrototypeId, VideoCodePrototype>
}

export const initialState: VideoCodePrototypesState = {
    byId: {},
}

/////////////
// REDUCER //
/////////////

export const videoCodePrototypesSlice = createSlice({
    name: 'videoCodePrototypes',
    initialState,
    reducers: {
        append: (
            state: VideoCodePrototypesState,
            action: PayloadAction<VideoCodePrototype>
        ): VideoCodePrototypesState => {
            const newPrototype = action.payload
            return {
                byId: {
                    ...state.byId,
                    [newPrototype.id]: newPrototype,
                },
            }
        },
        update: (
            state: VideoCodePrototypesState,
            action: PayloadAction<{ transientVideoCodePrototype: VideoCodePrototype }>
        ): VideoCodePrototypesState => {
            const { transientVideoCodePrototype } = action.payload

            return {
                ...state,
                byId: set(state.byId, transientVideoCodePrototype.id, transientVideoCodePrototype),
            }
        },
        remove: (
            state: VideoCodePrototypesState,
            action: PayloadAction<{
                prototypeId: VideoCodePrototypeId
                prototypeState: Record<VideoCodePrototypeId, VideoCodePrototype> // (needed inside SolutionSlice extraReducer! - therefore we keep it in here to keep the action type identical)
            }>
        ): VideoCodePrototypesState => {
            const { prototypeId } = action.payload
            const childIds = state.byId[prototypeId].videoCodes

            const newById = [prototypeId, ...childIds].reduce((acc, id) => {
                return removeIn(acc, [id])
            }, state.byId)

            return { byId: newById }
        },
    },
    extraReducers: (builder) => {
        builder.addCase(initData, (_, action) => {
            return action.payload.videoCodePrototypes
        })
    },
})

///////////////
// SELECTORS //
///////////////

const selectById = (state: AppState) => state.data.videoCodePrototypes.byId
const selectPrototypeById = (state: AppState, props: { videoCodeId: VideoCodePrototypeId }) =>
    state.data.videoCodePrototypes.byId[props.videoCodeId]

export const selectors = {
    selectById,
    selectPrototypeById,
}
