import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Fachbereich } from 'StimulusControllers/Schreibtisch/types'

type FachbereichFilterState = Array<Fachbereich['id']>

const initialState: FachbereichFilterState = []

const FachbereichFilterSlice = createSlice({
    name: 'fachbereichFilter',
    initialState,
    reducers: {
        toggleFilter: (state, action: PayloadAction<Fachbereich['id']>) => {
            if (state.includes(action.payload)) {
                return state.filter((id) => id !== action.payload)
            }

            return [...state, action.payload]
        },
    },
})

export default FachbereichFilterSlice
export const { toggleFilter } = FachbereichFilterSlice.actions

export const selectActiveFachbereichFilters = (state: { filters: { fachbereich: FachbereichFilterState } }) =>
    state.filters.fachbereich
