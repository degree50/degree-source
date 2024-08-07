// FIXME refactor to simplify (and implement 'todo'-tests):
// * determine if either out of right bound, left bound or not out of bound at all
// * --> extract functions for left/right bound adjustment
export const calculateTimelineStartTime = (currentTime: number, newRenderedDuration: number, videoDuration: number) => {
    // we zoom around the current time (cursor)
    const newRenderStartTime = currentTime - newRenderedDuration / 2
    const newRenderEndTime = newRenderStartTime + newRenderedDuration

    const outOfBoundsDifference = newRenderEndTime - videoDuration
    const startTimeWithOutOfBoundsDifference = newRenderStartTime - outOfBoundsDifference
    const startTimeWithRightBoundAdjustment = Math.min(startTimeWithOutOfBoundsDifference, newRenderStartTime)

    // WHY:
    // if startTimeWithRightBoundAdjustment is lower than zero, we are out of the left bound and
    // set our time to 0.
    return Math.max(0, startTimeWithRightBoundAdjustment)
}
