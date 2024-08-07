import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'

export type Fachbereich = {
    id: string
    name: string
}

export type Course = {
    id: string
    name: string
}

export type ExerciseId = string
export type Exercise = {
    id: ExerciseId
    name: string
    fachbereich?: Fachbereich
    course: Course
    status: 'IN_BEARBEITUNG' | 'IN_REVIEW' | 'NEU' | 'BEENDET'
    phaseCount: number
    completedPhases: number
    lastEditedAt?: { date: string }
}

export type VideoFavoriteId = string
export type VideoFavorite = {
    id: VideoFavoriteId
    video: Video & {
        fachbereiche: Array<Fachbereich>
        courses: Array<Course>
    }
}

export type MaterialId = string
export type Material = {
    id: MaterialId
    material: string
    owner: string
    name: string
    originalExercisePhaseTeamId: string
    originalExercisePhaseName: string
    originalExercisePhaseUrl: string
    createdAt: { date: string }
    lastUpdatedAt?: { date: string }
    fachbereich?: Fachbereich
    course?: Course
}
