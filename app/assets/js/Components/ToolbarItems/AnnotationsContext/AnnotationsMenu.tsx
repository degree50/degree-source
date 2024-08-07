import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo, useCallback } from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { useAppDispatch } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import MenuButton from 'Components/VideoEditor/components/MenuButton'
import MenuItem from 'Components/VideoEditor/components/MenuItem'

const prefix = 'ANNOTATION'

export const AnnotationOverlayIds = {
    active: `${prefix}/active`,
    create: `${prefix}/create`,
    all: `${prefix}/all`,
    edit: `${prefix}/edit`,
    remove: `${prefix}/remove`,
}

const mapStateToProps = (state: AppState) => {
    return {
        allAnnotationsCount: selectors.selectAllAnnotationIdsByStartTime(state).length,
        activeAnnotationCount: selectors.selectAllActiveAnnotationIdsAtCursor(state).length,
        userCanCreateAnnotations: selectors.selectCanUserCreateAnnotations(state),
        enabled: selectors.selectAnnotationMenuEnabled(state),
    }
}

const connector = connect(mapStateToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux

const AnnotationsMenu: FC<Props> = (props) => {
    const dispatch = useAppDispatch()

    const setOverlay = useCallback(
        (config) => {
            dispatch(actions.videoEditor.overlay.setOverlay(config))
        },
        [dispatch]
    )

    const activeAnnotationsLabel = `Aktive Annotationen (${props.activeAnnotationCount})`
    const allAnnotationsLabel = `Alle Annotationen (${props.allAnnotationsCount})`
    const activeAnnotationsAriaLabel = `Aktive Annotationen (${props.activeAnnotationCount} aktive Annotationen)`
    const allAnnotationsAriaLabel = `Alle Annotationen (${props.allAnnotationsCount} Annotationen)`
    const menuButtonAriaLabel = `Annotationen (${props.activeAnnotationCount} aktive Annotationen)`

    return (
        <div className="video-editor-menu">
            {props.activeAnnotationCount > 0 && (
                <div className="video-editor-menu__count-badge">{props.activeAnnotationCount}</div>
            )}
            <MenuButton
                icon={<i className="fas fa-pen" />}
                ariaLabel={menuButtonAriaLabel}
                disabled={!props.enabled}
                pauseVideo
            >
                <MenuItem
                    ariaLabel={activeAnnotationsAriaLabel}
                    label={activeAnnotationsLabel}
                    onClick={() =>
                        setOverlay({
                            overlayId: AnnotationOverlayIds.active,
                            closeOthers: true,
                        })
                    }
                />
                <MenuItem
                    ariaLabel="Erstelle Annotation"
                    label="Erstelle Annotation"
                    onClick={() =>
                        setOverlay({
                            overlayId: AnnotationOverlayIds.create,
                            closeOthers: true,
                        })
                    }
                    disabled={!props.userCanCreateAnnotations}
                />
                <MenuItem
                    ariaLabel={allAnnotationsAriaLabel}
                    label={allAnnotationsLabel}
                    onClick={() =>
                        setOverlay({
                            overlayId: AnnotationOverlayIds.all,
                            closeOthers: true,
                        })
                    }
                />
            </MenuButton>
        </div>
    )
}

export default connector(memo(AnnotationsMenu))
