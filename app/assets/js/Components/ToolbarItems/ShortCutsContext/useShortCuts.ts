import { useHotkeys } from 'react-hotkeys-hook'
import { useAppDispatch, useAppSelector } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import { initializeShortCuts } from './ShortCutsSaga'
import { useEffect } from 'react'
import { selectHotKeyByShortCutId, ShortCutId } from './ShortCutsSlice'
import {
    setCurrentTimeAsEndValueAction,
    setCurrentTimeAsStartValueAction,
} from './shortCutSagas/SetCurrentTimeAsValueShortCutSaga'
import { AnnotationOverlayIds } from '../AnnotationsContext/AnnotationsMenu'
import { VideoCodeOverlayIds } from '../VideoCodesContext/VideoCodesMenu'
import { CutOverlayIds } from '../CuttingContext/CuttingMenu'
import { togglePlayShortCutAction } from './shortCutSagas/togglePlayShortCutSaga'
import { openOverlayAction } from './shortCutSagas/openOverlayShortCutSaga'
import { initializeSoundOptionsAction } from './shortCutSoundsSaga'
import { SetVideoPlayerTimeOverlayId } from '../SetVideoPlayerTimeContext/Overlays/SetVideoPlayerTimeOverlay'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AllMediaItemsOverlayIds } from 'Components/ToolbarItems/AllMediaItemsContext/AllMediaItemsMenu'

export const useShortCuts = () => {
    const dispatch = useAppDispatch()
    const userCanCreateAnnotations = useAppSelector(selectors.selectCanUserCreateAnnotations)
    const userCanCreateVideoCodes = useAppSelector(selectors.selectCanUserCreateVideoCodes)
    const userCanCreateVideoCuts = useAppSelector(selectors.selectCanUserCreateVideoCuts)

    useEffect(() => {
        dispatch(initializeShortCuts())
        dispatch(initializeSoundOptionsAction())
        // TODO
        // should we add dispatch here? Or is this supposed to run only once?
        // If the latter is the case, please document this and add the respective eslint-ignore comment
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [])

    /**
     * create hotKey for each shortCut
     * We could also handle all shortCuts in a single `useHotKeys` hook, but for now this way is easier
     */
    // togglePlay
    const togglePlayHotKey = useAppSelector((state) => selectHotKeyByShortCutId(state, ShortCutId.TOGGLE_PLAY))
    useHotkeys(
        togglePlayHotKey,
        (keyboardEvent) => {
            dispatch(togglePlayShortCutAction())

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )

    // setCurrentTimeAsStartValue
    const setCurrentTimeAsStartValueHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.SET_CURRENT_TIME_AS_START_VALUE)
    )
    useHotkeys(
        setCurrentTimeAsStartValueHotKey,
        (keyboardEvent) => {
            dispatch(setCurrentTimeAsStartValueAction())

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )

    // setCurrentTimeAsEndValue
    const setCurrentTimeAsEndValueHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.SET_CURRENT_TIME_AS_END_VALUE)
    )
    useHotkeys(
        setCurrentTimeAsEndValueHotKey,
        (keyboardEvent) => {
            dispatch(setCurrentTimeAsEndValueAction())

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )

    // createAnnotation
    const createAnnotationHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.CREATE_ANNOTATION)
    )
    useHotkeys(
        createAnnotationHotKey,
        (keyboardEvent) => {
            dispatch(openOverlayAction(AnnotationOverlayIds.create))

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'], enabled: userCanCreateAnnotations },
        [dispatch]
    )

    // createVideoCode
    const createVideoCodeHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.CREATE_VIDEO_CODE)
    )
    useHotkeys(
        createVideoCodeHotKey,
        (keyboardEvent) => {
            dispatch(openOverlayAction(VideoCodeOverlayIds.create))

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'], enabled: userCanCreateVideoCodes },
        [dispatch]
    )

    // createVideoCut
    const createVideoCutHotKey = useAppSelector((state) => selectHotKeyByShortCutId(state, ShortCutId.CREATE_VIDEO_CUT))
    useHotkeys(
        createVideoCutHotKey,
        (keyboardEvent) => {
            dispatch(openOverlayAction(CutOverlayIds.create))

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'], enabled: userCanCreateVideoCuts },
        [dispatch]
    )

    // setVideoPlayerTime
    const setVideoPlayerTimeHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.SET_VIDEO_PLAYER_TIME)
    )
    useHotkeys(
        setVideoPlayerTimeHotKey,
        (keyboardEvent) => {
            dispatch(openOverlayAction(SetVideoPlayerTimeOverlayId))

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )

    // toggleVideoFavorite
    const toggleVideoFavoriteHotkey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.TOGGLE_VIDEO_FAVORITE)
    )
    const video = useAppSelector(selectors.config.selectVideos)[0]

    useHotkeys(
        toggleVideoFavoriteHotkey,
        (keyboardEvent) => {
            if (video) {
                // @ts-ignore
                dispatch(actions.config.toggleVideoFavorite(video.id))
            }

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )

    // createVideoCode
    const showGesamtlisteHotKey = useAppSelector((state) =>
        selectHotKeyByShortCutId(state, ShortCutId.SHOW_GESAMTLISTE)
    )
    useHotkeys(
        showGesamtlisteHotKey,
        (keyboardEvent) => {
            dispatch(openOverlayAction(AllMediaItemsOverlayIds.all))

            keyboardEvent.preventDefault()
            keyboardEvent.stopPropagation()
        },
        { enableOnTags: ['SELECT', 'INPUT', 'TEXTAREA'] },
        [dispatch]
    )
}
