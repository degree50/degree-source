import React from 'react'
import { connect } from 'react-redux'
import { selectComponent, selectIsVisible, selectSize, setOverlayVisibility } from './OverlaySlice'
import FileUpload from '../FileUpload/FileUpload'
import { ComponentTypesEnum } from '../../../../types'
import AttachmentViewer from '../AttachmentViewer/AttachmentViewer'
import VideoPlayerWrapper, { Video } from '../../../../Components/VideoPlayer/VideoPlayerWrapper'
import Presence from '../Presence/Presence'
import ExerciseDescription from '../ExerciseDescription/ExerciseDescription'
import { selectors } from '../Config/ConfigSlice'
import { AppState, AppDispatch } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

export const overlaySizesEnum = {
    DEFAULT: 'default',
    SMALL: 'small',
    LARGE: 'large',
}

const renderOverlayComponent = (component?: ComponentTypesEnum, videos: Video[] = []) => {
    switch (component) {
        case ComponentTypesEnum.TASK:
            return <ExerciseDescription />
        case ComponentTypesEnum.DOCUMENT_UPLOAD:
            return <FileUpload />
        case ComponentTypesEnum.ATTACHMENT_VIEWER:
            return <AttachmentViewer />
        case ComponentTypesEnum.VIDEO_PLAYER:
            return <VideoPlayerWrapper videos={videos} />
        case ComponentTypesEnum.PRESENCE:
            return <Presence />
        default: {
            return null
        }
    }
}

const mapStateToProps = (state: AppState) => ({
    isVisible: selectIsVisible(state),
    component: selectComponent(state),
    size: selectSize(state),
    videos: selectors.selectConfig(state).videos,
})

const mapDispatchToProps = (dispatch: AppDispatch) => ({
    setOverlayVisibility: (isVisible: boolean) => dispatch(setOverlayVisibility(isVisible)),
})

type OverlayProps = ReturnType<typeof mapStateToProps> & ReturnType<typeof mapDispatchToProps>

const Overlay: React.FC<OverlayProps> = (props) => {
    const handleVisibilityToggle = () => {
        props.setOverlayVisibility(!props.isVisible)
    }

    const sizeClass = 'overlay--' + props.size
    const className = props.isVisible ? 'overlay overlay--is-visible' : 'overlay'
    return (
        <div className={className + ' ' + sizeClass}>
            <button className={'overlay__close button'} type="button" onClick={handleVisibilityToggle}>
                <i className={'fas fa-times'} />
            </button>
            <div className={'overlay__content'}>{renderOverlayComponent(props.component, props.videos)}</div>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(Overlay)
