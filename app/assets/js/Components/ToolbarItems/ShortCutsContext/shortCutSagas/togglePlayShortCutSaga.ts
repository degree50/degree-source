import { createAction } from '@reduxjs/toolkit'
import { actions, selectors } from 'Components/VideoEditor/VideoEditorSlice'
import { put, takeLatest } from 'redux-saga/effects'
import { playShortCutSuccessSoundAction, playShortCutTriggerSoundAction } from '../shortCutSoundsSaga'
import { selectState } from 'StimulusControllers/ExerciseAndSolutionStore/rootSaga'

export const togglePlayShortCutAction = createAction('SAGA/SHORT_CUT/TOGGLE_PLAY')

export function* togglePlayShortCutSaga() {
    yield takeLatest(togglePlayShortCutAction, togglePlayShortCut)
}

function* togglePlayShortCut() {
    const isPaused = selectors.player.selectIsPaused(yield selectState())

    yield put(actions.player.setPause(!isPaused))

    const newIsPaused = selectors.player.selectIsPaused(yield selectState())

    if (isPaused !== newIsPaused) {
        yield put(playShortCutSuccessSoundAction())
    } else {
        yield put(playShortCutTriggerSoundAction())
    }
}
