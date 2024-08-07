import Checkbox from 'Components/Checkbox'
import { useCourseQuery } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'
import { useDispatch, useSelector } from 'react-redux'
import { selectActiveCourseFilters, toggleFilter } from 'StimulusControllers/Schreibtisch/Store/CourseFilterSlice'

const CourseFilter = () => {
    const { data, isFetching, error } = useCourseQuery()

    const dispatch = useDispatch()
    const courses = Object.values(data ?? {})
    const activeFilters = useSelector(selectActiveCourseFilters)

    const createChangeHandler = (courseId: string) => () => {
        dispatch(toggleFilter(courseId))
    }

    if (error) {
        return (
            <div className="schreibtisch-filter">
                <h6 className="schreibtisch-filter__label">Kurs</h6>
                <p>Fehler!</p>
            </div>
        )
    }

    if (isFetching) {
        return (
            <div className="schreibtisch-filter">
                <h6 className="schreibtisch-filter__label">Kurs</h6>
                <i className="fas fa-spinner fa-spin"></i>
            </div>
        )
    }

    return (
        <div className="schreibtisch-filter">
            <h6 className="schreibtisch-filter__label">Kurs</h6>
            <ul className="list">
                {courses.map((course) => (
                    <li key={course.id}>
                        <Checkbox
                            isSelected={activeFilters.includes(course.id)}
                            onChange={createChangeHandler(course.id)}
                        >
                            {course.name}
                        </Checkbox>
                    </li>
                ))}
            </ul>
        </div>
    )
}

export default CourseFilter
