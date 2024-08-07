import { Controller } from 'stimulus'
import ReactDOM from 'react-dom'
import React from 'react'
import { Provider } from 'react-redux'
import { store } from './ExerciseAndSolutionStore/Store'
import { ExercisePhaseApp } from './ExercisePhaseApp/ExercisePhaseApp'
import { initSolutionSyncAction } from './ExercisePhaseApp/Components/Solution/SolutionSaga'
import { ConfigState } from './ExercisePhaseApp/Components/Config/ConfigSlice'
import { normalizeFilterData } from './normalizeData'
import { initData } from 'Components/VideoEditor/initData'
import { DataState } from 'StimulusControllers/ExerciseAndSolutionStore/DataSlice'
import { actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { PresenceState, TeamMember } from './ExercisePhaseApp/Components/Presence/PresenceSlice'
import { ExercisePhaseTypesEnum } from './ExerciseAndSolutionStore/ExercisePhaseTypesEnum'

class ExercisePhaseController extends Controller {
    connect() {
        const propsAsString = this.data.get('props')
        const props = propsAsString ? JSON.parse(propsAsString) : {}

        const { liveSyncConfig, currentEditor } = props
        const config = props.config as ConfigState
        const data = props.data as DataState

        // set initial Redux state
        store.dispatch(actions.config.hydrateConfig(config))
        store.dispatch(actions.liveSyncConfig.hydrateLiveSyncConfig(liveSyncConfig))

        store.dispatch(initData(data))

        store.dispatch(actions.videoEditor.filter.init(normalizeFilterData(config, data)))

        store.dispatch(actions.currentEditor.setCurrentEditorId(currentEditor))

        if (config.isGroupPhase && !config.readOnly) {
            store.dispatch(initSolutionSyncAction())
            store.dispatch(
                actions.presence.setTeamMembers(
                    liveSyncConfig.teamMembers.reduce(
                        (acc: PresenceState['teamMembersById'], teamMember: TeamMember) => ({
                            ...acc,
                            [teamMember.id]: teamMember,
                        }),
                        {}
                    )
                )
            )
        }

        ReactDOM.render(
            <Provider store={store}>
                <ExercisePhaseApp type={props.config.type as ExercisePhaseTypesEnum} />
            </Provider>,
            this.element
        )
    }
}

export default ExercisePhaseController
