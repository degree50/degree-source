import { useEffect } from 'react'
import { useLocation } from 'react-router'
import { ROUTE_AUFGABEN } from '../Schreibtisch'
import ExerciseList from './ExerciseList/ExerciseList'

const MyExercises = () => {
    const { pathname } = useLocation()

    useEffect(() => {
        if (pathname.includes(ROUTE_AUFGABEN)) {
            // eslint-disable-next-line functional/immutable-data
            document.title = 'Schreibtisch - Aufgaben'
        }
    }, [pathname])

    return (
        <>
            <header className={'content-header'}>
                <h3>Meine Aufgaben</h3>
            </header>
            <ExerciseList />
        </>
    )
}

export default MyExercises
