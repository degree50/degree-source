import { connect } from 'react-redux'
import React from 'react'
import PrototypeList from './PrototypeList'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'

const mapStateToProps = (state: AppState) => {
    const isSolutionView = selectors.config.selectIsSolutionView(state)
    const isCurrentEditor = selectors.selectUserIsCurrentEditor(state)

    const readonly = isSolutionView || !isCurrentEditor

    const videoCodePrototypesOfCurrentSolution = selectors.data.selectCurrentSolutionVideoCodePrototypesList(state)
    const videoCodePrototypesOfPreviousSolutions = selectors.data.selectPreviousSolutionsVideoCodePrototypesList(state)
    const isVideoAnalysisPhase = selectors.config.selectPhaseType(state) === ExercisePhaseTypesEnum.VIDEO_ANALYSIS

    return {
        videoCodePrototypesOfCurrentSolution,
        videoCodePrototypesOfPreviousSolutions,
        isVideoAnalysisPhase,
        readonly,
    }
}

const mapDispatchToProps = {
    openOverlay: actions.videoEditor.overlay.setOverlay,
    setCurrentlyEditedElementId: actions.videoEditor.overlay.setCurrentlyEditedElementId,
    setCurrentlyEditedElementParentId: actions.videoEditor.overlay.setCurrentlyEditedElementParentId,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

// Renders a list of configured video codes.
// These are not yet part of the ReadOnlyMediaTrack.
// Be pressing the add button on these codes, students can add them
// to the media track, to arrange them.
const VideoCodePrototypes = (props: Props) => {
    return (
        <>
            {props.videoCodePrototypesOfPreviousSolutions.length > 0 ? (
                <>
                    <h4>Code-System vorheriger Lösungen</h4>
                    <PrototypeList videoCodePrototypes={props.videoCodePrototypesOfPreviousSolutions} readonly={true} />
                </>
            ) : null}
            {props.isVideoAnalysisPhase ? (
                <>
                    <h4>Code-System dieser Lösung</h4>
                    <PrototypeList
                        videoCodePrototypes={props.videoCodePrototypesOfCurrentSolution}
                        emptyMessage="Es stehen aktuell keine Video-Codes zur Auswahl"
                        readonly={props.readonly}
                    />
                </>
            ) : null}
        </>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(VideoCodePrototypes))
