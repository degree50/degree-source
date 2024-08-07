import { createAction } from '@reduxjs/toolkit'
import { put, takeLatest } from 'redux-saga/effects'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { SetVideoPlayerTimeOverlayId } from './Overlays/SetVideoPlayerTimeOverlay'

export const setPlayerTimeControlAction = createAction('SAGA/VIDEOJS/CUSTOM__CONTROL_ACTION/SET_PLAYER_TIME')

export function* setPlayerTimeControlSaga() {
    yield takeLatest(setPlayerTimeControlAction, openSetPlayerTimeControl)
}

function* openSetPlayerTimeControl() {
    yield put(actions.player.setPause(true))
    yield put(
        actions.overlay.setOverlay({
            overlayId: SetVideoPlayerTimeOverlayId,
            closeOthers: false,
        })
    )
}
