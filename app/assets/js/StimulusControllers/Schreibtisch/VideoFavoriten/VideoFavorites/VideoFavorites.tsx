import React from 'react'
import { useVideoFavoritesQuery } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'
import VideoFavourite from './VideoFavorite'
import { Course, Fachbereich, VideoFavorite } from 'StimulusControllers/Schreibtisch/types'
import { useSelector } from 'react-redux'
import { selectActiveCourseFilters } from 'StimulusControllers/Schreibtisch/Store/CourseFilterSlice'
import { selectActiveFachbereichFilters } from 'StimulusControllers/Schreibtisch/Store/FachbereichFilterSlice'

const filterVideoFavoritesByFachbereichAndCourseFilters = (
    videoFavorites: Array<VideoFavorite>,
    fachbereichFilters: Array<Fachbereich['id']>,
    courseFilters: Array<Course['id']>
) => {
    const filteredByFachbereich =
        (fachbereichFilters.length > 0
            ? videoFavorites.filter((videoFavorite) =>
                  videoFavorite.video.fachbereiche.some((fachbereich) => fachbereichFilters.includes(fachbereich.id))
              )
            : videoFavorites) ?? []

    const filteredByCourse =
        courseFilters.length > 0
            ? filteredByFachbereich.filter((videoFavorite) =>
                  videoFavorite.video.courses.some((course) => courseFilters.includes(course.id))
              )
            : filteredByFachbereich

    return filteredByCourse
}

const VideoFavorites = () => {
    const { data, isFetching, error } = useVideoFavoritesQuery()
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
        return (
            <>
                <header className={'content-header'}>
                    <h3>Meine Videofavoriten</h3>
                </header>
                <p className="video-favorites">Keine Videos favorisiert</p>
            </>
        )
    }

    const filteredVideos = filterVideoFavoritesByFachbereichAndCourseFilters(
        data,
        activeFachbereichFilters,
        activeCourseFilters
    )

    return (
        <>
            <header className={'content-header'}>
                <h3>Meine Videofavoriten</h3>
            </header>
            <ul data-test-id="video-favorites" className="overview">
                {filteredVideos.map((videoFavorite) => (
                    <li key={videoFavorite.id}>
                        <VideoFavourite video={videoFavorite.video} />
                    </li>
                ))}
            </ul>
        </>
    )
}

export default React.memo(VideoFavorites)
