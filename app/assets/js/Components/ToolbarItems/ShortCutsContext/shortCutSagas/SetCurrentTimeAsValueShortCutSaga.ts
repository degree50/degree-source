import { put, takeLatest } from 'redux-saga/effects'
import { createAction } from '@reduxjs/toolkit'
import { CutOverlayIds } from '../../CuttingContext/CuttingMenu'
import { AnnotationOverlayIds } from '../../AnnotationsContext/AnnotationsMenu'
import { VideoCodeOverlayIds } from '../../VideoCodesContext/VideoCodesMenu'
import { selectors as OverlaySelectors } from '../../components/OverlayContainer/OverlaySlice'
import { ShortCutId } from '../ShortCutsSlice'
import { playShortCutSuccessSoundAction, playShortCutTriggerSoundAction } from '../shortCutSoundsSaga'
import { selectState } from 'StimulusControllers/ExerciseAndSolutionStore/rootSaga'

export const setCurrentTimeAsStartValueAction = createAction('SAGA/SHORT_CUTS/SET_CURRENT_TIME_AS_START_VALUE')
export const setCurrentTimeAsEndValueAction = createAction('SAGA/SHORT_CUTS/SET_CURRENT_TIME_AS_END_VALUE')

export function* setCurrentTimeAsValueShortCutSaga() {
    yield takeLatest(setCurrentTimeAsStartValueAction, setCurrentTimeAsStartValueSaga)
    yield takeLatest(setCurrentTimeAsEndValueAction, setCurrentTimeAsEndValueSaga)
}

const supportedOverlayIds = [
    CutOverlayIds.create,
    CutOverlayIds.edit,
    AnnotationOverlayIds.create,
    AnnotationOverlayIds.edit,
    VideoCodeOverlayIds.create,
    VideoCodeOverlayIds.edit,
]

function* setCurrentTimeAsStartValueSaga() {
    // get active overlays
    const activeOverlayIds = OverlaySelectors.overlayIds(yield selectState())

    // if overlay in supported overlays
    if (activeOverlayIds.some((overlayId) => supportedOverlayIds.includes(overlayId))) {
        // trigger click on button
        document
            .querySelector<HTMLButtonElement>(
                `button[data-short-cut-id="${ShortCutId.SET_CURRENT_TIME_AS_START_VALUE}"]`
            )
            ?.click()

        yield put(playShortCutSuccessSoundAction())
    } else {
        yield put(playShortCutTriggerSoundAction())
    }
}

function* setCurrentTimeAsEndValueSaga() {
    // get active overlays
    const activeOverlayIds = OverlaySelectors.overlayIds(yield selectState())
    // if overlay in supported overlays
    if (activeOverlayIds.some((overlayId) => supportedOverlayIds.includes(overlayId))) {
        // trigger click on button
        document
            .querySelector<HTMLButtonElement>(`button[data-short-cut-id="${ShortCutId.SET_CURRENT_TIME_AS_END_VALUE}"]`)
            ?.click()

        yield put(playShortCutSuccessSoundAction())
    } else {
        yield put(playShortCutTriggerSoundAction())
    }
}
