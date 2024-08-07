import React, { useEffect } from 'react'
import { useLocation } from 'react-router-dom'
import { ROUTE_MATERIALIEN } from '../Schreibtisch'
import MaterialList from './MaterialList/MaterialList'

const MyMaterials = () => {
    const { pathname } = useLocation()

    useEffect(() => {
        if (pathname.includes(ROUTE_MATERIALIEN)) {
            // eslint-disable-next-line functional/immutable-data
            document.title = 'Schreibtisch - Materialien'
        }
    }, [pathname])

    return (
        <>
            <MaterialList />
        </>
    )
}

export default React.memo(MyMaterials)
