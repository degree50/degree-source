import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { initData } from 'Components/VideoEditor/initData'
import { AppState } from './Store'

export type MaterialId = string

export type Material = {
    id: string
    material: string
    solutionId: string
}

export type MaterialsState = {
    allIds: Array<MaterialId>
    byId: Record<MaterialId, Material>
}

export const initialState: MaterialsState = {
    allIds: [],
    byId: {},
}

const MaterialsSlice = createSlice({
    name: 'materials',
    initialState,
    reducers: {
        updateMaterial: (state, action: PayloadAction<{ material: Material }>) => {
            const { material } = action.payload

            return {
                ...state,
                byId: {
                    ...state.byId,
                    [material.id]: material,
                },
            }
        },
    },
    extraReducers: (builder) => {
        builder.addCase(initData, (_, action) => {
            return action.payload.materials
        })
    },
})

const byId = (state: AppState) => state.data.materials.byId

export const selectors = {
    byId,
}

export default MaterialsSlice
