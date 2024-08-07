import React, { useRef, useState, useEffect, useMemo, useCallback } from 'react'
import videojs, { VideoJsPlayerOptions, VideoJsPlayer } from 'video.js'
import videojsDE from 'video.js/dist/lang/de.json'
import 'video.js/dist/video-js.css'

import { Video } from './VideoPlayerWrapper'
import { generate } from 'shortid'
import { useAddCustomVideoJsComponent } from '../VideoEditor/useCustomVideoJsComponent'
import { usePatchVideoJsToMakeHotKeysWork } from '../VideoEditor/usePatchVideoJsToMakeHotKeysWork'
import { SetPlayerTimeControl } from 'Components/ToolbarItems/SetVideoPlayerTimeContext/SetVideoPlayerTimeMenu'
import classNames from 'classnames'

type Props = {
    videoJsOptions: VideoJsPlayerOptions
    videoMap?: Video
    worker?: Worker
    updateTimeCallback?: (currentTime: number) => void
    setPauseCallback?: (pause: boolean) => void
    isPaused?: boolean
    playPosition?: number
    hidden?: boolean
}

const defaultVideoJsOptions: VideoJsPlayerOptions = {
    fluid: true,
    language: 'de',
}

function getVideoSources(video?: Video): VideoJsPlayerOptions['sources'] {
    if (!video) {
        return []
    }

    if (video.url.hls) {
        return [
            {
                src: video.url.hls,
                type: 'application/x-mpegURL',
            },
        ]
    } else if (video.url.mp4) {
        return [
            {
                src: video.url.mp4,
                type: 'video/mp4',
            },
        ]
    } else {
        return []
    }
}

const VideoJSPlayer: React.FC<Props> = (props) => {
    const [player, setPlayer] = useState<VideoJsPlayer | undefined>(undefined)
    const videoRef: React.RefObject<HTMLVideoElement> = useRef(null)
    const [vttPath, setVttPath] = useState(props?.videoMap?.url?.vtt)

    // WHY: We create a UID to support multiple players on a single page
    const playerId = useMemo(() => generate(), [])

    useEffect(() => {
        if (videoRef.current !== null) {
            videojs.addLanguage('de', videojsDE)
            setPlayer(
                videojs(videoRef.current, {
                    ...defaultVideoJsOptions,
                    ...props.videoJsOptions,
                    sources: getVideoSources(props.videoMap),
                })
            )
        }

        return () => {
            player?.dispose()
        }
    }, [])

    usePatchVideoJsToMakeHotKeysWork(player)

    useAddCustomVideoJsComponent(SetPlayerTimeControl, player)

    useEffect(() => {
        if (player && props.worker && !props.worker.onmessage) {
            // eslint-disable-next-line
            props.worker.onmessage = (event) => {
                setVttPath(event.data)
            }
        }
    }, [player, props.worker])

    useEffect(() => {
        if (player && props.isPaused !== undefined) {
            if (props.isPaused) {
                player.pause()
            } else {
                player.play()
            }
        }
    }, [player, props.isPaused])

    useEffect(() => {
        if (player && props.playPosition !== undefined) {
            player.currentTime(props.playPosition)
        }
    }, [player, props.playPosition])

    const updateTime = (event: React.SyntheticEvent<HTMLVideoElement, Event>) => {
        if (props.updateTimeCallback) {
            props.updateTimeCallback(event.currentTarget.currentTime)
        }
    }

    const handlePause = useCallback(() => {
        if (props.setPauseCallback && !props.isPaused) {
            props.setPauseCallback(true)
        }
    }, [props])

    const handlePlay = useCallback(() => {
        if (props.setPauseCallback && props.isPaused) {
            props.setPauseCallback(false)
        }
    }, [props])

    const className = classNames('video-player', { 'video-player--hidden': props.hidden })

    return (
        <div className={className}>
            <div data-vjs-player>
                <video
                    id={playerId}
                    ref={videoRef}
                    onTimeUpdate={updateTime}
                    onPause={handlePause}
                    onPlay={handlePlay}
                    className="video-js"
                >
                    {vttPath && <track kind="captions" src={vttPath} label="Standard" default />}
                </video>
            </div>
        </div>
    )
}

export default React.memo(VideoJSPlayer)
