import { memo } from 'react'
import ExerciseListItemProgress from 'StimulusControllers/Schreibtisch/Aufgaben/ExerciseList/ExerciseListItemProgress'
import { Exercise } from 'StimulusControllers/Schreibtisch/types'

type Props = {
    exercise: Exercise
}

const prettyConvertPhaseStatus = (backendStatus: Exercise['status']) => {
    switch (backendStatus) {
        case 'IN_BEARBEITUNG': {
            return 'In Bearbeitung'
        }

        case 'IN_REVIEW': {
            return 'Wird geprüft'
        }

        case 'NEU': {
            return 'Neu'
        }

        case 'BEENDET': {
            return 'Beendet'
        }
    }
}

const ExerciseListItem = (props: Props) => {
    const { exercise } = props

    // see route "exercise__show" in ExerciseController.php
    const url = `/exercise/${exercise.id}/show`
    // TODO
    const ariaLabel = undefined

    return (
        <tr aria-label={ariaLabel}>
            <td>{exercise.fachbereich?.name ?? '-'}</td>
            <td>{exercise.course.name}</td>
            <td>
                <a href={url}>{exercise.name}</a>
            </td>
            <td>
                <ExerciseListItemProgress completedPhases={exercise.completedPhases} phaseCount={exercise.phaseCount} />
            </td>
            <td className="exercise-status">{prettyConvertPhaseStatus(exercise.status)}</td>
        </tr>
    )
}

export default memo(ExerciseListItem)
