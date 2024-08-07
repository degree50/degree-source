import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { AppState } from '../../../ExerciseAndSolutionStore/Store'
import { overlaySizesEnum } from './Overlay'
import { ComponentTypesEnum } from 'types'

interface OverlayState {
    isVisible: boolean
    component?: ComponentTypesEnum
    size: string
}

const initialState: OverlayState = {
    isVisible: false,
    component: undefined,
    size: overlaySizesEnum.DEFAULT,
}

export const overlaySlice = createSlice({
    name: 'overlay',
    initialState,
    reducers: {
        setOverlayVisibility: (state, action: PayloadAction<boolean>) => {
            return {
                ...state,
                isVisible: action.payload,
            }
        },
        setOverlayComponent: (state, action: PayloadAction<ComponentTypesEnum>) => {
            return {
                ...state,
                component: action.payload,
            }
        },
        setOverlaySize: (state, action: PayloadAction<string>) => {
            return {
                ...state,
                size: action.payload,
            }
        },
    },
})

export const { setOverlayVisibility, setOverlayComponent, setOverlaySize } = overlaySlice.actions

export const selectIsVisible = (state: AppState) => state.overlay.isVisible
export const selectComponent = (state: AppState) => state.overlay.component
export const selectSize = (state: AppState) => state.overlay.size

export default overlaySlice.reducer
