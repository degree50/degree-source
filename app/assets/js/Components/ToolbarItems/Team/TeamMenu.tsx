import Button from 'Components/Button/Button'
import React, { FC, memo } from 'react'
import { connect } from 'react-redux'
import { TEAM_OVERLAY_ID } from './TeamOverlay'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => ({
    isSolutionView: selectors.config.selectIsSolutionView(state),
    isGroupPhase: selectors.config.selectIsGroupPhase(state),
    onlineTeamMemberIds: selectors.presence.selectOnlineTeamMemberIds(state),
})

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const TeamMenu: FC<Props> = (props) => {
    const handleClick = () => {
        props.setOverlay({ overlayId: TEAM_OVERLAY_ID, closeOthers: true })
    }

    const isDisabled = !props.isGroupPhase || props.isSolutionView

    return (
        <div className="video-editor-menu">
            {!isDisabled && <div className="video-editor-menu__count-badge">{props.onlineTeamMemberIds.length}</div>}
            <Button
                title="Team"
                isDisabled={isDisabled}
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handleClick}
            >
                <i className="fas fa-users" />
            </Button>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(TeamMenu))
