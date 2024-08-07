import React from 'react'
import { connect } from 'react-redux'
import TeamMembersList from './TeamMembersList'
import { AppState } from '../../../ExerciseAndSolutionStore/Store'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

type PresenceProps = ReturnType<typeof mapStateToProps>

const Presence: React.FC<PresenceProps> = ({ onlineTeamMemberIds }) => {
    return (
        <div className={'presence'}>
            <header className={'presence__header'}>Online: {onlineTeamMemberIds.length}</header>
            <TeamMembersList />
        </div>
    )
}

const mapStateToProps = (state: AppState) => ({
    onlineTeamMemberIds: selectors.presence.selectOnlineTeamMemberIds(state),
})

export default connect(mapStateToProps)(Presence)
