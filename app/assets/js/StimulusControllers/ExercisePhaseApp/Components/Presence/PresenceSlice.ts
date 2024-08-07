import { createSlice, PayloadAction, createSelector } from '@reduxjs/toolkit'
import { AppState } from '../../../ExerciseAndSolutionStore/Store'

export enum ConnectionState {
    CONNECTED = 'CONNECTED',
    DISCONNECTED = 'DISCONNECTED',
    UNKNOWN = 'UNKNOWN',
}

export type TeamMemberId = string
export type TeamMember = {
    id: TeamMemberId
    name: string
    connectionState: ConnectionState
}

export type PresenceState = {
    teamMemberIds: Array<TeamMemberId>
    teamMembersById: Record<TeamMemberId, TeamMember>
    error?: string
    isConnecting: boolean
}

const initialState: PresenceState = {
    teamMemberIds: [],
    teamMembersById: {},
    error: undefined,
    isConnecting: false,
}

const PresenceSlice = createSlice({
    name: 'Presence',
    initialState,
    reducers: {
        setTeamMembers: (
            state: PresenceState,
            action: PayloadAction<PresenceState['teamMembersById']>
        ): PresenceState => ({
            ...state,
            teamMemberIds: Object.keys(action.payload),
            teamMembersById: action.payload,
        }),
        setIsConnecting: (state: PresenceState, action: PayloadAction<boolean>): PresenceState => ({
            ...state,
            isConnecting: action.payload,
        }),
        setError: (state: PresenceState, action: PayloadAction<PresenceState['error']>): PresenceState => ({
            ...state,
            error: action.payload,
        }),
    },
})

export const { actions } = PresenceSlice
export default PresenceSlice.reducer

// Selectors

const selectTeamMemberIds = (state: AppState) => state.presence.teamMemberIds
const selectTeamMemberById = (id: TeamMemberId, state: AppState) => state.presence.teamMembersById[id]
const selectTeamMembersById = (state: AppState) => state.presence.teamMembersById
const selectIsConnecting = (state: AppState) => state.presence.isConnecting

const selectOnlineTeamMemberIds = createSelector([selectTeamMemberIds, selectTeamMembersById], (allIds, byId) =>
    allIds.filter((memberId) => byId[memberId].connectionState === ConnectionState.CONNECTED)
)

export const selectors = {
    selectTeamMemberIds,
    selectTeamMemberById,
    selectTeamMembersById,
    selectIsConnecting,
    selectOnlineTeamMemberIds,
}
