import React from 'react'
import { connect } from 'react-redux'
import { VideoEditorState, selectors as videoEditorSelectors, actions } from 'Components/VideoEditor/VideoEditorSlice'
import Filter from './Filter'
import Toolbar from '../MediaLaneToolbar'
import { TabsTypesEnum } from 'types'
import {
    ComponentId,
    ConfigStateSlice,
    selectors as configSelectors,
} from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import AnnotationLaneContainer from './AnnotationLaneContainer'
import VideoCodeLaneContainer from './VideoCodeLaneContainer'
import CutLaneContainer from './CutLaneContainer'
import { useMediaLaneClick } from '../MediaLane/useMediaLaneClick'
import MediaLaneToolbarItem from '../MediaLaneToolbar/MediaLaneToolbarItem'
import LaneHeightMenu from './LaneHeightMenu'
import MediaLaneFullHeightToggle from './MediaLaneFullHeightToggle'

const getMediaLaneContainerComponentById = (componentId: ComponentId) => {
    switch (componentId) {
        case TabsTypesEnum.VIDEO_ANNOTATIONS: {
            return AnnotationLaneContainer
        }

        case TabsTypesEnum.VIDEO_CODES: {
            return VideoCodeLaneContainer
        }

        case TabsTypesEnum.VIDEO_CUTTING: {
            return CutLaneContainer
        }

        default: {
            return undefined
        }
    }
}

type OwnProps = {
    isFullHeight: boolean
    toggleFullHeight: () => void
}

const mapStateToProps = (state: VideoEditorState & ConfigStateSlice) => ({
    videos: configSelectors.selectVideos(state),
    mediaLaneRenderConfig: videoEditorSelectors.mediaLaneRenderConfig.selectRenderConfig(state.videoEditor),
    components: videoEditorSelectors.filter.selectComponents(state),
})

const mapDispatchToProps = {
    setPlayPosition: actions.player.setPlayPosition,
    setRenderConfig: actions.mediaLaneRenderConfig.setRenderConfig,
    updateZoom: actions.mediaLaneRenderConfig.updateZoom,
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const MultiLane = (props: Props) => {
    const { handleMediaLaneClick } = useMediaLaneClick(
        props.mediaLaneRenderConfig,
        props.setRenderConfig,
        props.setPlayPosition
    )

    const firstVideo = props.videos[0]

    if (!firstVideo) {
        return null
    }

    const firstVideoDuration = firstVideo.duration

    return (
        <div className="video-editor-timeline">
            <Toolbar
                handleTimeLineAction={handleMediaLaneClick}
                renderConfig={props.mediaLaneRenderConfig}
                videoDuration={firstVideoDuration}
                updateZoom={props.updateZoom}
            >
                <MediaLaneToolbarItem>
                    <LaneHeightMenu />
                </MediaLaneToolbarItem>
                <MediaLaneToolbarItem>
                    <MediaLaneFullHeightToggle
                        isFullHeight={props.isFullHeight}
                        toggleFullHeight={props.toggleFullHeight}
                    />
                </MediaLaneToolbarItem>
            </Toolbar>
            <div className="video-editor-timeline__entries multilane">
                <Filter />

                <div className="multilane__content">
                    {props.components.map((component) => {
                        if (!component.isVisible) {
                            return null
                        }

                        const MediaLaneContainer = getMediaLaneContainerComponentById(component.id)
                        if (!MediaLaneContainer) {
                            return null
                        }

                        return <MediaLaneContainer key={component.id} />
                    })}
                </div>
            </div>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(MultiLane))
