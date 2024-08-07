import React from 'react'
import { connect } from 'react-redux'

import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import MediaLane from '../../MediaLane/index'
import { VideoCode, MediaItem } from '../../../types'
import { solveConflicts } from '../../../utils/solveItemConflicts'
import { useMediaItemHandling } from '../../../utils/useMediaItemHandling'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

type OwnProps = {
    mediaItems: MediaItem<VideoCode>[]
    readOnly?: boolean
}

const mapStateToProps = (state: AppState) => {
    return {
        mediaLaneRenderConfig: selectors.videoEditor.mediaLaneRenderConfig.selectRenderConfig(state.videoEditor),
    }
}

const mapDispatchToProps = {
    syncSolution: syncSolutionAction,
    setVideoCodes: actions.data.videoCodes.set,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps & OwnProps

const VideoCodeMedialane = (props: Props) => {
    const mediaItems: MediaItem<VideoCode>[] = solveConflicts(props.mediaItems) as MediaItem<VideoCode>[]

    const { updateMediaItem } = useMediaItemHandling<VideoCode>({
        mediaItems,
        setMediaItems: props.setVideoCodes,
        updateCallback: props.syncSolution,
        updateCondition: true, // TODO
    })

    return <MediaLane mediaItems={mediaItems} updateMediaItem={updateMediaItem} readOnly={props.readOnly} />
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(VideoCodeMedialane))
