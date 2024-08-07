import React, { useEffect } from 'react'
import { useLocation } from 'react-router'
import { ROUTE_VIDEO_FAVORITEN } from '../Schreibtisch'
import VideoFavorites from './VideoFavorites/VideoFavorites'

const MyVideoFavorites = () => {
    const { pathname } = useLocation()

    useEffect(() => {
        if (pathname.includes(ROUTE_VIDEO_FAVORITEN)) {
            // eslint-disable-next-line functional/immutable-data
            document.title = 'Schreibtisch - Videofavoriten'
        }
    }, [pathname])

    return (
        <>
            <VideoFavorites />
        </>
    )
}

export default React.memo(MyVideoFavorites)
