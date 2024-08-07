import { createSlice, PayloadAction } from '@reduxjs/toolkit'

export type OverlayState = {
    isVisible: boolean
    currentOverlayIds: Array<string>
    currentlyEditedElementId?: string
    currentlyEditedElementParentId?: string
}

const initialState: OverlayState = {
    isVisible: false,
    currentOverlayIds: [],
}

const OverlaySlice = createSlice({
    name: 'Overlay',
    initialState,
    reducers: {
        show: (state: OverlayState): OverlayState => ({
            ...state,
            isVisible: true,
        }),
        hide: (state: OverlayState): OverlayState => ({
            ...state,
            isVisible: false,
        }),
        setOverlay: (
            state: OverlayState,
            action: PayloadAction<{ overlayId: string; closeOthers: boolean }>
        ): OverlayState => {
            const { closeOthers, overlayId } = action.payload
            const currentOverlayIds = closeOthers
                ? [overlayId]
                : [...state.currentOverlayIds.filter((id) => id !== overlayId), overlayId]

            return {
                ...state,
                currentOverlayIds,
                isVisible: true,
            }
        },
        unsetOverlay: (state: OverlayState, action: PayloadAction<string>): OverlayState => ({
            ...state,
            currentOverlayIds: state.currentOverlayIds.filter((overlayId) => overlayId !== action.payload),
        }),
        setCurrentlyEditedElementId: (
            state: OverlayState,
            action: PayloadAction<string | undefined>
        ): OverlayState => ({
            ...state,
            currentlyEditedElementId: action.payload,
        }),
        setCurrentlyEditedElementParentId: (
            state: OverlayState,
            action: PayloadAction<string | undefined>
        ): OverlayState => ({
            ...state,
            currentlyEditedElementParentId: action.payload,
        }),
    },
})

export const { actions } = OverlaySlice

const isVisibleSelector = (state: { videoEditor: { overlay: OverlayState } }) => state.videoEditor.overlay.isVisible
const overlayIdSelector = (state: { videoEditor: { overlay: OverlayState } }) =>
    state.videoEditor.overlay.currentOverlayIds
const currentlyEditedElementIdSelector = (state: { videoEditor: { overlay: OverlayState } }) =>
    state.videoEditor.overlay.currentlyEditedElementId
const currentlyEditedElementParentIdSelector = (state: { videoEditor: { overlay: OverlayState } }) =>
    state.videoEditor.overlay.currentlyEditedElementParentId

export const selectors = {
    isVisible: isVisibleSelector,
    overlayIds: overlayIdSelector,
    currentlyEditedElementId: currentlyEditedElementIdSelector,
    currentlyEditedElementParentId: currentlyEditedElementParentIdSelector,
}

export default OverlaySlice.reducer
