import { createSelector } from '@reduxjs/toolkit'
import { selectors as cutsSelectors } from 'Components/ToolbarItems/CuttingContext/CuttingSlice'
import { selectors as solutionSelectors } from '../SolutionSlice'
import { Cut, MediaItemTypeEnum } from '../types'
import { sortByStartTime } from '../utils/time'

/**
 * Denormalized cuts to be used when sending cuts to the backend server
 */
const selectDenormalizedCurrentCutList = createSelector(
    [solutionSelectors.selectCurrentCutIds, cutsSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => byId[id])
)

/**
 * Denormalized cut which cuts contain a type property, to easier identify them as media item
 * of type [MediaItemTypeEnum.cut] for further processing
 */
const selectCurrentCuts = createSelector(
    [solutionSelectors.selectCurrentCutIds, cutsSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => ({ ...byId[id], type: MediaItemTypeEnum.cut }))
)

const selectCurrentCutListByStartTime = createSelector([selectCurrentCuts], sortByStartTime)

const selectCurrentCutIdsByStartTime = createSelector([selectCurrentCutListByStartTime], (cutList) =>
    cutList.map((cut) => cut.id)
)

const selectCutIsFromCurrentSolution = createSelector(
    [solutionSelectors.selectCurrentId, cutsSelectors.selectCutById],
    (currentSolutionId, cut) => currentSolutionId && cut && currentSolutionId === cut.solutionId
)

const selectCreatorNameForCut = createSelector(
    [solutionSelectors.selectById, cutsSelectors.selectCutById],
    (solutionsById, cut) => {
        const solution = cut.solutionId ? solutionsById[cut.solutionId] : undefined

        return solution?.userName ?? '<Unbekannter Ersteller>'
    }
)

export const composedCutSelectors = {
    selectDenormalizedCurrentCutList,
    selectCurrentCutListByStartTime,
    selectCurrentCutIdsByStartTime,
    selectCutIsFromCurrentSolution,
    selectCurrentCuts,
    selectCreatorNameForCut,
}

export const cutAsRichtext = ({
    cut,
    creatorName,
    isFromPreviousSolution,
}: {
    cut: Cut
    creatorName: string
    isFromPreviousSolution: boolean
}) => {
    const creatorDescription = `Schnitt ${isFromPreviousSolution ? 'aus Lösung' : ''} von: ${creatorName}`
    const description = `Beschreibung: ${cut.text}`
    const start = `Von: ${cut.start}`
    const end = `Bis: ${cut.end}`
    const memo = `${cut.memo.length > 0 ? `Memo: ${cut.memo}` : ''}`

    return [description, creatorDescription, start, end, memo].join('\n')
}
