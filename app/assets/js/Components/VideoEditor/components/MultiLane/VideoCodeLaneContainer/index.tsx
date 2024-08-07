import React, { useMemo } from 'react'
import { connect } from 'react-redux'
import { TabsTypesEnum } from 'types'
import VideoCodesMediaLane from 'Components/VideoEditor/components/MultiLane/VideoCodeLaneContainer/VideoCodesMedialane'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { MediaItem, VideoCode, VideoCodePrototype } from 'Components/VideoEditor/types'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import MediaLaneDescription from '../MediaLaneDescription'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import getComponentName from 'Components/VideoEditor/components/MultiLane/getComponentName'

const mergeCodesAndPrototypesToItems = (videoCodes: VideoCode[], prototypes: Record<string, VideoCodePrototype>) => {
    return videoCodes.map((videoCode) => {
        const prototype = prototypes[videoCode.idFromPrototype]

        return new MediaItem({
            start: videoCode.start,
            end: videoCode.end,
            text: prototype ? prototype.name : '',
            memo: videoCode.memo,
            originalData: videoCode,
            lane: 0,
            color: prototype ? prototype.color : null,
        })
    })
}

const mapStateToProps = (state: AppState) => {
    const currentSolutionId = selectors.data.solutions.selectCurrentId(state)
    const isReadonly = !selectors.selectUserCanEditSolution(state, {
        solutionId: currentSolutionId,
    })

    return {
        currentSolutionOwner: selectors.data.solutions.selectCurrentSolutionOwner(state),
        currentIsFromGroupPhase: selectors.data.solutions.selectCurrentSolutionFromGroupPhase(state),
        videoCodesById: selectors.data.videoCodes.selectById(state),
        videoCodes: selectors.data.selectCurrentVideoCodesByStartTime(state),
        prototypes: selectors.data.videoCodePrototypes.selectById(state),
        previousSolutions: selectors.selectActiveSolutionsWithVideoCodes(state),
        exercisePhaseType: selectors.config.selectPhaseType(state),
        isSolutionView: selectors.config.selectIsSolutionView(state),
        isReadonly,
    }
}

const mapDispatchToProps = {}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const componentName = getComponentName(TabsTypesEnum.VIDEO_CODES)

const VideoCodeLaneContainer = (props: Props) => {
    const renderExercisePhaseEditorMediaLane = useMemo(() => {
        if (!props.isSolutionView && props.exercisePhaseType === ExercisePhaseTypesEnum.VIDEO_ANALYSIS) {
            const mediaItems = mergeCodesAndPrototypesToItems(props.videoCodes, props.prototypes)

            return (
                <div>
                    <MediaLaneDescription
                        componentName={componentName}
                        itemCount={mediaItems.length}
                        userName={props.currentSolutionOwner.userName}
                        fromGroupPhase={props.currentIsFromGroupPhase}
                    />
                    <VideoCodesMediaLane mediaItems={mediaItems} readOnly={props.isReadonly} />
                </div>
            )
        }

        return null
    }, [
        props.currentIsFromGroupPhase,
        props.currentSolutionOwner.userName,
        props.exercisePhaseType,
        props.isReadonly,
        props.isSolutionView,
        props.prototypes,
        props.videoCodes,
    ])

    return (
        <>
            {renderExercisePhaseEditorMediaLane}
            {props.previousSolutions.map((solution) => {
                const videoCodes = solution.solutionData.videoCodes.map((id) => props.videoCodesById[id])
                const mediaItems = mergeCodesAndPrototypesToItems(videoCodes, props.prototypes)

                return (
                    <div key={solution.id}>
                        <MediaLaneDescription
                            componentName={componentName}
                            itemCount={mediaItems.length}
                            userName={solution.userName}
                            fromGroupPhase={solution.fromGroupPhase}
                            isPreviousSolution={true}
                        />
                        <VideoCodesMediaLane mediaItems={mediaItems} readOnly />
                    </div>
                )
            })}
        </>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(VideoCodeLaneContainer))
