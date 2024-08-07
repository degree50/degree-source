import React from 'react'
import { connect } from 'react-redux'
import TeamMembersListItem from './TeamMembersListItem'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

type TeamMembersListProps = ReturnType<typeof mapStateToProps>

const TeamMembersList: React.FC<TeamMembersListProps> = ({ isConnecting, teamMemberIds }) => {
    return (
        <div className={'presence__team-members'}>
            {isConnecting ? (
                'connecting ...'
            ) : teamMemberIds.length > 0 ? (
                <ul>
                    {teamMemberIds.map((id) => (
                        <TeamMembersListItem teamMemberId={id} key={id} />
                    ))}
                </ul>
            ) : (
                'fetching presence data ...'
            )}
        </div>
    )
}

const mapStateToProps = (state: AppState) => ({
    teamMemberIds: selectors.presence.selectTeamMemberIds(state),
    isConnecting: selectors.presence.selectIsConnecting(state),
})

export default connect(mapStateToProps)(TeamMembersList)
