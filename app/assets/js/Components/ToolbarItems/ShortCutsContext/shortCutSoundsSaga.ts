import { createAction } from '@reduxjs/toolkit'
import { put, takeLatest } from 'redux-saga/effects'
import { Howl } from 'howler'
import {
    selectIsSoundEnabled,
    selectShortCutSoundState,
    setShortCutSoundsState,
    ShortCutSoundState,
} from './ShortCutSoundsSlice'
import { merge } from 'lodash'
import { selectState } from 'StimulusControllers/ExerciseAndSolutionStore/rootSaga'

export const playShortCutSuccessSoundAction = createAction('SAGA/SHORT_CUT/SHORT_CUT_SUCCESS')
export const playShortCutTriggerSoundAction = createAction('SAGA/SHORT_CUT/SHORT_CUT_TRIGGER')
export const initializeSoundOptionsAction = createAction('SAGA/SHORT_CUT/INITIALIZE_SOUND_OPTIONS')
export const persistSoundOptionsAction = createAction('SAGA/SHORT_CUT/PERSIST_SOUND_OPTIONS')

export function* shortCutSoundsSaga() {
    yield takeLatest(playShortCutSuccessSoundAction, playShortCutSuccess)
    yield takeLatest(playShortCutTriggerSoundAction, playShortCutTrigger)
    yield takeLatest(initializeSoundOptionsAction, initializeSoundOptions)
    yield takeLatest(persistSoundOptionsAction, persistSoundOptions)
}

const successSound = new Howl({
    src: '/sounds/shortCutSuccess.mp3',
})

export function* playShortCutSuccess() {
    const isSoundEnabled = selectIsSoundEnabled(yield selectState())

    if (isSoundEnabled) {
        successSound.play()
    }
}

const triggerSound = new Howl({
    src: '/sounds/shortCutTrigger.mp3',
})

export function* playShortCutTrigger() {
    const isSoundEnabled = selectIsSoundEnabled(yield selectState())

    if (isSoundEnabled) {
        triggerSound.play()
    }
}

const STORAGE_KEY = 'degree-short-cut-sound-options'
function* initializeSoundOptions() {
    const persistedShortCutSoundOptions = localStorage.getItem(STORAGE_KEY)

    if (persistedShortCutSoundOptions) {
        const shortCutSoundOptionsFromStore = selectShortCutSoundState(yield selectState())
        const shortCutSoundOptionsFromLocalStorage = JSON.parse(persistedShortCutSoundOptions) as ShortCutSoundState

        const combinedState = merge({}, shortCutSoundOptionsFromStore, shortCutSoundOptionsFromLocalStorage)

        yield put(setShortCutSoundsState(combinedState))
    }
}

function* persistSoundOptions() {
    const shortCutSoundOptionsFromStore = selectShortCutSoundState(yield selectState())

    localStorage.setItem(STORAGE_KEY, JSON.stringify(shortCutSoundOptionsFromStore))
}
