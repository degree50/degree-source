import { HashRouter, Routes, Route, Navigate } from 'react-router-dom'
import MyExercises from 'StimulusControllers/Schreibtisch/Aufgaben/MyExercises'
import MaterialEditor from './MaterialEditor/MaterialEditor'
import MyMaterials from './Materialien/MyMaterials'
import Navigation from './Navigation/Navigation'
import MyVideoFavorites from './VideoFavoriten/MyVideoFavorites'

export const ROUTE_AUFGABEN = '/aufgaben'
export const ROUTE_VIDEO_FAVORITEN = '/video-favoriten'
export const ROUTE_MATERIALIEN = '/materialien'
export const ROUTE_EDIT_MATERIAL = `${ROUTE_MATERIALIEN}/edit`

const Schreibtisch = () => {
    return (
        <div className="schreibtisch">
            <HashRouter>
                <Navigation />
                <main>
                    <Routes>
                        <Route index element={<Navigate to={ROUTE_AUFGABEN} replace={true} />} />

                        <Route path={ROUTE_AUFGABEN} element={<MyExercises />} />

                        <Route path={ROUTE_VIDEO_FAVORITEN} element={<MyVideoFavorites />} />

                        <Route path={ROUTE_MATERIALIEN} element={<MyMaterials />} />

                        <Route path={`${ROUTE_EDIT_MATERIAL}/:id`} element={<MaterialEditor />} />
                    </Routes>
                </main>
            </HashRouter>
        </div>
    )
}

export default Schreibtisch
