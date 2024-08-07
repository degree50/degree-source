import React, { FC, memo, useState } from 'react'
import ConnectedVideoJSPlayer from 'Components/VideoPlayer/ConnectedVideoJSPlayer'
import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'
import MediaLaneContainer from './components/MediaLaneContainer'
import OverlayContainer from 'Components/ToolbarItems/components/OverlayContainer'
import Toolbar from './components/Toolbar'
import { useShortCuts } from 'Components/ToolbarItems/ShortCutsContext/useShortCuts'
import classNames from 'classnames'

type Props = {
    videos: Array<Video>
}

const VideoEditor: FC<Props> = (props) => {
    const firstVideo = props.videos[0]

    useShortCuts()

    const [showMediaLane, setShowMediaLane] = useState(false)
    const [isMediaLaneFullHeight, setIsMediaLaneFullHeight] = useState(false)

    const handleMediaLaneToggle = () => setShowMediaLane(!showMediaLane)
    const handleMediaLaneFullHeightToggle = () => setIsMediaLaneFullHeight(!isMediaLaneFullHeight)

    const videoEditorClassNames = classNames('video-editor', {
        'video-editor--media-lane-full-height': isMediaLaneFullHeight,
        'video-editor--media-lane-visible': showMediaLane && !isMediaLaneFullHeight,
    })

    return (
        <div className={videoEditorClassNames} data-test-id="videoEditor">
            <ConnectedVideoJSPlayer
                hidden={isMediaLaneFullHeight}
                videoJsOptions={{
                    autoplay: false,
                    controls: true,
                    fluid: false,
                    // @ts-ignore
                    fill: true,
                }}
                videoMap={firstVideo}
            />
            <Toolbar hidden={isMediaLaneFullHeight}>
                <div className="video-editor-menu video-editor-menu--right">
                    <button
                        className="button button--type-primary video-editor__toolbar__button video-editor__toolbar__button--with-text"
                        title="Zeitleiste anzeigen/verbergen"
                        aria-label={showMediaLane ? 'Zeitleiste verbergen' : 'Zeitleiste anzeigen'}
                        onClick={handleMediaLaneToggle}
                    >
                        <i className={showMediaLane ? 'fas fa-chevron-down' : 'fas fa-chevron-up'} />
                        {showMediaLane ? 'Zeitleiste verbergen' : 'Zeitleiste anzeigen'}
                    </button>
                </div>
            </Toolbar>
            <OverlayContainer />
            <MediaLaneContainer
                showMediaLane={showMediaLane}
                isFullHeight={isMediaLaneFullHeight}
                toggleFullHeight={handleMediaLaneFullHeightToggle}
            />
        </div>
    )
}

export default memo(VideoEditor)
