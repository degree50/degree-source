import React from 'react'
import { connect } from 'react-redux'

import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import MediaLane from '../../MediaLane/index'
import { Cut, MediaItem } from '../../../types'
import { solveConflicts } from '../../../utils/solveItemConflicts'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { useCuttingMediaItemHandling } from 'Components/ToolbarItems/CuttingContext/util'

type OwnProps = {
    cuts: Cut[]
    readOnly?: boolean
}

const mapStateToProps = (state: AppState) => ({
    mediaLaneRenderConfig: selectors.videoEditor.mediaLaneRenderConfig.selectRenderConfig(state.videoEditor),
})

const mapDispatchToProps = {
    syncSolution: syncSolutionAction,
    setCuts: actions.data.cuts.set,
    removeCut: actions.data.cuts.remove,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps & OwnProps

const CutMedialane = (props: Props) => {
    const itemsFromCuts = props.cuts.map(
        (cut) =>
            new MediaItem({
                start: cut.start,
                end: cut.end,
                text: cut.text,
                memo: cut.memo,
                originalData: cut,
                lane: 0,
            })
    )

    const mediaItems: MediaItem<Cut>[] = solveConflicts(itemsFromCuts) as MediaItem<Cut>[]

    const { updateMediaItem } = useCuttingMediaItemHandling({
        mediaItems,
        setCutList: props.setCuts,
        updateCallback: props.syncSolution,
        updateCondition: true, // TODO
    })

    return (
        <MediaLane
            mediaItems={mediaItems}
            updateMediaItem={updateMediaItem}
            showTextInMediaItems={false}
            readOnly={props.readOnly}
        />
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(CutMedialane))
