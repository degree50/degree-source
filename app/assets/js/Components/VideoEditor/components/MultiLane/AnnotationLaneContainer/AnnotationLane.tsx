import React from 'react'
import { connect } from 'react-redux'

import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { MediaItem, Annotation } from 'Components/VideoEditor/types'
import { solveConflicts } from 'Components/VideoEditor/utils/solveItemConflicts'
import { useMediaItemHandling } from 'Components/VideoEditor/utils/useMediaItemHandling'
import MediaLane from '../../MediaLane'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

type OwnProps = {
    annotations: Annotation[]
    readOnly?: boolean
}

const mapStateToProps = (state: AppState) => ({
    mediaLaneRenderConfig: selectors.videoEditor.mediaLaneRenderConfig.selectRenderConfig(state.videoEditor),
})

const mapDispatchToProps = {
    syncSolution: syncSolutionAction,
    setAnnotations: actions.data.annotations.set,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps & OwnProps

const Annotationlane = (props: Props) => {
    const itemsFromAnnotations = props.annotations.map(
        (entity) =>
            new MediaItem({
                start: entity.start,
                end: entity.end,
                text: entity.text,
                memo: entity.memo,
                originalData: entity,
                lane: 0,
            })
    )

    const mediaItems = solveConflicts(itemsFromAnnotations) as MediaItem<Annotation>[]

    const { updateMediaItem } = useMediaItemHandling<Annotation>({
        mediaItems,
        setMediaItems: props.setAnnotations,
        updateCallback: props.syncSolution,
        updateCondition: true, // TODO
    })

    return <MediaLane updateMediaItem={updateMediaItem} mediaItems={mediaItems} readOnly={props.readOnly} />
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(Annotationlane))
