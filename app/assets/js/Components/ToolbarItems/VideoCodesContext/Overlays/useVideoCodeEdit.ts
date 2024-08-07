import { VideoCode } from 'Components/VideoEditor/types'
import { clamp } from 'Components/VideoEditor/utils'
import { adjustEndTimeByStart, secondToTime, timeToSecond } from 'Components/VideoEditor/utils/time'
import { ChangeEvent, useState } from 'react'

export const useVideoCodeEdit = (duration: number, initialVideoCode?: VideoCode) => {
    const [transientVideoCode, setTransientVideoCode] = useState<VideoCode | undefined>(initialVideoCode)

    const handleStartTimeChange = (time: string) => {
        if (transientVideoCode) {
            setTransientVideoCode({
                ...transientVideoCode,
                start: secondToTime(clamp(timeToSecond(time), 0, duration)),
                end: adjustEndTimeByStart(time, transientVideoCode.end, duration),
            })
        }
    }

    const handleEndTimeChange = (time: string) => {
        if (transientVideoCode) {
            setTransientVideoCode({
                ...transientVideoCode,
                end: secondToTime(clamp(timeToSecond(time), timeToSecond(transientVideoCode.start), duration)),
            })
        }
    }

    const handleTextChange = (ev: ChangeEvent<HTMLTextAreaElement>) => {
        if (transientVideoCode) {
            setTransientVideoCode({
                ...transientVideoCode,
                text: ev.target.value,
            })
        }
    }

    const handleMemoChange = (memo: string) => {
        if (transientVideoCode) {
            setTransientVideoCode({
                ...transientVideoCode,
                memo: memo,
            })
        }
    }

    const updateSelectedCode = (prototypeId: string) => {
        if (transientVideoCode) {
            setTransientVideoCode({
                ...transientVideoCode,
                idFromPrototype: prototypeId,
            })
        }
    }

    const minAllowedStartTime = '00:00:00.000'
    const maxAllowedStartTime = secondToTime(duration - 1)
    const minAllowedEndTime = secondToTime(
        Math.min(timeToSecond(transientVideoCode?.start ?? minAllowedStartTime) + 1, duration)
    )
    const maxAllowedEndTime = secondToTime(duration)

    return {
        transientVideoCode,
        handleStartTimeChange,
        handleEndTimeChange,
        handleTextChange,
        handleMemoChange,
        updateSelectedCode,
        minAllowedStartTime,
        maxAllowedStartTime,
        minAllowedEndTime,
        maxAllowedEndTime,
    }
}
