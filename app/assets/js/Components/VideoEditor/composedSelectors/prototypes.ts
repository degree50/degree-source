import { createSelector } from '@reduxjs/toolkit'
import { VideoCodePrototype } from '../types'
import { selectors as solutionSelectors } from '../SolutionSlice'
import { selectors as videoCodePrototypeSelectors } from 'Components/ToolbarItems/VideoCodesContext/VideoCodePrototypesSlice'
import { VIDEO_CODE_PROTOTYPE_API_PROPERTY } from 'StimulusControllers/normalizeData'

const prototypesByName = (a: VideoCodePrototype, b: VideoCodePrototype) => {
    if (a.name < b.name) {
        return -1
    } else if (a.name > b.name) {
        return 1
    } else {
        return 0
    }
}

const selectDenormalizedCurrentPrototypes = createSelector(
    [solutionSelectors.selectCurrentSolutionVideoCodePrototypeIds, videoCodePrototypeSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => byId[id]).sort(prototypesByName)
)

const selectDenormalizedPrototypes = createSelector([videoCodePrototypeSelectors.selectById], (byId) =>
    Object.values(byId)
        .map((prototype) => byId[prototype.id])
        .sort(prototypesByName)
)

const selectDenormalizedPreviousSolutionsVideoCodePrototypes = createSelector(
    [solutionSelectors.selectPreviousSolutionsVideoCodePrototypeIds, videoCodePrototypeSelectors.selectById],
    (previousIds, byId) => previousIds.map((id) => byId[id]).sort(prototypesByName)
)

const selectAllPrototypesList = createSelector([selectDenormalizedPrototypes], (prototypes) => {
    return prototypes.reduce((allPrototypes: VideoCodePrototype[], prototype) => {
        if (prototype.parentId) {
            return allPrototypes
        }

        const childCodes = prototypes.filter((p) => p.parentId === prototype.id)

        return [...allPrototypes, { ...prototype, videoCodes: childCodes }]
    }, [])
})

const selectAllVideoCodePrototypesOfCurrentSolution = createSelector(
    [selectDenormalizedCurrentPrototypes],
    (prototypes) => {
        return prototypes.reduce((allPrototypes: VideoCodePrototype[], prototype) => {
            if (prototype.parentId) {
                return allPrototypes
            }

            const childCodes = prototypes.filter((p) => p.parentId === prototype.id)

            return [...allPrototypes, { ...prototype, videoCodes: childCodes }]
        }, [])
    }
)

const selectVideoCodePrototypesOfCurrentSolutionFlattened = createSelector(
    [selectAllVideoCodePrototypesOfCurrentSolution],
    (prototypes) => {
        return prototypes.reduce((allPrototypes: VideoCodePrototype[], prototype) => {
            return [...allPrototypes, prototype, ...prototype.videoCodes]
        }, [])
    }
)

const selectAllPrototypesFlattened = createSelector([selectAllPrototypesList], (prototypes) => {
    return prototypes.reduce((allFlattenedPrototypes: VideoCodePrototype[], prototype) => {
        return [...allFlattenedPrototypes, prototype, ...prototype.videoCodes]
    }, [])
})

const selectCurrentSolutionVideoCodePrototypesList = createSelector(
    [selectDenormalizedCurrentPrototypes],
    (prototypes) => {
        return prototypes.reduce((acc: VideoCodePrototype[], prototype) => {
            if (prototype.parentId) {
                return acc
            }

            const childCodes = prototypes.filter((c) => c.parentId === prototype.id)

            return [...acc, { ...prototype, videoCodes: childCodes }]
        }, [])
    }
)

const selectPreviousSolutionsVideoCodePrototypesList = createSelector(
    [selectDenormalizedPreviousSolutionsVideoCodePrototypes],
    (prototypes) => {
        return prototypes.reduce((acc: VideoCodePrototype[], prototype) => {
            // Only root prototypes
            if (prototype.parentId) {
                return acc
            }

            // No duplicates
            if (acc.find((p) => p.id === prototype.id)) {
                return acc
            }

            const childCodes = prototypes.filter((c) => c.parentId === prototype.id)

            return [...acc, { ...prototype, videoCodes: childCodes }]
        }, [])
    }
)

export const composedPrototypeSelectors = {
    selectCurrentSolutionVideoCodePrototypesList,
    selectPreviousSolutionsVideoCodePrototypesList,
    selectAllPrototypesList,
    selectAllPrototypesFlattened,
    selectDenormalizedCurrentPrototypes,
    selectVideoCodePrototypesOfCurrentSolutionFlattened,
}
