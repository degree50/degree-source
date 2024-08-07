import { setPlayerTimeControlSaga } from 'Components/ToolbarItems/SetVideoPlayerTimeContext/SetPlayerTimeControlSaga'
import { openOverlayShortCutSaga } from 'Components/ToolbarItems/ShortCutsContext/shortCutSagas/openOverlayShortCutSaga'
import { setCurrentTimeAsValueShortCutSaga } from 'Components/ToolbarItems/ShortCutsContext/shortCutSagas/SetCurrentTimeAsValueShortCutSaga'
import { togglePlayShortCutSaga } from 'Components/ToolbarItems/ShortCutsContext/shortCutSagas/togglePlayShortCutSaga'
import { shortCutSoundsSaga } from 'Components/ToolbarItems/ShortCutsContext/shortCutSoundsSaga'
import { shortCutsSaga } from 'Components/ToolbarItems/ShortCutsContext/ShortCutsSaga'
import { all, call, select, spawn } from 'redux-saga/effects'
import presenceSaga from '../ExercisePhaseApp/Components/Presence/PresenceSaga'
import solutionSaga from '../ExercisePhaseApp/Components/Solution/SolutionSaga'
import { AppState } from './Store'

const sagas = [
    presenceSaga,
    solutionSaga,
    shortCutsSaga,
    setCurrentTimeAsValueShortCutSaga,
    shortCutSoundsSaga,
    togglePlayShortCutSaga,
    openOverlayShortCutSaga,
    setPlayerTimeControlSaga,
]

export default function* rootSaga() {
    yield all(
        sagas.map((saga) =>
            spawn(function* () {
                while (true) {
                    try {
                        yield call(saga)
                        break
                    } catch (e) {
                        console.error(e)
                    }
                }
            })
        )
    )
}

/**
 * WHY: yield select() returns any, so we wrap this to get type safety
 *
 * @example
 *  const state = yield* selectState() // state: AppState
 *
 * @example with redux selector
 *  const mercureEndpoint = selectMercureEndpoint(yield* selectState()) // mercureEndpoint: string
 */
export function* selectState() {
    const state: AppState = yield select()
    return state
}
