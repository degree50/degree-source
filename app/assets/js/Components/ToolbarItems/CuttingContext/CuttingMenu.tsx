import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo, useCallback } from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { useAppDispatch } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import MenuButton from 'Components/VideoEditor/components/MenuButton'
import MenuItem from 'Components/VideoEditor/components/MenuItem'

const prefix = 'CUT'

export const CutOverlayIds = {
    active: `${prefix}/active`,
    create: `${prefix}/create`,
    all: `${prefix}/all`,
    allByCutOrder: `${prefix}/allByCutOrder`,
    edit: `${prefix}/edit`,
    remove: `${prefix}/remove`,
    cutPreview: `${prefix}/cutPreview`,
}

const mapStateToProps = (state: AppState) => {
    const activePhaseType = selectors.config.selectPhaseType(state)
    const isSolutionView = selectors.config.selectIsSolutionView(state)
    const userIsCurrentEditor = selectors.selectUserIsCurrentEditor(state)
    const cutsAreActive = selectors.config.selectCutsAreActive(state)

    const disableCreate =
        isSolutionView || activePhaseType !== ExercisePhaseTypesEnum.VIDEO_CUTTING || !userIsCurrentEditor
    const disablePreview = isSolutionView
    const disabled = !cutsAreActive

    return {
        allCutsCount: selectors.selectAllCutIdsByStartTime(state).length,
        activeCutCount: selectors.selectCurrentCutIdsAtCursor(state).length,
        disableCreate,
        disablePreview,
        disabled,
    }
}

const connector = connect(mapStateToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux

const CutsMenu: FC<Props> = (props) => {
    const dispatch = useAppDispatch()

    const setOverlay = useCallback(
        (config) => {
            dispatch(actions.videoEditor.overlay.setOverlay(config))
        },
        [dispatch]
    )

    const allCutsAriaLabel = `Alle Schnitte (${props.allCutsCount} Schnitte)`
    const activeCutsAriaLabel = `Aktive Schnitte (${props.activeCutCount} aktive Schnitte)`
    const allCutsLabel = `Alle Schnitte (${props.allCutsCount})`
    const activeCutsLabel = `Aktive Schnitte (${props.activeCutCount})`
    const menuButtonAriaLabel = `Schnitte (${props.activeCutCount} aktive Schnitte)`

    return (
        <div className="video-editor-menu">
            {props.activeCutCount > 0 && <div className="video-editor-menu__count-badge">{props.activeCutCount}</div>}
            <MenuButton
                icon={<i className="fas fa-cut" />}
                ariaLabel={menuButtonAriaLabel}
                pauseVideo
                disabled={props.disabled}
            >
                <MenuItem
                    ariaLabel={activeCutsAriaLabel}
                    label={activeCutsLabel}
                    onClick={() => setOverlay({ overlayId: CutOverlayIds.active, closeOthers: true })}
                />
                <MenuItem
                    ariaLabel="Erstelle Schnitt"
                    label="Erstelle Schnitt"
                    onClick={() => setOverlay({ overlayId: CutOverlayIds.create, closeOthers: true })}
                    disabled={props.disableCreate}
                />
                <MenuItem
                    ariaLabel={allCutsAriaLabel}
                    label={allCutsLabel}
                    onClick={() => setOverlay({ overlayId: CutOverlayIds.all, closeOthers: true })}
                />
                <MenuItem
                    ariaLabel="Schnittreihenfolge"
                    label="Schnittreihenfolge"
                    onClick={() =>
                        setOverlay({
                            overlayId: CutOverlayIds.allByCutOrder,
                            closeOthers: true,
                        })
                    }
                    disabled={props.disableCreate}
                />
                <MenuItem
                    ariaLabel="Schnitt Vorschau"
                    label="Schnitt Vorschau"
                    onClick={() =>
                        setOverlay({
                            overlayId: CutOverlayIds.cutPreview,
                            closeOthers: true,
                        })
                    }
                    disabled={props.disablePreview}
                />
            </MenuButton>
        </div>
    )
}

export default connector(memo(CutsMenu))
