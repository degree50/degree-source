import { Annotation } from 'Components/VideoEditor/types'
import { clamp } from 'Components/VideoEditor/utils'
import { adjustEndTimeByStart, secondToTime, timeToSecond } from 'Components/VideoEditor/utils/time'
import { useState } from 'react'

export const useAnnotationEdit = (duration: number, initialAnnotation?: Annotation) => {
    const [transientAnnotation, setTransientAnnotation] = useState<Annotation | undefined>(initialAnnotation)

    const handleStartTimeChange = (time: string) => {
        if (transientAnnotation) {
            setTransientAnnotation({
                ...transientAnnotation,
                // TODO: should we clamp to 'duration - 1 second' maximum?
                start: secondToTime(clamp(timeToSecond(time), 0, duration)),
                end: adjustEndTimeByStart(time, transientAnnotation.end, duration),
            })
        }
    }

    const handleEndTimeChange = (time: string) => {
        if (transientAnnotation) {
            setTransientAnnotation({
                ...transientAnnotation,
                end: secondToTime(clamp(timeToSecond(time), timeToSecond(transientAnnotation.start), duration)),
            })
        }
    }

    const updateText = (text: string) => {
        if (transientAnnotation) {
            setTransientAnnotation({
                ...transientAnnotation,
                text,
            })
        }
    }

    const updateMemo = (memo: string) => {
        if (transientAnnotation) {
            setTransientAnnotation({
                ...transientAnnotation,
                memo,
            })
        }
    }

    const minAllowedStartTime = '00:00:00.000'
    const maxAllowedStartTime = secondToTime(duration - 1)
    const minAllowedEndTime = secondToTime(
        Math.min(timeToSecond(transientAnnotation?.start ?? minAllowedStartTime) + 1, duration)
    )
    const maxAllowedEndTime = secondToTime(duration)

    return {
        transientAnnotation,
        handleStartTimeChange,
        handleEndTimeChange,
        updateText,
        updateMemo,
        minAllowedStartTime,
        maxAllowedStartTime,
        minAllowedEndTime,
        maxAllowedEndTime,
    }
}
