import { memo } from 'react'
import { useExercisesQuery } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'
import ExerciseListItem from 'StimulusControllers/Schreibtisch/Aufgaben/ExerciseList/ExerciseListItem'
import { selectActiveCourseFilters } from 'StimulusControllers/Schreibtisch/Store/CourseFilterSlice'
import { useSelector } from 'react-redux'
import { selectActiveFachbereichFilters } from 'StimulusControllers/Schreibtisch/Store/FachbereichFilterSlice'
import { Course, Exercise, Fachbereich } from 'StimulusControllers/Schreibtisch/types'

// TODO: how to do as selector with RTK Query
const filterExercisesWithFachbereichAndCourseFilters = (
    exercises: Array<Exercise>,
    fachbereichFilters: Array<Fachbereich['id']>,
    courseFilters: Array<Course['id']>
) => {
    const filteredByFachbereich =
        (fachbereichFilters.length > 0
            ? exercises.filter((exercise) => {
                  if (exercise.fachbereich) {
                      return fachbereichFilters.includes(exercise.fachbereich.id)
                  }
                  return false
              })
            : exercises) ?? []

    const filteredByCourse =
        courseFilters.length > 0
            ? filteredByFachbereich.filter((exercise) => courseFilters.includes(exercise.course.id))
            : filteredByFachbereich

    return filteredByCourse
}

const ExerciseList = () => {
    const { data, isFetching, error } = useExercisesQuery()
    const activeCourseFilters = useSelector(selectActiveCourseFilters)
    const activeFachbereichFilters = useSelector(selectActiveFachbereichFilters)

    if (isFetching) {
        return (
            <div className="loading-screen">
                <i className="fas fa-spinner fa-spin"></i>
            </div>
        )
    }

    if (error || data === undefined) {
        console.error(error)
        return <p>Fehler!</p>
    }

    const filteredExercises = filterExercisesWithFachbereichAndCourseFilters(
        data,
        activeFachbereichFilters,
        activeCourseFilters
    )

    // TODO: a11y
    return (
        <table className="exercise-list" data-test-id="exercise-list">
            <thead>
                <tr>
                    <th>Fachbereich</th>
                    <th>Kurs</th>
                    <th>Aufgabe</th>
                    <th>Phasen</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                {filteredExercises.map((exercise) => (
                    <ExerciseListItem key={exercise.id} exercise={exercise} />
                ))}
            </tbody>
        </table>
    )
}

export default memo(ExerciseList)
