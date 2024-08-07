import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { TeamMemberId } from './PresenceSlice'

export type CurrentEditorState = {
    currentEditorId?: TeamMemberId
}

const initialState: CurrentEditorState = {
    currentEditorId: undefined,
}

const CurrentEditorSlice = createSlice({
    name: 'CurrentEditor',
    initialState,
    reducers: {
        setCurrentEditorId: (state: CurrentEditorState, action: PayloadAction<TeamMemberId>): CurrentEditorState => ({
            ...state,
            currentEditorId: action.payload,
        }),
    },
})

export const { actions } = CurrentEditorSlice
export default CurrentEditorSlice.reducer

export type CurrentEditorStateSlice = { currentEditor: CurrentEditorState }

const selectCurrentEditorId = (state: AppState) => state.currentEditor.currentEditorId

export const selectors = {
    selectCurrentEditorId,
}
