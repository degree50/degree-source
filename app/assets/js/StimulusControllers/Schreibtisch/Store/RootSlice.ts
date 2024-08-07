import { combineReducers } from '@reduxjs/toolkit'
import { SchreibtischApi } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'
import FachbereichFilterSlice from 'StimulusControllers/Schreibtisch/Store/FachbereichFilterSlice'
import CourseFilterSlice from 'StimulusControllers/Schreibtisch/Store/CourseFilterSlice'

export const RootSlice = combineReducers({
    [SchreibtischApi.reducerPath]: SchreibtischApi.reducer,
    filters: combineReducers({
        fachbereich: FachbereichFilterSlice.reducer,
        course: CourseFilterSlice.reducer,
    }),
})
