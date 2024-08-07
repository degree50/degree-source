///////////
// STATE //
///////////

import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { initData } from 'Components/VideoEditor/initData'
import { VideoCode } from 'Components/VideoEditor/types'
import { remove, set } from 'immutable'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

export type VideoCodeId = string

export type VideoCodesState = {
    byId: Record<VideoCodeId, VideoCode>
}

export const initialState: VideoCodesState = {
    byId: {},
}

/////////////
// REDUCER //
/////////////

export const videoCodesSlice = createSlice({
    name: 'videoCodes',
    initialState,
    reducers: {
        set: (state: VideoCodesState, action: PayloadAction<VideoCode[]>) => {
            const normalized = action.payload.reduce((acc, videoCode) => {
                return {
                    ...acc,
                    [videoCode.id]: videoCode,
                }
            }, {})

            return {
                ...state,
                byId: {
                    ...state.byId,
                    ...normalized,
                },
            }
        },
        append: (state: VideoCodesState, action: PayloadAction<VideoCode>): VideoCodesState => {
            const newVideoCode = action.payload
            return {
                ...state,
                byId: {
                    ...state.byId,
                    [newVideoCode.id]: newVideoCode,
                },
            }
        },
        update: (state: VideoCodesState, action: PayloadAction<{ transientVideoCode: VideoCode }>): VideoCodesState => {
            const { transientVideoCode } = action.payload

            return {
                ...state,
                byId: set(state.byId, transientVideoCode.id, transientVideoCode),
            }
        },
        remove: (state: VideoCodesState, action: PayloadAction<string>): VideoCodesState => {
            const elementId = action.payload

            return {
                ...state,
                byId: remove(state.byId, elementId),
            }
        },
    },
    extraReducers: (builder) => {
        builder.addCase(initData, (_, action) => {
            return action.payload.videoCodes
        })
    },
})

///////////////
// SELECTORS //
///////////////

const selectById = (state: AppState) => state.data.videoCodes.byId
const selectVideoCodeById = (state: AppState, props: { videoCodeId: VideoCodeId }) =>
    state.data.videoCodes.byId[props.videoCodeId]

export const selectors = {
    selectById,
    selectVideoCodeById,
}
