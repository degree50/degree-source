import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Course } from 'StimulusControllers/Schreibtisch/types'

type CourseFilterState = Array<Course['id']>

const initialState: CourseFilterState = []

const CourseFilterSlice = createSlice({
    name: 'courseFilter',
    initialState,
    reducers: {
        toggleFilter: (state, action: PayloadAction<Course['id']>) => {
            if (state.includes(action.payload)) {
                return state.filter((id) => id !== action.payload)
            }

            return [...state, action.payload]
        },
    },
})

export default CourseFilterSlice
export const { toggleFilter } = CourseFilterSlice.actions

export const selectActiveCourseFilters = (state: { filters: { course: CourseFilterState } }) => state.filters.course
