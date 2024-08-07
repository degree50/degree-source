import { createSelector } from '@reduxjs/toolkit'
import { selectors as videoCodeSelectors } from 'Components/ToolbarItems/VideoCodesContext/VideoCodesSlice'
import { selectors as solutionSelectors } from '../SolutionSlice'
import { sortByStartTime } from '../utils/time'
import { MediaItemTypeEnum, VideoCode, VideoCodePrototype } from '../types'
import { getColorName } from 'ntc-ts'

/**
 * Denormalized video codes to be used when sending videoCodes to the backend server
 */
const selectDenormalizedCurrentVideoCodes = createSelector(
    [solutionSelectors.selectCurrentVideoCodeIds, videoCodeSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => byId[id])
)

/**
 * Denormalized videoCodes which also contain a type property, to easier identify them as media item
 * of type [MediaItemTypeEnum.videoCode] for further processing
 */
const selectCurrentVideoCodes = createSelector(
    [solutionSelectors.selectCurrentVideoCodeIds, videoCodeSelectors.selectById],
    (currentIds, byId) => currentIds.map((id) => ({ ...byId[id], type: MediaItemTypeEnum.videoCode }))
)

const selectCurrentVideoCodesByStartTime = createSelector([selectCurrentVideoCodes], sortByStartTime)

const selectCurrentVideoCodeIdsSortedByStartTime = createSelector(
    [selectCurrentVideoCodesByStartTime],
    (videoCodesByStartTime) => videoCodesByStartTime.map((videoCode) => videoCode.id)
)

const selectVideoCodeIsFromCurrentSolution = createSelector(
    [solutionSelectors.selectCurrentId, videoCodeSelectors.selectVideoCodeById],
    (currentSolutionId, videoCode) => currentSolutionId && videoCode && currentSolutionId === videoCode.solutionId
)

const selectCreatorNameForVideoCode = createSelector(
    [solutionSelectors.selectById, videoCodeSelectors.selectVideoCodeById],
    (solutionsById, videoCode) => {
        const solution = videoCode.solutionId ? solutionsById[videoCode.solutionId] : undefined

        return solution?.userName ?? '<Unbekannter Ersteller>'
    }
)

export const composedVideoCodeSelectors = {
    selectDenormalizedCurrentVideoCodes,
    selectCurrentVideoCodesByStartTime,
    selectCurrentVideoCodeIdsSortedByStartTime,
    selectCurrentVideoCodes,
    selectVideoCodeIsFromCurrentSolution,
    selectCreatorNameForVideoCode,
}

export const videoCodeAsRichtext = ({
    videoCode,
    videoCodePrototype,
    parentVideoCodePrototype,
    creatorName,
    isFromPreviousSolution,
}: {
    videoCode: VideoCode
    videoCodePrototype?: VideoCodePrototype
    parentVideoCodePrototype?: VideoCodePrototype
    creatorName: string
    isFromPreviousSolution: boolean
}) => {
    const code = `Code: ${videoCodePrototype?.name ?? 'Kein Code ausgewählt'}`
    const color = `Farbe: ${videoCodePrototype?.color ? getColorName(videoCodePrototype.color).name : ''}`
    const userCreated = `${videoCodePrototype?.userCreated ? 'Selbsterstellter Code' : 'Vordefinierter Code'}`
    const subCode = `${parentVideoCodePrototype ? `Unter-Code von ${parentVideoCodePrototype.name}` : ''}`
    const creatorDescription = `Codierung ${isFromPreviousSolution ? 'aus Lösung' : ''} von: ${creatorName}`
    const start = `Von: ${videoCode.start}`
    const end = `Bis: ${videoCode.end}`
    const memo = `${videoCode.memo.length > 0 ? `Memo: ${videoCode.memo}` : ''}`

    return (
        [code, color, userCreated, ...(subCode.length > 0 ? [subCode] : []), creatorDescription, start, end, memo]
            // Filter out empty lines
            .filter((line) => line === '')
            .join('\n')
    )
}
