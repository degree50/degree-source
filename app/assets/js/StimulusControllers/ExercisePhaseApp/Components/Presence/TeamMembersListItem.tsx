import React from 'react'
import { connect } from 'react-redux'
import { ConnectionState, TeamMemberId } from './PresenceSlice'
import { promoteUserToCurrentEditorAction } from './PresenceSaga'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

type OwnProps = {
    teamMemberId: TeamMemberId
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const renderConnectionState = (connectionState: ConnectionState) => {
    switch (connectionState) {
        case ConnectionState.CONNECTED:
            return <i className="presence__connection-state fas fa-circle " />
        case ConnectionState.DISCONNECTED:
        default:
            return <i className="presence__connection-state far fa-circle" />
    }
}

const renderUserRoleState = (currentEditorId: TeamMemberId | undefined, teamMemberId: TeamMemberId) => {
    return currentEditorId === teamMemberId ? (
        <i className="fas fa-crown presence__team-member-role" />
    ) : (
        <i className="fas fa-user presence__team-member-role" />
    )
}

const TeamMembersListItem: React.FC<Props> = (props) => {
    const className = `presence__team-member presence__team-member${
        props.teamMember.connectionState === ConnectionState.CONNECTED ? '--connected' : '--disconnected'
    }`

    const isCurrentEditor = props.teamMemberId === props.currentEditor
    const memberName = props.teamMember.name.split('@')[0]

    const handleClick = () => {
        props.promoteTeamMemberToCurrentEditor(props.teamMemberId)
    }

    const label = `
        ${isCurrentEditor ? 'Bearbeitender' : 'Teilnehmer'}
        ${memberName}
        ${props.teamMember.connectionState === ConnectionState.CONNECTED ? 'online' : 'offline'}
    `

    const buttonLabel = `Bearbeiter*innenkrone übernehmen`

    return (
        <li className={className}>
            <span tabIndex={0} aria-label={label}>
                {renderUserRoleState(props.currentEditor, props.teamMemberId)} {memberName}
            </span>
            {props.teamMemberId === props.userId && !isCurrentEditor && (
                <button
                    className={'button button--type-outline-primary button--size-small'}
                    onClick={handleClick}
                    title={buttonLabel}
                    aria-label={buttonLabel}
                >
                    <i className="fas fa-crown" />
                </button>
            )}
            {renderConnectionState(props.teamMember.connectionState)}
        </li>
    )
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => ({
    teamMember: selectors.presence.selectTeamMemberById(ownProps.teamMemberId, state),
    currentEditor: selectors.currentEditor.selectCurrentEditorId(state),
    userId: selectors.config.selectUserId(state),
})

const mapDispatchToProps = {
    promoteTeamMemberToCurrentEditor: promoteUserToCurrentEditorAction,
}

export default connect(mapStateToProps, mapDispatchToProps)(TeamMembersListItem)
