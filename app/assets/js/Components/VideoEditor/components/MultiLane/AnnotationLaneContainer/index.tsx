import React, { useMemo } from 'react'
import { connect } from 'react-redux'
import { TabsTypesEnum } from 'types'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import AnnotationLane from './AnnotationLane'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import MediaLaneDescription from '../MediaLaneDescription'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import getComponentName from 'Components/VideoEditor/components/MultiLane/getComponentName'

const mapStateToProps = (state: AppState) => {
    const currentSolutionId = selectors.data.solutions.selectCurrentId(state)
    const isReadonly = !selectors.selectUserCanEditSolution(state, {
        solutionId: currentSolutionId,
    })

    return {
        currentSolutionOwner: selectors.data.solutions.selectCurrentSolutionOwner(state),
        currentIsFromGroupPhase: selectors.data.solutions.selectCurrentSolutionFromGroupPhase(state),
        annotations: selectors.data.selectCurrentAnnotationsByStartTime(state),
        previousSolutions: selectors.selectActiveSolutionsWithAnnotations(state),
        exercisePhaseType: selectors.config.selectPhaseType(state),
        isSolutionView: selectors.config.selectIsSolutionView(state),
        isReadonly,
    }
}

const mapDispatchToProps = {}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const componentName = getComponentName(TabsTypesEnum.VIDEO_ANNOTATIONS)

const AnnotationLaneContainer = (props: Props) => {
    const renderExercisePhaseEditorMediaLane = useMemo(() => {
        if (!props.isSolutionView && props.exercisePhaseType === ExercisePhaseTypesEnum.VIDEO_ANALYSIS) {
            return (
                <div>
                    <MediaLaneDescription
                        componentName={componentName}
                        itemCount={props.annotations.length}
                        userName={props.currentSolutionOwner.userName}
                        fromGroupPhase={props.currentIsFromGroupPhase}
                    />
                    <AnnotationLane annotations={props.annotations} readOnly={props.isReadonly} />
                </div>
            )
        }
        return null
    }, [
        props.annotations,
        props.currentIsFromGroupPhase,
        props.currentSolutionOwner.userName,
        props.exercisePhaseType,
        props.isReadonly,
        props.isSolutionView,
    ])

    return (
        <>
            {renderExercisePhaseEditorMediaLane}
            {props.previousSolutions.map((solution) => (
                <div key={solution.id}>
                    <MediaLaneDescription
                        componentName={componentName}
                        itemCount={solution.annotations.length}
                        userName={solution.userName}
                        fromGroupPhase={solution.fromGroupPhase}
                        isPreviousSolution={true}
                    />
                    <AnnotationLane annotations={solution.annotations} readOnly />
                </div>
            ))}
        </>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(AnnotationLaneContainer))
