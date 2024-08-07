import React from 'react'
import { connect } from 'react-redux'
import { TabsTypesEnum } from 'types'
import VideoCutMediaLane from 'Components/VideoEditor/components/MultiLane/CutLaneContainer/VideoCutMedialane'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import MediaLaneDescription from '../MediaLaneDescription'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import getComponentName from 'Components/VideoEditor/components/MultiLane/getComponentName'

const mapStateToProps = (state: AppState) => {
    const currentSolutionId = selectors.data.solutions.selectCurrentId(state)
    const isReadonly = !selectors.selectUserCanEditSolution(state, {
        solutionId: currentSolutionId,
    })

    return {
        cuts: selectors.data.selectCurrentCutListByStartTime(state),
        currentSolutionOwner: selectors.data.solutions.selectCurrentSolutionOwner(state),
        currentIsFromGroupPhase: selectors.data.solutions.selectCurrentSolutionFromGroupPhase(state),
        isReadonly,
    }
}

const mapDispatchToProps = {}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CutLaneContainer = (props: Props) => (
    <div>
        <MediaLaneDescription
            componentName={getComponentName(TabsTypesEnum.VIDEO_CUTTING)}
            itemCount={props.cuts.length}
            userName={props.currentSolutionOwner.userName}
            fromGroupPhase={props.currentIsFromGroupPhase}
        />
        <VideoCutMediaLane cuts={props.cuts} readOnly={props.isReadonly} />
    </div>
)

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(CutLaneContainer))
