import FachbereichFilter from 'StimulusControllers/Schreibtisch/Filter/FachbereichFilter'
import CourseFilter from 'StimulusControllers/Schreibtisch/Filter/CourseFilter'

const Filter = () => {
    return (
        <section className="schreibtisch-filters">
            <header>
                <i className="fas fa-filter" />
                <span>Filter</span>
            </header>
            <main>
                <FachbereichFilter />
                <CourseFilter />
            </main>
        </section>
    )
}

export default Filter
