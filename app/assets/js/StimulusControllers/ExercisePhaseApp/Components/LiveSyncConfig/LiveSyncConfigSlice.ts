import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { AppState } from '../../../ExerciseAndSolutionStore/Store'

type LiveSyncConfigState = {
    topics: {
        solution: string
        presence: string
    }
    mercureEndpoint: string
    subscriptionsEndpoint: string
}

const initialState: LiveSyncConfigState = {
    topics: {
        solution: '',
        presence: '',
    },
    mercureEndpoint: '',
    subscriptionsEndpoint: '',
}

const LiveSyncConfigSlice = createSlice({
    name: 'LiveSyncConfig',
    initialState,
    reducers: {
        hydrateLiveSyncConfig: (
            state: LiveSyncConfigState,
            action: PayloadAction<LiveSyncConfigState>
        ): LiveSyncConfigState => ({
            ...state,
            ...action.payload,
        }),
    },
})

export const { actions } = LiveSyncConfigSlice
export default LiveSyncConfigSlice.reducer

const selectLiveSyncConfig = (state: AppState) => state.liveSyncConfig
export const selectors = {
    selectLiveSyncConfig,
}
