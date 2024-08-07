import { timeToSecond } from './utils/time'
import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'
import {
    ANNOTATIONS_API_PROPERTY,
    CUTLIST_API_PROPERTY,
    MATERIAL_API_PROPERTY,
    VIDEO_CODE_PROTOTYPE_API_PROPERTY,
    VIDEO_CODES_API_PROPERTY,
} from 'StimulusControllers/normalizeData'
import { MaterialId } from 'StimulusControllers/ExerciseAndSolutionStore/MaterialsSlice'
import { AnnotationId } from 'Components/ToolbarItems/AnnotationsContext/AnnotationsSlice'
import { CutId } from 'Components/ToolbarItems/CuttingContext/CuttingSlice'
import { VideoCodePrototypeId } from 'Components/ToolbarItems/VideoCodesContext/VideoCodePrototypesSlice'
import { VideoCodeId } from 'Components/ToolbarItems/VideoCodesContext/VideoCodesSlice'

export enum MediaItemTypeEnum {
    annotation = 'annotation',
    videoCode = 'videoCode',
    cut = 'cut',
}

export type VideoCodePrototype = {
    id: string
    name: string
    color: string
    userCreated: boolean
    videoCodes: Array<VideoCodePrototype>
    parentId?: string
}

// Media item type without methods, so that it is serializable
export type MediaItemType = {
    start: string
    end: string
    text: string
    memo: string
    color: null | string
    solutionId?: string
}

export type MediaItemTypeWithTypeInformation = MediaItemType & {
    type: MediaItemTypeEnum
}

export type AnnotationFromAPI = MediaItemType & { id?: string }
export type Annotation = Omit<AnnotationFromAPI, 'id'> & { id: string }

export type VideoCodeFromAPI = MediaItemType & { id?: string } & {
    idFromPrototype: string
}
export type VideoCode = Omit<VideoCodeFromAPI, 'id'> & { id: string }

export type CutFromAPI = MediaItemType & { id?: string } & {
    offset: number
    playbackRate: number
}

export type Cut = Omit<CutFromAPI, 'id'> & { id: string }
export type CutList = Array<Cut>

export type SolutionId = string

export type SolutionData = {
    [ANNOTATIONS_API_PROPERTY]: AnnotationId[]
    [VIDEO_CODES_API_PROPERTY]: VideoCodeId[]
    [CUTLIST_API_PROPERTY]: CutId[]
    [VIDEO_CODE_PROTOTYPE_API_PROPERTY]: VideoCodePrototypeId[]
    [MATERIAL_API_PROPERTY]: MaterialId
}

export enum ExercisePhaseStatus {
    INITIAL = 'INITIAL',
    IN_BEARBEITUNG = 'IN_BEARBEITUNG',
    IN_REVIEW = 'IN_REVIEW',
    BEENDET = 'BEENDET',
}

export type Solution = {
    id: SolutionId
    userId?: string
    userName?: string
    solutionData: SolutionData
    cutVideo?: Video
    fromGroupPhase?: boolean
    status: ExercisePhaseStatus
}

export type VideoListsState = {
    videoCodes: Array<VideoCodeFromAPI>
    annotations: Array<AnnotationFromAPI>
    customVideoCodesPool: Array<VideoCodePrototype>
    cutList: CutList
}

export class MediaItem<T> {
    start: string
    end: string
    text: string
    memo: string
    color: null | string
    lane: number
    originalData: T & { id?: string }

    constructor({
        start,
        end,
        text,
        memo,
        color = null,
        originalData,
        lane = 0,
    }: {
        start: string
        end: string
        text: string
        memo: string
        color?: string | null
        lane: number
        originalData: T & { id?: string }
    }) {
        this.start = start
        this.end = end
        this.text = text
        this.memo = memo
        this.color = color
        this.originalData = originalData
        this.lane = lane
    }

    public startsBefore(item: MediaItem<any>): boolean {
        return this.startTime > item.startTime
    }

    get check(): boolean {
        return this.startTime >= 0 && this.endTime >= 0 && this.startTime < this.endTime
    }

    get clone(): MediaItem<T> {
        return new MediaItem({
            start: this.start,
            end: this.end,
            text: this.text,
            memo: this.memo,
            color: this.color,
            originalData: this.originalData,
            lane: this.lane,
        })
    }

    get startTime(): number {
        return timeToSecond(this.start)
    }

    get endTime(): number {
        return timeToSecond(this.end)
    }

    get duration(): string {
        return (this.endTime - this.startTime).toFixed(3)
    }
}

export type Player = {
    pause: boolean
    duration: number
    seek: number
    playing: boolean
    currentTime: number
    subtitle: { switch: (data: Record<string, unknown>) => void }
}
