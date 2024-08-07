import MenuButton from 'Components/VideoEditor/components/MenuButton'
import MenuItem from 'Components/VideoEditor/components/MenuItem'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const prefix = 'VIDEO_CODE'
export const VideoCodeOverlayIds = {
    list: `{$prefix}/list`,
    active: `${prefix}/active`,
    create: `${prefix}/create`,
    all: `${prefix}/all`,
    edit: `${prefix}/edit`,
    editPrototype: `${prefix}/editPrototype`,
    remove: `${prefix}/remove`,
    removePrototype: `${prefix}/removePrototype`,
}

const mapStateToProps = (state: AppState) => {
    return {
        allVideoCodesCount: selectors.selectAllVideoCodeIdsByStartTime(state).length,
        activeVideoCodeCount: selectors.selectAllActiveVideoCodeIdsAtCursor(state).length,
        userCanCreateVideoCodes: selectors.selectCanUserCreateVideoCodes(state),
        enabled: selectors.selectVideoCodeMenuEnabled(state),
    }
}

const mapDispatchToProps = {
    setOverlay: actions.overlay.setOverlay,
    setCurrentlyEditedElementIndex: actions.overlay.setCurrentlyEditedElementId,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const VideoCodesMenu: FC<Props> = (props) => {
    const activeCodesAriaLabel = `Aktive Codierungen (${props.activeVideoCodeCount} aktive Codierungen)`
    const allCodesAriaLabel = `Alle Codierungen (${props.allVideoCodesCount} Codierungen)`
    const activeCodesLabel = `Aktive Codierungen (${props.activeVideoCodeCount})`
    const allCodesLabel = `Alle Codierungen (${props.allVideoCodesCount})`
    const menuButtonAriaLabel = `Codierungen (${props.activeVideoCodeCount} aktive Codierungen)`

    return (
        <div className="video-editor-menu">
            {props.activeVideoCodeCount > 0 && (
                <div className="video-editor-menu__count-badge">{props.activeVideoCodeCount}</div>
            )}
            <MenuButton
                icon={<i className="fa fa-tag" />}
                ariaLabel={menuButtonAriaLabel}
                disabled={!props.enabled}
                pauseVideo
                data-test-id="video-codes-menu"
            >
                <MenuItem
                    ariaLabel={activeCodesAriaLabel}
                    label={activeCodesLabel}
                    onClick={() =>
                        props.setOverlay({
                            overlayId: VideoCodeOverlayIds.active,
                            closeOthers: true,
                        })
                    }
                />
                <MenuItem
                    ariaLabel="Setze neue Codierung"
                    label="Setze neue Codierung"
                    onClick={() =>
                        props.setOverlay({
                            overlayId: VideoCodeOverlayIds.create,
                            closeOthers: true,
                        })
                    }
                    disabled={!props.userCanCreateVideoCodes}
                />
                <MenuItem
                    ariaLabel={allCodesAriaLabel}
                    label={allCodesLabel}
                    onClick={() =>
                        props.setOverlay({
                            overlayId: VideoCodeOverlayIds.all,
                            closeOthers: true,
                        })
                    }
                />
                <MenuItem
                    ariaLabel="Codesystem"
                    label="Codesystem"
                    onClick={() =>
                        props.setOverlay({
                            overlayId: VideoCodeOverlayIds.list,
                            closeOthers: true,
                        })
                    }
                />
            </MenuButton>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(VideoCodesMenu))
