import { combineReducers, createSelector } from '@reduxjs/toolkit'
import configReducer, {
    actions as configActions,
    selectors as configSelectors,
} from '../ExercisePhaseApp/Components/Config/ConfigSlice'
import liveSyncConfigReducer, {
    actions as liveSyncConfigActions,
    selectors as liveSyncConfigSelectors,
} from '../ExercisePhaseApp/Components/LiveSyncConfig/LiveSyncConfigSlice'
import overlayReducer from '../ExercisePhaseApp/Components/Overlay/OverlaySlice'
import attachmentViewerReducer from '../ExercisePhaseApp/Components/AttachmentViewer/AttachmentViewerSlice'
import presenceReducer, {
    actions as presenceActions,
    selectors as presenceSelectors,
} from '../ExercisePhaseApp/Components/Presence/PresenceSlice'
import currentEditorReducer, {
    actions as currentEditorActions,
    selectors as currentEditorSelectors,
} from '../ExercisePhaseApp/Components/Presence/CurrentEditorSlice'
import VideoEditorSlice, {
    actions as videoEditorActions,
    selectors as videoEditorSelectors,
} from 'Components/VideoEditor/VideoEditorSlice'
import shortCutsReducer from '../../Components/ToolbarItems/ShortCutsContext/ShortCutsSlice'
import shortCutSoundOptionsReducer from '../../Components/ToolbarItems/ShortCutsContext/ShortCutSoundsSlice'
import DataSlice, {
    actions as dataActions,
    selectors as dataSelectors,
} from 'StimulusControllers/ExerciseAndSolutionStore/DataSlice'
import { sortByStartTime, timeToSecond } from 'Components/VideoEditor/utils/time'
import { Annotation, Cut, MediaItemTypeEnum, VideoCode } from 'Components/VideoEditor/types'
import { annotationWithCreatorNameAsRichtext } from 'Components/VideoEditor/composedSelectors/annotations'
import { videoCodeAsRichtext } from 'Components/VideoEditor/composedSelectors/videoCodes'
import { cutAsRichtext } from 'Components/VideoEditor/composedSelectors/cuts'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { TabsTypesEnum } from 'types'

export const RootReducer = combineReducers({
    videoEditor: VideoEditorSlice,
    data: DataSlice,
    config: configReducer,
    liveSyncConfig: liveSyncConfigReducer,
    overlay: overlayReducer,
    attachmentViewer: attachmentViewerReducer,
    presence: presenceReducer,
    currentEditor: currentEditorReducer,
    shortCuts: shortCutsReducer,
    shortCutSoundOptions: shortCutSoundOptionsReducer,
})

export const actions = {
    videoEditor: videoEditorActions,
    data: dataActions,
    config: configActions,
    liveSyncConfig: liveSyncConfigActions,
    currentEditor: currentEditorActions,
    presence: presenceActions,
}

/*************
 * Selectors *
 *************/

export const selectUserIsCurrentEditor = createSelector(
    [configSelectors.selectUserId, currentEditorSelectors.selectCurrentEditorId],
    (userId, editorId) => editorId !== undefined && userId === editorId
)

export const selectUserCanEditSolution = createSelector(
    [configSelectors.selectReadOnly, selectUserIsCurrentEditor, dataSelectors.solutions.selectIsCurrentSolution],
    (isReadonly, userIsCurrentEditor, isCurrentSolution) => {
        if (isReadonly) {
            return false
        }

        return userIsCurrentEditor && isCurrentSolution
    }
)

const isAtCursor = (entity: { start: string; end: string }, currentPlayPosition: number) =>
    timeToSecond(entity.start) <= currentPlayPosition && timeToSecond(entity.end) >= currentPlayPosition

const selectCurrentAnnotationIdsAtCursor = createSelector(
    [dataSelectors.selectCurrentAnnotationsByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (annotations, currentPlayPosition) => {
        return annotations
            .filter((annotation) => isAtCursor(annotation, currentPlayPosition))
            .map((annotation) => annotation.id)
    }
)

const selectCurrentVideoCodeIdsAtCursor = createSelector(
    [dataSelectors.selectCurrentVideoCodesByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (videoCodes, currentPlayPosition) => {
        return videoCodes
            .filter((videoCode) => isAtCursor(videoCode, currentPlayPosition))
            .map((videoCode) => videoCode.id)
    }
)

const selectCurrentCutIdsAtCursor = createSelector(
    [dataSelectors.selectCurrentCutListByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (cuts, currentPlayPosition) => {
        return cuts.filter((videoCode) => isAtCursor(videoCode, currentPlayPosition)).map((cut) => cut.id)
    }
)

const selectSolutionData = createSelector(
    [
        dataSelectors.selectDenormalizedCurrentAnnotations,
        dataSelectors.selectDenormalizedCurrentVideoCodes,
        dataSelectors.selectDenormalizedCurrentCutList,
        dataSelectors.selectCurrentSolutionVideoCodePrototypesList,
        dataSelectors.selectMaterialOfCurrentSolution,
    ],
    (annotations, videoCodes, cutList, videoCodePrototypes, material) => ({
        annotations,
        videoCodes,
        cutList,
        videoCodePrototypes,
        material,
    })
)

const selectActiveSolutionsWithAnnotations = createSelector(
    [
        videoEditorSelectors.filter.selectVisiblePreviousSolutions,
        dataSelectors.solutions.selectById,
        dataSelectors.annotations.selectById,
    ],
    (visibleSolutions, solutionsById, annotationsById) => {
        return visibleSolutions.map((visibleSolution) => {
            const solution = solutionsById[visibleSolution.id]
            const annotations = solution.solutionData.annotations.map((id) => ({
                ...annotationsById[id],
                type: MediaItemTypeEnum.annotation,
            }))

            return {
                ...solution,
                annotations,
            }
        })
    }
)

const selectActiveSolutionsWithVideoCodes = createSelector(
    [
        videoEditorSelectors.filter.selectVisiblePreviousSolutions,
        dataSelectors.solutions.selectById,
        dataSelectors.videoCodes.selectById,
    ],
    (visibleSolutions, solutionsById, videoCodesById) => {
        return visibleSolutions.map((visibleSolution) => {
            const solution = solutionsById[visibleSolution.id]
            const videoCodes = solution.solutionData.videoCodes.map((id) => ({
                ...videoCodesById[id],
                type: MediaItemTypeEnum.videoCode,
            }))

            return {
                ...solution,
                videoCodes,
            }
        })
    }
)

const selectActiveSolutionsWithCuts = createSelector(
    [
        videoEditorSelectors.filter.selectVisiblePreviousSolutions,
        dataSelectors.solutions.selectById,
        dataSelectors.cuts.selectById,
    ],
    (visibleSolutions, solutionsById, cutsById) => {
        return visibleSolutions.map((visibleSolution) => {
            const solution = solutionsById[visibleSolution.id]
            const cutList = solution.solutionData.cutList.map((id) => cutsById[id])

            return {
                ...solution,
                cutList,
            }
        })
    }
)

const selectActiveAnnotationsFromPreviousSolutions = createSelector(
    [selectActiveSolutionsWithAnnotations],
    (solutions) =>
        solutions.reduce((acc: Annotation[], s) => {
            return [...acc, ...s.annotations]
        }, [])
)

const selectActiveVideoCodesFromPreviousSolutions = createSelector([selectActiveSolutionsWithVideoCodes], (solutions) =>
    solutions.reduce((acc: VideoCode[], s) => {
        return [...acc, ...s.videoCodes]
    }, [])
)

const selectActiveCutListFromPreviousSolutions = createSelector([selectActiveSolutionsWithCuts], (solutions) =>
    solutions.reduce((acc: Cut[], s) => {
        return [...acc, ...s.cutList]
    }, [])
)

const selectAllAnnotations = createSelector(
    [selectActiveAnnotationsFromPreviousSolutions, dataSelectors.selectCurrentAnnotations],
    (previousAnnotations, currentAnnotations) => [...previousAnnotations, ...currentAnnotations]
)

const selectAllVideoCodes = createSelector(
    [selectActiveVideoCodesFromPreviousSolutions, dataSelectors.selectCurrentVideoCodes],
    (previousVideoCodes, currentVideoCodes) => [...previousVideoCodes, ...currentVideoCodes]
)

const selectAllCuts = createSelector(
    [selectActiveCutListFromPreviousSolutions, dataSelectors.selectCurrentCuts],
    (previousCuts, currentCuts) => [...previousCuts, ...currentCuts]
)

const selectAllAnnotationsByStartTime = createSelector([selectAllAnnotations], sortByStartTime)
const selectAllVideoCodesByStartTime = createSelector([selectAllVideoCodes], sortByStartTime)
const selectAllCutsByStartTime = createSelector([selectAllCuts], sortByStartTime)

const selectAllAnnotationIdsByStartTime = createSelector([selectAllAnnotationsByStartTime], (annotations) =>
    annotations.map((a) => a.id)
)

const selectAllVideoCodeIdsByStartTime = createSelector([selectAllVideoCodesByStartTime], (videoCodes) =>
    videoCodes.map((vc) => vc.id)
)

const selectAllMediaItemsByStartTime = createSelector(
    [selectAllAnnotations, selectAllVideoCodes, selectAllCuts],
    (annotations, videoCodes, cuts) => {
        return sortByStartTime([
            ...annotations.map((a) => ({ ...a, type: MediaItemTypeEnum.annotation })),
            ...videoCodes.map((vc) => ({ ...vc, type: MediaItemTypeEnum.videoCode })),
            ...cuts.map((c) => ({ ...c, type: MediaItemTypeEnum.cut })),
        ])
    }
)

const selectAllMediaItemsByStartTimeAsRichtext = createSelector(
    [
        selectAllMediaItemsByStartTime,
        dataSelectors.solutions.selectById,
        dataSelectors.videoCodes.selectById,
        dataSelectors.videoCodePrototypes.selectById,
        dataSelectors.cuts.selectById,
        dataSelectors.solutions.selectCurrentId,
    ],
    (mediaItems, solutions, videoCodes, videoCodePrototypes, cuts, currentSolutionId) =>
        mediaItems
            .map((item) => {
                const solution = item.solutionId ? solutions[item.solutionId] : undefined
                const isFromPreviousSolution = Boolean(item.solutionId && item.solutionId !== currentSolutionId)

                const creatorName = solution?.userName ?? '<Unbekannter Ersteller>'

                switch (item.type) {
                    case MediaItemTypeEnum.annotation: {
                        return annotationWithCreatorNameAsRichtext({ ...item, creatorName, isFromPreviousSolution })
                    }
                    case MediaItemTypeEnum.videoCode: {
                        const videoCode = videoCodes[item.id]
                        const videoCodePrototype = videoCodePrototypes[videoCode.idFromPrototype]
                        const parentVideoCodePrototype = videoCodePrototype.parentId
                            ? videoCodePrototypes[videoCodePrototype.parentId]
                            : undefined

                        return videoCodeAsRichtext({
                            videoCode,
                            videoCodePrototype,
                            parentVideoCodePrototype,
                            creatorName,
                            isFromPreviousSolution,
                        })
                    }
                    case MediaItemTypeEnum.cut: {
                        return cutAsRichtext({ cut: cuts[item.id], creatorName, isFromPreviousSolution })
                    }
                    default: {
                        throw new TypeError(`Invalid MediaItemType "${item.type}"`)
                    }
                }
            })
            .join('\n')
)

const selectAllCutIdsByStartTime = createSelector([selectAllCutsByStartTime], (cuts) => cuts.map((c) => c.id))

const selectAllActiveAnnotationIdsAtCursor = createSelector(
    [selectAllAnnotationsByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (annotations, currentPlayPosition) => {
        return annotations
            .filter((annotation) => isAtCursor(annotation, currentPlayPosition))
            .map((annotation) => annotation.id)
    }
)

const selectAllActiveVideoCodeIdsAtCursor = createSelector(
    [selectAllVideoCodesByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (videoCodes, currentPlayPosition) => {
        return videoCodes.filter((vc) => isAtCursor(vc, currentPlayPosition)).map((vc) => vc.id)
    }
)

const selectAllActiveCutIdsAtCursor = createSelector(
    [selectAllCutsByStartTime, videoEditorSelectors.player.selectSyncPlayPosition],
    (cuts, currentPlayPosition) => {
        return cuts.filter((cut) => isAtCursor(cut, currentPlayPosition)).map((cut) => cut.id)
    }
)

const selectCanUserCreateAnnotations = createSelector(
    [configSelectors.selectConfig, selectUserIsCurrentEditor],
    (config, userIsCurrentEditor) => {
        if (
            !config.isSolutionView &&
            config.type === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
            config.components?.includes(TabsTypesEnum.VIDEO_ANNOTATIONS)
        ) {
            return userIsCurrentEditor
        }

        return false
    }
)

const selectCanUserCreateVideoCodes = createSelector(
    [configSelectors.selectConfig, selectUserIsCurrentEditor],
    (config, userIsCurrentEditor) => {
        if (
            !config.isSolutionView &&
            config.type === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
            config.components?.includes(TabsTypesEnum.VIDEO_CODES)
        ) {
            return userIsCurrentEditor
        }

        return false
    }
)

const selectCanUserCreateVideoCuts = createSelector(
    [configSelectors.selectConfig, selectUserIsCurrentEditor],
    (config, userIsCurrentEditor) => {
        if (!config.isSolutionView && config.type === ExercisePhaseTypesEnum.VIDEO_CUTTING) {
            return userIsCurrentEditor
        }

        return false
    }
)

const selectVideoCodeMenuEnabled = createSelector([configSelectors.selectConfig], (config) => {
    if (
        config.dependsOnPreviousPhase &&
        config.previousPhaseType === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
        config.previousPhaseComponents?.includes(TabsTypesEnum.VIDEO_CODES)
    ) {
        return true
    }

    if (
        config.type === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
        config.components?.includes(TabsTypesEnum.VIDEO_CODES)
    ) {
        return true
    }

    return false
})

const selectAnnotationMenuEnabled = createSelector([configSelectors.selectConfig], (config) => {
    if (
        config.dependsOnPreviousPhase &&
        config.previousPhaseType === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
        config.previousPhaseComponents?.includes(TabsTypesEnum.VIDEO_ANNOTATIONS)
    ) {
        return true
    }

    if (
        config.type === ExercisePhaseTypesEnum.VIDEO_ANALYSIS &&
        config.components?.includes(TabsTypesEnum.VIDEO_ANNOTATIONS)
    ) {
        return true
    }

    return false
})

export const selectors = {
    videoEditor: videoEditorSelectors,
    data: dataSelectors,
    config: configSelectors,
    liveSyncConfig: liveSyncConfigSelectors,
    currentEditor: currentEditorSelectors,
    presence: presenceSelectors,

    selectUserIsCurrentEditor,
    selectUserCanEditSolution,
    selectSolutionData,

    selectCanUserCreateAnnotations,
    selectCanUserCreateVideoCodes,
    selectCanUserCreateVideoCuts,

    selectVideoCodeMenuEnabled,
    selectAnnotationMenuEnabled,

    selectAllMediaItemsByStartTime,
    selectAllMediaItemsByStartTimeAsRichtext,

    selectVideoCodeIdsAtCursor: selectCurrentVideoCodeIdsAtCursor,
    selectAllVideoCodesByStartTime,
    selectAllVideoCodeIdsByStartTime,
    selectAllActiveVideoCodeIdsAtCursor,
    selectActiveSolutionsWithVideoCodes,

    selectCurrentAnnotationIdsAtCursor,
    selectAllAnnotationsByStartTime,
    selectAllAnnotationIdsByStartTime,
    selectAllActiveAnnotationIdsAtCursor,
    selectActiveSolutionsWithAnnotations,

    selectCurrentCutIdsAtCursor,
    selectAllCutsByStartTime,
    selectAllCutIdsByStartTime,
    selectAllActiveCutIdsAtCursor,
    selectActiveSolutionsWithCuts,
}
