import React from 'react'
import MenuButton from '../MenuButton'
import MenuItem from '../MenuItem'
import { actions, selectors } from 'Components/VideoEditor/MediaLaneRenderConfigSlice'
import { MediaLaneHeightModifier } from '../MediaLane/MediaTrack'
import { connect } from 'react-redux'
import { VideoEditorState } from 'Components/VideoEditor/VideoEditorSlice'

const getHeightModifierLabel = (modifier: MediaLaneHeightModifier) => {
    return `${modifier * 100}%`
}

const mapStateToProps = (state: VideoEditorState) => {
    return {
        heightModifier: selectors.selectHeightModifier(state.videoEditor),
    }
}

const mapDispatchToProps = {
    setHeightModifier: actions.setHeightModifier,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const LaneHeightMenu = (props: Props) => {
    const heightModifierLabel = getHeightModifierLabel(props.heightModifier)
    const menuButtonLabel = `Zeilenhöhe: ${heightModifierLabel}`
    const icon = <i className="fa-solid fa-line-height"></i>

    return (
        <div className="video-editor-menu">
            <MenuButton
                label={menuButtonLabel}
                icon={icon}
                ariaLabel={menuButtonLabel}
                pauseVideo
                className="media-lane-toolbar__menu-button"
                closeMenuOnItemSelect
            >
                <MenuItem
                    isActive={props.heightModifier === 0.25}
                    ariaLabel="25% Höhe"
                    label="25%"
                    onClick={() => props.setHeightModifier(0.25)}
                />
                <MenuItem
                    isActive={props.heightModifier === 0.5}
                    ariaLabel="50% Höhe"
                    label="50%"
                    onClick={() => props.setHeightModifier(0.5)}
                />
                <MenuItem
                    isActive={props.heightModifier === 1}
                    ariaLabel="100% Höhe"
                    label="100%"
                    onClick={() => props.setHeightModifier(1)}
                />
            </MenuButton>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(LaneHeightMenu))
