import { FC, memo } from 'react'
import { connect } from 'react-redux'
import TeamMembersList from 'StimulusControllers/ExercisePhaseApp/Components/Presence/TeamMembersList'
import Overlay from '../components/Overlay'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

export const TEAM_OVERLAY_ID = 'overlay/team'

const mapStateToProps = (state: AppState) => ({
    teamMemberIds: selectors.presence.selectTeamMemberIds(state),
    onlineTeamMemberIds: selectors.presence.selectOnlineTeamMemberIds(state),
})

const mapDispatchToProps = {
    unsetOverlay: actions.videoEditor.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const TeamOverlay: FC<Props> = (props) => {
    const title = `Team - ${props.onlineTeamMemberIds.length} von ${props.teamMemberIds.length} online`
    const handleClose = () => {
        props.unsetOverlay(TEAM_OVERLAY_ID)
    }

    return (
        <Overlay title={title} closeCallback={handleClose}>
            <TeamMembersList />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(TeamOverlay))
