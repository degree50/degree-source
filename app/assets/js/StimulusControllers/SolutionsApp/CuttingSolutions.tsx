import React from 'react'
import { connect } from 'react-redux'
import ConnectedVideoJSPlayer from 'Components/VideoPlayer/ConnectedVideoJSPlayer'
import Toolbar from 'Components/VideoEditor/components/Toolbar'
import VideoCutSolutionVideo from './VideoCutSolutionVideo'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import OverlayContainer from 'Components/ToolbarItems/components/OverlayContainer'

const mapStateToProps = (state: AppState) => ({
    videos: selectors.config.selectVideos(state),
    solutions: selectors.selectActiveSolutionsWithCuts(state),
})

const mapDispatchToProps = {}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CuttingSolutions = (props: Props) => {
    const firstVideo = props.videos[0]

    return (
        <div className="video-editor">
            <ConnectedVideoJSPlayer
                videoJsOptions={{
                    autoplay: false,
                    controls: true,
                    fluid: false,
                    // @ts-ignore
                    fill: true,
                }}
                videoMap={firstVideo}
            />
            <Toolbar />
            <OverlayContainer />
            <div className="cutting-solutions" data-test-id="cuttingSolutions">
                {props.solutions.map((s) => (
                    <div key={s.id} className="cutting-solution">
                        <h5>
                            {s.fromGroupPhase ? 'Lösung der Gruppe von' : 'Lösung von'} {s.userName}:
                        </h5>
                        <div className="cutting-solution__video">
                            <VideoCutSolutionVideo videoConfig={s.cutVideo} />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(CuttingSolutions))
