import { createSlice, PayloadAction } from '@reduxjs/toolkit'

export interface PlayerState {
    // The position which the player emits to other components
    // like the mediaLane.
    // This should only be set by the player component!
    syncPlayPosition: number

    // The actual play position of the player.
    // When this is set the player changes its position.
    playPosition: number
    isPaused: boolean
}

const initialState: PlayerState = {
    syncPlayPosition: 0,
    playPosition: 0,
    isPaused: true,
}

export const playerSlice = createSlice({
    name: 'player',
    initialState,
    reducers: {
        setSyncPlayPosition: (state, action: PayloadAction<number>) => {
            return {
                ...state,
                syncPlayPosition: action.payload,
            }
        },
        setPlayPosition: (state, action: PayloadAction<number>) => {
            return {
                ...state,
                playPosition: action.payload,
            }
        },
        setPause: (state, action: PayloadAction<boolean>) => {
            return {
                ...state,
                isPaused: action.payload,
            }
        },
    },
})

export const { actions } = playerSlice

// Selectors
export type PlayerStateSlice = { videoEditor: { player: PlayerState } }

const selectPlayPosition = (state: PlayerStateSlice) => state.videoEditor.player.playPosition
const selectSyncPlayPosition = (state: PlayerStateSlice) => state.videoEditor.player.syncPlayPosition
const selectIsPaused = (state: PlayerStateSlice) => state.videoEditor.player.isPaused

export const selectors = {
    selectSyncPlayPosition,
    selectIsPaused,
    selectPlayPosition,
}

export default playerSlice.reducer
