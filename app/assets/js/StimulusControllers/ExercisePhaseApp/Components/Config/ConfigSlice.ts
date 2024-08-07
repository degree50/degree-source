import { Action, createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'
import { Attachment } from '../AttachmentViewer/AttachmentViewer'
import { ComponentTypesEnum, TabsTypesEnum } from 'types'
import { ExercisePhaseTypesEnum } from '../../../ExerciseAndSolutionStore/ExercisePhaseTypesEnum'

export type ComponentId = ComponentTypesEnum | TabsTypesEnum

export type ApiEndpoints = {
    updateSolution: string
    updateCurrentEditor: string
}

export interface ConfigState {
    title: string
    description: string
    type: ExercisePhaseTypesEnum
    userId: string
    userName: string
    isStudent?: boolean
    isGroupPhase: boolean
    dependsOnPreviousPhase: boolean
    previousPhaseType?: ExercisePhaseTypesEnum
    previousPhaseComponents?: Array<ComponentId>
    readOnly: boolean
    components: Array<ComponentId>
    attachments: Array<Attachment>
    videos: Array<Video>
    apiEndpoints: ApiEndpoints
    isSolutionView: boolean
}

const toggleVideoFavorite = createAsyncThunk('config/toggleVideoFavorite', async (videoId: string) => {
    await fetch(`/schreibtisch/video-favorites/toggle/${videoId}`, {
        method: 'POST',
    })

    return videoId
})

const initialState: ConfigState = {
    title: '',
    description: '',
    type: ExercisePhaseTypesEnum.VIDEO_ANALYSIS,
    userId: '',
    userName: '',
    isGroupPhase: false,
    dependsOnPreviousPhase: false,
    previousPhaseType: undefined,
    previousPhaseComponents: undefined,
    readOnly: false,
    components: [],
    attachments: [],
    videos: [],
    apiEndpoints: {
        updateSolution: '',
        updateCurrentEditor: '',
    },
    isSolutionView: false,
}

export const configSlice = createSlice({
    name: 'config',
    initialState,
    reducers: {
        hydrateConfig: (state, action: PayloadAction<ConfigState>): ConfigState => ({
            ...state,
            ...action.payload,
        }),
        setIsSolutionView: (state, _: Action) => ({
            ...state,
            isSolutionView: true,
        }),
    },
    extraReducers: (builder) => {
        builder.addCase(toggleVideoFavorite.fulfilled, (state, _action) => {
            return {
                ...state,
                videos: [
                    {
                        ...state.videos[0],
                        isFavorite: !state.videos[0]?.isFavorite,
                    },
                ],
            }
        })
    },
})

export const actions = {
    ...configSlice.actions,
    toggleVideoFavorite,
}

export type ConfigStateSlice = { config: ConfigState }

const selectConfig = (state: ConfigStateSlice) => state.config
const selectPhaseType = (state: ConfigStateSlice) => state.config.type
const selectUserId = (state: ConfigStateSlice) => state.config.userId
const selectUserName = (state: ConfigStateSlice) => state.config.userName
const selectReadOnly = (state: ConfigStateSlice) => state.config.readOnly
const selectVideos = (state: ConfigStateSlice) => state.config.videos
const selectApiEndpoints = (state: ConfigStateSlice) => state.config.apiEndpoints
const selectIsStudent = (state: ConfigStateSlice) => state.config.isStudent ?? false

export const selectComponents = (state: ConfigStateSlice) => state.config.components
const selectIsGroupPhase = (state: ConfigStateSlice) => state.config.isGroupPhase
const selectTitle = (state: ConfigStateSlice) => state.config.title
const selectDescription = (state: ConfigStateSlice) => state.config.description
const selectIsSolutionView = (state: ConfigStateSlice) => state.config.isSolutionView
const selectDependsOnPreviousPhase = (state: ConfigStateSlice) => state.config.dependsOnPreviousPhase

const selectAnnotationsAreActive = (state: ConfigStateSlice) =>
    state.config.components.includes(TabsTypesEnum.VIDEO_ANNOTATIONS)
const selectVideoCodesAreActive = (state: ConfigStateSlice) =>
    state.config.components.includes(TabsTypesEnum.VIDEO_CODES)
const selectCutsAreActive = (state: ConfigStateSlice) => state.config.components.includes(TabsTypesEnum.VIDEO_CUTTING)

export const selectors = {
    selectConfig,
    selectPhaseType,
    selectUserId,
    selectUserName,
    selectReadOnly,
    selectIsSolutionView,
    selectVideos,
    selectAnnotationsAreActive,
    selectVideoCodesAreActive,
    selectCutsAreActive,
    selectComponents,
    selectIsGroupPhase,
    selectTitle,
    selectDescription,
    selectDependsOnPreviousPhase,
    selectApiEndpoints,
    selectIsStudent,
}

export default configSlice.reducer
