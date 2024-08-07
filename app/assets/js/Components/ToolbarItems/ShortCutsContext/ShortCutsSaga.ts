import { put, takeLatest } from 'redux-saga/effects'
import { createAction } from '@reduxjs/toolkit'
import { merge } from 'lodash'
import { selectShortCutsState, setShortCutsState, ShortCutsState } from './ShortCutsSlice'
import { selectState } from 'StimulusControllers/ExerciseAndSolutionStore/rootSaga'

export const initializeShortCuts = createAction('SAGA/SHORT_CUTS/INITIALIZE')
export const persistShortCuts = createAction('SAGA/SHORT_CUTS/PERSIST')

export function* shortCutsSaga() {
    yield takeLatest(initializeShortCuts, initializeShortCutsSaga)
    yield takeLatest(persistShortCuts, persistShortCutsSaga)
}

const STORAGE_KEY = 'degree-short-cuts'

function* initializeShortCutsSaga() {
    const persistedShortCuts = localStorage.getItem(STORAGE_KEY)

    if (persistedShortCuts) {
        const shortCutsStateFromStore = selectShortCutsState(yield selectState())
        const shortCutsStateFromLocalStorage = JSON.parse(persistedShortCuts) as ShortCutsState

        // set new state as combination
        // TODO: If we remove a shortCut from the system this should not re-introduce it but rather be filtered out
        // That means we map only the systems shortCuts and can only overwrite those
        const combinedShortCuts = merge({}, shortCutsStateFromStore, shortCutsStateFromLocalStorage)

        yield put(setShortCutsState(combinedShortCuts))
    }
}

function* persistShortCutsSaga() {
    const shortCuts = selectShortCutsState(yield selectState())

    localStorage.setItem(STORAGE_KEY, JSON.stringify(shortCuts))
}
