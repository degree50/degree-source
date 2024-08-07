import { Solution } from 'Components/VideoEditor/types'
import { generate } from 'shortid'
import { ConfigState } from './ExercisePhaseApp/Components/Config/ConfigSlice'
import { TabsTypesEnum } from '../types'
import { DataState } from 'StimulusControllers/ExerciseAndSolutionStore/DataSlice'
import {
    FilterState,
    videoComponents,
    VideoComponentId,
    ActiveComponent,
    ActivePreviousSolution,
} from 'Components/ToolbarItems/FilterContext/FilterSlice'

export const addIdsToEntities = <E extends { id?: string }>(entities: Array<E>) =>
    entities.map((e) => ({ ...e, id: e?.id ?? generate() }))

export const ANNOTATIONS_API_PROPERTY = 'annotations'
export const VIDEO_CODES_API_PROPERTY = 'videoCodes'
export const CUTLIST_API_PROPERTY = 'cutList'
export const VIDEO_CODE_PROTOTYPE_API_PROPERTY = 'videoCodePrototypes'
export const MATERIAL_API_PROPERTY = 'material'

export const normalizeFilterData = (config: ConfigState, data: DataState) => {
    const previousSolutions = data.solutions.previous.map((id) => data.solutions.byId[id])

    return {
        ...initializeComponentFilter(config, previousSolutions),
        ...initializePreviousSolutionsFilter(previousSolutions),
    }
}

/**
 * Determines which videoComponents (cuts, codes, annotations) should be available
 * by checking the config components as well as components from previous solutions.
 */
export const initializeComponentFilter = (
    config: ConfigState,
    previousSolutions: Solution[]
): Pick<FilterState, 'components' | 'componentIds'> => {
    const idsFromPreviousSolutions = previousSolutions.reduce((acc: TabsTypesEnum[], solution) => {
        const hasCodes = solution.solutionData.videoCodes.length > 0
        const hasAnnotations = solution.solutionData.annotations.length > 0
        const hasCuts = solution.solutionData.cutList.length > 0

        return [
            ...acc,
            ...(hasCodes ? [TabsTypesEnum.VIDEO_CODES] : []),
            ...(hasAnnotations ? [TabsTypesEnum.VIDEO_ANNOTATIONS] : []),
            ...(hasCuts ? [TabsTypesEnum.VIDEO_CUTTING] : []),
        ]
    }, [])

    const uniqueIds = new Set([...config.components, ...idsFromPreviousSolutions])

    const allComponentIds = videoComponents.filter((c) => [...uniqueIds].includes(c))
    const components = allComponentIds.reduce((acc: Record<VideoComponentId, ActiveComponent>, id) => {
        return {
            ...acc,
            [id]: { id, isVisible: true },
        }
    }, {} as Record<VideoComponentId, ActiveComponent>)

    return {
        componentIds: allComponentIds,
        components,
    }
}

export const initializePreviousSolutionsFilter = (
    previousSolutions: { id: string }[]
): Pick<FilterState, 'previousSolutions' | 'previousSolutionIds'> => {
    const activePreviousSolutions = previousSolutions.reduce((acc: Record<string, ActivePreviousSolution>, s) => {
        return {
            ...acc,
            [s.id]: { id: s.id, isVisible: true },
        }
    }, {} as Record<string, ActivePreviousSolution>)

    const previousSolutionIds = previousSolutions.map((s) => s.id)

    return {
        previousSolutions: activePreviousSolutions,
        previousSolutionIds,
    }
}
