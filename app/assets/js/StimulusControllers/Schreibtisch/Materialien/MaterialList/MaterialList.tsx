import React from 'react'
import { useMaterialQuery } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'
import MaterialTile from './MaterialTile'
import { Course, Fachbereich, Material } from 'StimulusControllers/Schreibtisch/types'
import { useSelector } from 'react-redux'
import { selectActiveCourseFilters } from 'StimulusControllers/Schreibtisch/Store/CourseFilterSlice'
import { selectActiveFachbereichFilters } from 'StimulusControllers/Schreibtisch/Store/FachbereichFilterSlice'

const filterMaterialByFachbereichAndCourseFilters = (
    materials: Array<Material>,
    fachbereichFilters: Array<Fachbereich['id']>,
    courseFilters: Array<Course['id']>
) => {
    const filteredByFachbereich =
        (fachbereichFilters.length > 0
            ? materials.filter((material) => {
                  if (material.fachbereich) {
                      return fachbereichFilters.includes(material.fachbereich.id)
                  }
                  return false
              })
            : materials) ?? []

    const filteredByCourse =
        courseFilters.length > 0
            ? filteredByFachbereich.filter((material) => {
                  if (material.course) {
                      return courseFilters.includes(material.course.id)
                  }
                  return false
              })
            : filteredByFachbereich

    return filteredByCourse
}

const MaterialList = () => {
    const { data, isFetching, error } = useMaterialQuery()
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

    if (data.length === 0) {
        return <p>Keine Materialien vorhanden</p>
    }

    const filteredMaterials = filterMaterialByFachbereichAndCourseFilters(
        data,
        activeFachbereichFilters,
        activeCourseFilters
    )

    return (
        <>
            <header className={'content-header'}>
                <h3>Meine Materialien</h3>
            </header>
            <ul data-test-id="material" className="overview material">
                {filteredMaterials.map((material) => (
                    <li key={material.id}>
                        <MaterialTile material={material} />
                    </li>
                ))}
            </ul>
        </>
    )
}

export default React.memo(MaterialList)
