import { createAction } from '@reduxjs/toolkit'
import { DataState } from 'StimulusControllers/ExerciseAndSolutionStore/DataSlice'

// Why isn't this located inside ./DataSlice?
// => If we locate this inside the slice, we run into circular dependency issues
// which will lead to runtime crashes. (however the compiler won't complain, which is why
// this issue is rather hard to spot)
// See https://github.com/reduxjs/redux-toolkit/issues/911
export const initData = createAction<DataState>('data/init')
