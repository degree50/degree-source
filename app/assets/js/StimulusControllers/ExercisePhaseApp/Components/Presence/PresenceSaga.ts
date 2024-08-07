import { call, cancel, debounce, fork, put, select, take, takeLatest } from 'redux-saga/effects'
import { eventChannel, EventChannel, Task } from 'redux-saga'
import { createAction } from '@reduxjs/toolkit'
import Axios from 'axios'
import { ConnectionState, TeamMember } from './PresenceSlice'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

export const initPresenceAction = createAction('Presence/Saga/init')
export const disconnectPresenceAction = createAction('Presence/Saga/disconnect')
const eventStreamOpenedAction = createAction('Presence/Saga/eventStreamOpened')
const eventStreamMessageAction = createAction('Presence/Saga/eventStreamMessage')
export const promoteUserToCurrentEditorAction = createAction(
    'Presence/Saga/promoteUserToCurrentEditor',
    (userId: string) => ({
        payload: { userId },
    })
)

/**
 * TODO:
 * This is just a first draft value - check if we can lower the value and can still prevent false positive
 * presence state updated
 */
const PRESENCE_DEBOUNCE_MS = 5000

export default function* presenceSaga() {
    yield takeLatest(initPresenceAction, presenceListener)
    yield takeLatest(eventStreamOpenedAction, fetchSubscriptions)
    yield debounce(PRESENCE_DEBOUNCE_MS, eventStreamMessageAction, fetchSubscriptions)
    yield takeLatest(promoteUserToCurrentEditorAction, promoteUserToCurrentEditor)
}

// presenceListenerLifeCycle
function* presenceListener() {
    const lifeSyncConfig = selectors.liveSyncConfig.selectLiveSyncConfig(yield select())
    yield put(actions.presence.setIsConnecting(true))

    try {
        // setup SSE for presence topic
        const mercureUrl = new URL(lifeSyncConfig.mercureEndpoint, document.location.toString())
        mercureUrl.searchParams.append('topic', lifeSyncConfig.topics.presence)
        const eventSource = new EventSource(mercureUrl.toString())
        const eventChannel: EventChannel<EventSource> = yield call(connect, eventSource)
        yield put(actions.presence.setIsConnecting(false))

        const messageHandler: Task = yield fork(handleMessages, eventChannel)
        // TODO maybe remove as we do listen for open now
        yield* fetchSubscriptions()

        // wait for disconnect by user
        yield take(disconnectPresenceAction)
        yield cancel(messageHandler)
    } catch (e) {
        yield put(actions.presence.setError((e as any).message ?? e))
        yield put(actions.presence.setIsConnecting(false))
    }
}

function connect(eventSource: EventSource) {
    return eventChannel((emit) => {
        // eslint-disable-next-line
        eventSource.onmessage = () => {
            emit(eventStreamMessageAction()) // the payload is irrelevant because we call the subscription api anyway
        }

        // eslint-disable-next-line
        eventSource.onopen = () => {
            emit(eventStreamOpenedAction()) // the payload is irrelevant because we call the subscription api anyway
        }

        return () => {
            eventSource.close()
        }
    })
}

// Type of subscription in mercure subscription payload
type Subscription = {
    id: string // "/.well-known/mercure/subscriptions/exercisePhaseTeam-3b3b41a9-68e5-41d7-a080-58f537d429d6/urn%3Auuid%3A8d5cab9a-97e1-40f8-8406-c234b4549688",
    type: string // "Subscription",
    subscriber: string // "urn:uuid:8d5cab9a-97e1-40f8-8406-c234b4549688",
    topic: string // "exercisePhaseTeam-3b3b41a9-68e5-41d7-a080-58f537d429d6",
    active: boolean
    payload: {
        user: {
            id: string // "8607ec0e-debe-44de-82ed-ae6095f55835",
            name: string // "admin@sandstorm.de"
        }
    }
}

// Transform Subscription[] to TeamMember record & connection states
const transformSubscriptionsToTeamMembers = (subscriptions: Array<Subscription>): Record<string, TeamMember> =>
    subscriptions.reduce(
        (acc, subscription) => ({
            ...acc,
            [subscription.payload.user.id]: {
                id: subscription.payload.user.id,
                name: subscription.payload.user.name,
                // TODO: we ignore subscription.active for now because the subscription will vanish if the user really quit
                connectionState: ConnectionState.CONNECTED,
            },
        }),
        {}
    )

/**
 * Push actions emitted from custom channel to root saga
 */
function* handleMessages(channel: EventChannel<any>) {
    try {
        while (true) {
            // FIXME
            // refine typing
            const action: { type: string } = yield take(channel)

            switch (action.type) {
                case eventStreamOpenedAction.type:
                    yield put(eventStreamOpenedAction())
                    break
                case eventStreamMessageAction.type:
                    yield put(eventStreamMessageAction())
                    break
            }
        }
    } finally {
        channel.close()
    }
}

const isSubscriptionResponse = (response: any): response is { data: { subscriptions: Array<Subscription> } } => {
    return Array.isArray(response?.data?.subscriptions)
}

function* fetchSubscriptions() {
    const liveSyncConfig = selectors.liveSyncConfig.selectLiveSyncConfig(yield select())
    const response: unknown = yield Axios.get(liveSyncConfig.subscriptionsEndpoint, {
        headers: {
            'Content-Type': 'application/ld+json',
        },
    })

    const subscriptions: Array<Subscription> = isSubscriptionResponse(response) ? response.data.subscriptions : []

    // merge existing teamMembers with Members in Subscription
    // possibly adding new members
    // setting the connection state of members that are not subscribed to DISCONNECTED
    const teamMembersFromState = selectors.presence.selectTeamMembersById(yield select())

    // set all members as DISCONNECTED - only members with active subscription will be set as CONNECTED again
    const teamMembersFromStateDisconnected: Record<string, TeamMember> = selectors.presence
        .selectTeamMemberIds(yield select())
        .reduce(
            (acc, teamMemberId) => ({
                ...acc,
                [teamMemberId]: {
                    ...teamMembersFromState[teamMemberId],
                    connectionState: ConnectionState.DISCONNECTED,
                },
            }),
            {}
        )
    const teamMembersInSubscription = transformSubscriptionsToTeamMembers(subscriptions)
    const updatedTeamMembers = Object.keys(teamMembersInSubscription).reduce(
        (acc, teamMemberId) => ({
            ...acc,
            [teamMemberId]: {
                ...teamMembersInSubscription[teamMemberId],
            },
        }),
        teamMembersFromStateDisconnected
    )

    // WHY: it's possible that while a team phase session is open, that other members join the team - so we need to update those
    yield put(actions.presence.setTeamMembers(updatedTeamMembers))
}

function* promoteUserToCurrentEditor(action: ReturnType<typeof promoteUserToCurrentEditorAction>) {
    const userId = selectors.config.selectUserId(yield select())
    const currentEditorId = selectors.currentEditor.selectCurrentEditorId(yield select())

    const { userId: userIdToPromote } = action.payload

    if (userId !== currentEditorId) {
        try {
            Axios.post(selectors.config.selectConfig(yield select()).apiEndpoints.updateCurrentEditor, {
                currentEditorCandidateId: userIdToPromote,
            })
        } catch (e) {
            console.warn('>>>>> updateCurrentEditor', e)
        }
    }
}
