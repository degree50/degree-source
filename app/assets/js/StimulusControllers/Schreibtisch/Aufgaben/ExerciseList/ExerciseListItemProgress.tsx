import { memo } from 'react'
import Meter from 'Components/Meter/Meter'

type Props = {
    phaseCount: number
    completedPhases: number
}

const ExerciseListItemProgress = (props: Props) => {
    const { phaseCount, completedPhases } = props

    return (
        <Meter
            value={completedPhases}
            maxValue={phaseCount}
            showValueLabel={true}
            valueLabel={`${completedPhases} von ${phaseCount}`}
            aria-label={`${completedPhases} von ${phaseCount} Phasen abgeschlossen.`}
        />
    )
}

export default memo(ExerciseListItemProgress)
