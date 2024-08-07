import React from 'react'
import { connect } from 'react-redux'
import { selectors } from '../../Components/Config/ConfigSlice'
import VideoEditor from 'Components/VideoEditor'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => {
    return {
        videos: selectors.selectVideos(state),
    }
}

type Props = ReturnType<typeof mapStateToProps>

const VideoAnalysis: React.FC<Props> = (props) => {
    return <VideoEditor videos={props.videos} />
}

export default connect(mapStateToProps)(VideoAnalysis)
