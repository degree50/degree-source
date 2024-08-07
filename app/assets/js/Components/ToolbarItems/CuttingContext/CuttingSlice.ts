///////////
// STATE //
///////////

import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { initData } from 'Components/VideoEditor/initData'
import { Cut } from 'Components/VideoEditor/types'
import { remove, set } from 'immutable'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

export type CutId = string

export type CutsState = {
    byId: Record<CutId, Cut>
}

export const initialState: CutsState = {
    byId: {},
}

/////////////
// REDUCER //
/////////////

export const cuttingSlice = createSlice({
    name: 'cuts',
    initialState,
    reducers: {
        set: (state, action: PayloadAction<Cut[]>) => {
            const normalized = action.payload.reduce((acc, cut) => {
                return {
                    ...acc,
                    [cut.id]: cut,
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
        append: (state: CutsState, action: PayloadAction<Cut>): CutsState => {
            const newCut = action.payload
            return {
                ...state,
                byId: {
                    ...state.byId,
                    [newCut.id]: newCut,
                },
            }
        },
        update: (state: CutsState, action: PayloadAction<{ transientCut: Cut }>): CutsState => {
            const { transientCut } = action.payload

            return {
                ...state,
                byId: set(state.byId, transientCut.id, transientCut),
            }
        },
        remove: (state: CutsState, action: PayloadAction<string>): CutsState => {
            const elementId = action.payload

            return {
                ...state,
                byId: remove(state.byId, elementId),
            }
        },
    },
    extraReducers: (builder) => {
        builder.addCase(initData, (_, action) => {
            return action.payload.cuts
        })
    },
})

///////////////
// SELECTORS //
///////////////

const selectById = (state: AppState) => state.data.cuts.byId
const selectCutById = (state: AppState, props: { cutId: CutId }) => state.data.cuts.byId[props.cutId]

export const selectors = {
    selectById,
    selectCutById,
}
