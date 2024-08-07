import React from 'react'
import { connect } from 'react-redux'
import 'video.js/dist/video-js.css'
import { actions, selectors, VideoEditorState } from '../VideoEditor/VideoEditorSlice'
import VideoJSPlayer from './VideoJSPlayer'

const mapStateToProps = (state: VideoEditorState) => {
    return {
        playPosition: selectors.player.selectPlayPosition(state),
        isPaused: selectors.player.selectIsPaused(state),
    }
}

const mapDispatchToProps = {
    updateTimeCallback: actions.player.setSyncPlayPosition,
    setPauseCallback: actions.player.setPause,
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(VideoJSPlayer))
