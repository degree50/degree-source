import { Video } from 'Components/VideoPlayer/VideoPlayerWrapper'
import React, { useCallback } from 'react'
import { useToggleVideoFavoriteMutation } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'

type Props = {
    video: Video
}

const VideoFavorite = (props: Props) => {
    const { video } = props
    const [sendToggleRequest] = useToggleVideoFavoriteMutation()

    const unfavorVideo = useCallback(
        (e) => {
            e.stopPropagation()
            sendToggleRequest(video.id)
        },
        [video, sendToggleRequest]
    )

    // NOTE: This is basically a copy of a tile from Video.html.twig
    // To make it easier to match the template with the component, I did not
    // split this further into sub-components on purpose!
    return (
        <div className="overview-item">
            <div className="overview-item__date">
                <span className="sr-only">Video erstellt am: </span> {video.createdAt}
            </div>
            <div className="overview-item__status">
                <span className="sr-only">Status des Videos: Video ist öffentlich</span>
                <i className="fas fa-eye" aria-hidden={true} title="Video ist öffentlich"></i>
            </div>
            <a id={`tile__title--${video.id}`} href={`video/play/${video.id}`} className="overview-item__title">
                <img height="50" alt="Vorschaubild des Videos" src={video.url.thumbnail} />
                <span className="sr-only">Name des Videos: </span> {video.name}
            </a>
            <div className="overview-item__actions">
                <button
                    tabIndex={0}
                    id={`favor-button--${video.id}`}
                    data-test-id={`remove-video-from-favorites--${video.id}`}
                    onClick={unfavorVideo}
                    className="button button--type-link overview-item__action-favourite"
                    title="Entfavorisieren"
                    type={'button'}
                >
                    <i className="fas fa-star"></i>
                </button>
                {video.userIsCreator ? (
                    <button className="button button--type-link" title="Aktionen aus/einblenden">
                        <i className="fas fa-ellipsis-v"></i>
                        <div className="overview-item__actions-dropdown">
                            <a href={`/video/play/${video.id}`}>
                                <i className="fas fa-play"></i> Video anschauen
                            </a>
                            <a href={`/video/edit/${video.id}`}>
                                <i className="fas fa-pencil-alt"></i> Video bearbeiten
                            </a>
                            <a href={`/video/delete/${video.id}`} className="color-alert">
                                <i className="fas fa-trash-alt"></i> Video löschen
                            </a>
                        </div>
                    </button>
                ) : null}
            </div>
        </div>
    )
}

export default React.memo(VideoFavorite)
