import { createAction } from '@reduxjs/toolkit'
import { put, takeLatest } from 'redux-saga/effects'
import { actions as OverlayActions } from '../../components/OverlayContainer/OverlaySlice'
import { playShortCutSuccessSoundAction, playShortCutTriggerSoundAction } from '../shortCutSoundsSaga'
import { actions as PlayerActions } from 'Components/VideoEditor/PlayerSlice'
import { selectComponents } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import { AnnotationOverlayIds } from '../../AnnotationsContext/AnnotationsMenu'
import { TabsTypesEnum } from 'types'
import { VideoCodeOverlayIds } from '../../VideoCodesContext/VideoCodesMenu'
import { CutOverlayIds } from '../../CuttingContext/CuttingMenu'
import { selectState } from 'StimulusControllers/ExerciseAndSolutionStore/rootSaga'

export const openOverlayAction = createAction('SAGA/SHORT_CUT/OPEN_OVERLAY', (overlayId: string) => ({
    payload: { overlayId },
}))

export function* openOverlayShortCutSaga() {
    yield takeLatest(openOverlayAction, openOverlay)
}

function* openOverlay(action: ReturnType<typeof openOverlayAction>) {
    const overlayId = action.payload.overlayId
    const components = selectComponents(yield selectState())

    // Why: We shall not open an overlay we should not be able to open because the component is not active
    // Example: We try to open "create Annotation" overlay but only the video codes component is active for this phase.
    // issue #342
    if (
        (overlayId === AnnotationOverlayIds.create && !components.includes(TabsTypesEnum.VIDEO_ANNOTATIONS)) ||
        (overlayId === VideoCodeOverlayIds.create && !components.includes(TabsTypesEnum.VIDEO_CODES)) ||
        (overlayId === CutOverlayIds.create && !components.includes(TabsTypesEnum.VIDEO_CUTTING))
    ) {
        yield put(playShortCutTriggerSoundAction())
    } else {
        yield put(PlayerActions.setPause(true))
        yield put(OverlayActions.setOverlay({ overlayId, closeOthers: false }))

        yield put(playShortCutSuccessSoundAction())
    }
}
