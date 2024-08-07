import { createSelector } from '@reduxjs/toolkit'
import { selectors as annotationSelectors } from 'Components/ToolbarItems/AnnotationsContext/AnnotationsSlice'
import { selectors as solutionSelectors } from '../SolutionSlice'
import { Annotation, MediaItemTypeEnum } from '../types'
import { sortByStartTime } from '../utils/time'

/**
 * Denormalized annotations to be used when sending annotations to the backend server
 */
const selectDenormalizedCurrentAnnotations = createSelector(
    [solutionSelectors.selectCurrentAnnotationIds, annotationSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => byId[id])
)

/**
 * Denormalized annotations which also contain a type property, to easier identify them as media item
 * of type [MediaItemTypeEnum.Annotation] for further processing
 */
const selectCurrentAnnotations = createSelector(
    [solutionSelectors.selectCurrentAnnotationIds, annotationSelectors.selectById],
    (currentIds, byId) =>
        currentIds.map((id) => ({
            ...byId[id],
            type: MediaItemTypeEnum.annotation,
        }))
)

const selectCurrentAnnotationsByStartTime = createSelector([selectCurrentAnnotations], sortByStartTime)

const selectCurrentAnnotationIdsSortedByStartTime = createSelector(
    [selectCurrentAnnotationsByStartTime],
    (annotationsByStartTime) => annotationsByStartTime.map((annotation) => annotation.id)
)

const selectAnnotationIsFromCurrentSolution = createSelector(
    [solutionSelectors.selectCurrentId, annotationSelectors.selectAnnotationById],
    (currentSolutionId, annotation) => currentSolutionId && annotation && currentSolutionId === annotation.solutionId
)

const selectCreatorNameForAnnotation = createSelector(
    [solutionSelectors.selectById, annotationSelectors.selectAnnotationById],
    (solutionsById, annotation) => {
        const solution = annotation.solutionId ? solutionsById[annotation.solutionId] : undefined

        return solution?.userName ?? '<Unbekannter Ersteller>'
    }
)

export const composedAnnotationSelectors = {
    selectDenormalizedCurrentAnnotations,
    selectCurrentAnnotationsByStartTime,
    selectCurrentAnnotationIdsSortedByStartTime,
    selectCurrentAnnotations,
    selectAnnotationIsFromCurrentSolution,
    selectCreatorNameForAnnotation,
}

export const annotationWithCreatorNameAsRichtext = (
    annotation: Annotation & { creatorName: string; isFromPreviousSolution: boolean }
) => {
    const description = `Beschreibung: ${annotation.text}`
    const creatorDescription = `Annotation ${annotation.isFromPreviousSolution ? 'aus Lösung' : ''} von: ${
        annotation.creatorName
    }`
    const start = `Von: ${annotation.start}`
    const end = `Bis: ${annotation.end}`
    const memo = `${annotation.memo.length > 0 ? `Memo: ${annotation.memo}` : ''}`

    return [description, creatorDescription, start, end, memo].join('\n')
}
