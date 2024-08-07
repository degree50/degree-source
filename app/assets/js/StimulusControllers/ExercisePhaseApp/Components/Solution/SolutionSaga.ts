import { call, cancel, fork, put, select, take, takeLatest, throttle } from 'redux-saga/effects'
import { eventChannel, EventChannel, Task } from 'redux-saga'
import { createAction } from '@reduxjs/toolkit'
import Axios from 'axios'
import { initPresenceAction } from '../Presence/PresenceSaga'
import { initData } from 'Components/VideoEditor/initData'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

export const initSolutionSyncAction = createAction('Solution/Saga/init')
export const disconnectSolutionSyncAction = createAction('Solution/Saga/disconnect')
export const updateSolutionAction = createAction('Solution/Saga/updateSolution', (data) => ({ payload: data }))
export const syncSolutionAction = createAction('Solution/Saga/SyncSolution')
export const syncSolutionAsDozentAction = createAction('Solution/Saga/SyncSolutionAsDozent')

export default function* solutionSaga() {
    yield takeLatest(initSolutionSyncAction, solutionSyncListener)
    yield throttle(500, syncSolutionAction, syncSolution)
    yield throttle(500, syncSolutionAsDozentAction, syncSolutionAsDozent)
}

function* solutionSyncListener() {
    const lifeSyncConfig = selectors.liveSyncConfig.selectLiveSyncConfig(yield select())

    try {
        // setup SSE for presence topic
        const mercureUrl = new URL(lifeSyncConfig.mercureEndpoint, document.location.toString())
        mercureUrl.searchParams.append('topic', lifeSyncConfig.topics.solution)
        const eventSource = new EventSource(mercureUrl.toString())
        const eventChannel: EventChannel<EventSource> = yield call(connect, eventSource)

        yield put(initPresenceAction())

        const messageHandler: Task = yield fork(handleMessages, eventChannel)

        // wait for disconnect by user
        yield take(disconnectSolutionSyncAction)
        yield cancel(messageHandler)
    } catch (e) {
        console.error(e)
    }
}

function connect(eventSource: EventSource) {
    return eventChannel((emit) => {
        // eslint-disable-next-line
        eventSource.onmessage = (ev) => {
            emit(updateSolutionAction(ev.data))
        }

        return () => {
            eventSource.close()
        }
    })
}

function* handleMessages(channel: EventChannel<any>) {
    try {
        while (true) {
            // FIXME any typing
            const action: { payload: any } = yield take(channel)

            // FIXME any typings
            // set currentEditor
            const eventData: { currentEditor: any; data: any } = yield JSON.parse(action.payload)
            const currentEditor: string = eventData.currentEditor
            yield put(actions.currentEditor.setCurrentEditorId(currentEditor))

            const { data } = eventData

            yield put(initData(data))
        }
    } finally {
        channel.close()
    }
}

/**
 * Upload solution if user is currentEditor
 */
function* syncSolution() {
    const config = selectors.config.selectConfig(yield select())

    if (!config.readOnly && config.userId === selectors.currentEditor.selectCurrentEditorId(yield select())) {
        const solutionData = selectors.selectSolutionData(yield select())
        const updateSolutionEndpoint = selectors.config.selectConfig(yield select()).apiEndpoints.updateSolution

        try {
            yield Axios.post(updateSolutionEndpoint, solutionData)
        } catch (e) {
            console.warn('>>>>> updateSolution', e)
        }
    }
}

function* syncSolutionAsDozent() {
    // get id of current Solution
    const currentSolutionId = selectors.data.solutions.selectCurrentId(yield select())
    const endpoint = `/exercise-phase/review-solution/${currentSolutionId}`
    const solutionData = selectors.selectSolutionData(yield select())

    // build request
    try {
        yield Axios.post(endpoint, solutionData)
    } catch (e) {
        console.warn('>>>>> updateSolution', e)
    }
}
