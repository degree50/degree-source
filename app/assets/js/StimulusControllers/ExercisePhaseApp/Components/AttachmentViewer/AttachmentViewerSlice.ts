import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Attachment } from './AttachmentViewer'

interface AttachmentViewerState {
    activeAttachment?: Attachment
}

const initialState: AttachmentViewerState = {
    activeAttachment: undefined,
}

export const attachmentViewerSlice = createSlice({
    name: 'attachmentViewer',
    initialState,
    reducers: {
        setActiveAttachment: (state, action: PayloadAction<Attachment | undefined>) => {
            return {
                ...state,
                activeAttachment: action.payload,
            }
        },
    },
})

export const { setActiveAttachment } = attachmentViewerSlice.actions

export type AttachmentViewerStateSlice = {
    attachmentViewer: AttachmentViewerState
}

export const selectActiveAttachment = (state: AttachmentViewerStateSlice) => state.attachmentViewer.activeAttachment

export default attachmentViewerSlice.reducer
