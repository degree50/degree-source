import { RefObject, useCallback, useEffect, useState } from 'react'
import VideoContext from 'videocontext'
import { t } from 'react-i18nify'
import { d2t, t2d } from 'duration-time-conversion'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'
import { Cut, CutList, MediaItem } from 'Components/VideoEditor/types'
import { notify } from 'Components/VideoEditor/utils'
import { useMediaItemHandling } from 'Components/VideoEditor/utils/useMediaItemHandling'

/**
 * Default volume value (100 is max)
 */
export const INITIAL_VOLUME = 100

export const useVolume = () => {
    const [volume, setVolume] = useState<number>(INITIAL_VOLUME)

    const handleVolumeChange = useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            setVolume(parseInt(event.currentTarget.value))
        },
        [setVolume]
    )

    return { volume, handleVolumeChange }
}

/**
 * Specialized media item handling for cuts.
 */
export const useCuttingMediaItemHandling = ({
    mediaItems,
    setCutList,
    updateCallback,
    updateCondition,
}: {
    mediaItems: Array<MediaItem<Cut>>
    originalVideoUrl?: string
    setCutList: (mediaItems: Array<Cut>) => void
    updateCallback: () => void
    updateCondition: boolean
}) => {
    const {
        currentIndex,

        setCurrentIndex,
        setCurrentTimeForMediaItems,
        updateMediaItems,
        hasMediaItem,
        copyMediaItems,
    } = useMediaItemHandling<Cut>({
        mediaItems,
        setMediaItems: setCutList,
        updateCallback,
        updateCondition,
    })

    /**
     * Handles the update of Cut media items and makes sure that their offset is handled correctly
     */
    const updateMediaItem = useCallback(
        (item: MediaItem<Cut>, { start, end, ...rest }: { start?: string; end?: string; rest?: any[] }) => {
            const index = hasMediaItem(item)

            if (index < 0) return

            const copiedItems = copyMediaItems()
            const { clone } = item

            const newStart = start || item.start
            const newEnd = end || item.end

            const duration = t2d(newEnd) - t2d(newStart)

            // Make sure that the clip will
            // a) always be at least on second long
            // b) always have an exact second count (e.g. not 1 second + 20 frames)
            //
            // WHY: The serverside rendering of the cutList will always be full seconds
            // and its result might otherwise deviate from the representation inside the frontend.
            const adjustedDuration = Math.round(duration) < 1 ? 1 : Math.round(duration)
            const adjustedEnd = d2t((t2d(newStart) + adjustedDuration).toFixed(3))

            const newValues = {
                ...rest,
                // NOTE: It's important that we DO NOT use the adjustedEnd value here, to correctly determine
                // what item handle was used to change the item.
                originalData: start
                    ? {
                          ...item.originalData,
                          offset: t2d(start),
                      }
                    : item.originalData,
                start: newStart,
                // Add a frame to our adjustedEnd if it does not differ from our input
                //
                // WHY:
                // This is a hack to ensure that the item changes at least a bit after rounding.
                // That way we can rely on components which receive these items as props to re-render.
                // This is necessary, because some of our components have some form of inner mutation/DOM manipulation. E.g. our useItemInteraction() hook directly interacts with the DOM.
                // When an item is changed the change is reflected immediately in the DOM (e.g. when the width of a node is written), but might actually get nullified by our rounding in here.
                // Because of the rounding our input item might then be exactly the same as before, which would lead to no update in our store and therefore no re-render of the components. (so we would have a state mismatch between our actual list of items and the rendered items)
                // It would probably be better to find a way to make item interaction etc. to be controlled components instead or work with some form of intermediate data structure which can be reset.
                end: adjustedEnd === item.end ? d2t((t2d(adjustedEnd) + 0.01).toFixed(3)) : adjustedEnd,
            }

            // eslint-disable-next-line
            Object.assign(clone, newValues)

            if (clone.check) {
                updateMediaItems([...copiedItems.slice(0, index), clone, ...copiedItems.slice(index + 1)], false)
            } else {
                notify(t('parameter-error'), 'error')
            }
        },
        [hasMediaItem, copyMediaItems, updateMediaItems]
    )

    // Run only once
    useEffect(() => {
        // Hydrate store on initial render
        updateMediaItems(mediaItems, true)
    }, [])

    return {
        currentIndex,

        setCurrentIndex,
        copyMediaItems,
        hasMediaItem,
        setCurrentTimeForMediaItems,
        updateMediaItem,
        updateMediaItems,
    }
}

export const determineDraggedHandle = (updatedValues: { start?: string; end?: string }): HandleSide => {
    if (updatedValues.start && !updatedValues.end) {
        return 'left'
    } else if (!updatedValues.start && updatedValues.end) {
        return 'right'
    } else {
        return 'center'
    }
}

/**
 * Adjusts the start and endtime of a given item and updates it in the context of its surrounding items.
 *
 * @return list of items with the adjusted item at the defined index
 */
export const adjustItemTimelinePositionInList = (
    items: MediaItem<any>[],
    item: MediaItem<any>,
    index: number,
    newStartTime: string
) => [
    ...items.slice(0, index),

    // eslint-disable-next-line
    Object.assign(item.clone, {
        ...item,
        start: newStartTime,
        end: d2t((t2d(newStartTime) + parseFloat(item.duration)).toFixed(3)),
    }),
    ...items.slice(index + 1),
]

/**
 * Initializes a VideoContext and binds it to a given canvas ref
 *
 * @returns { videoCtx, combineEffect } - returns the created videocontext as well as a combineEffect
 */
export const initVideoContext = (canvasRef: RefObject<HTMLCanvasElement>) => {
    const canvas = canvasRef.current

    const videoCtx = new VideoContext(canvas)
    const combineEffect = videoCtx.compositor(VideoContext.DEFINITIONS.COMBINE)

    // connect all sources
    combineEffect.connect(videoCtx.destination)

    let requestAnimationFrameId: number = -1 // eslint-disable-line
    // WHY: This is needed to keep the state updating
    // TODO: But Why and how is this working?
    function render() {
        requestAnimationFrameId = requestAnimationFrame(render)
    }

    requestAnimationFrameId = requestAnimationFrame(render)

    // WHY: prevent memory leak: remove animationFrame callback and thus stopping the render loop
    const cancelRenderLoop = () => {
        cancelAnimationFrame(requestAnimationFrameId)
    }

    return { videoCtx, combineEffect, cancelRenderLoop }
}

export type VideoContextPlayListElement = {
    url: string
    offset: number
    playbackRate: number
    start: number
    duration: number
}

const CUT_PADDING_SECS = 0.001

/**
 * Transforms a list of MediaItemType<Cut> into elements which are playable by the video context player.
 * All items are snapped back-to-back to their predecessor, so that there remains no empty space between cuts.
 */
export const transformCutListToVideoContextPlayList = (
    cuts: CutList,
    originalVideoUrl: string
): Array<VideoContextPlayListElement> =>
    cuts.reduce(
        (
            acc: {
                nextStart: number
                videoContextElements: Array<VideoContextPlayListElement>
            },
            cut
        ) => {
            const newElement: VideoContextPlayListElement = {
                url: originalVideoUrl,
                offset: cut.offset,
                playbackRate: cut.playbackRate,
                start: acc.nextStart,
                duration: t2d(cut.end) - t2d(cut.start),
            }

            return {
                ...acc,
                videoContextElements: [...acc.videoContextElements, newElement],
                // New nextStart for the next element is the starting position of the current element
                // added up with the duration of the current element -> which is basically the end of the current item
                nextStart: acc.nextStart + newElement.duration + CUT_PADDING_SECS,
            }
        },
        { videoContextElements: [], nextStart: 0 }
    ).videoContextElements

/**
 * Given a VideoContext and a Cut this directly adds a new videoNode to the context
 */
export const addVideoContextPlayListElement = (
    videoContextPlayListElement: VideoContextPlayListElement,
    videoCtx: VideoContext
) => {
    // We create the video node ourselves, because we need it asap, to retrieve
    // the aspect ratio of the first video node to determine the dimensions of
    // the canvas it is rendered to.
    const newVideoElement = document.createElement('video')
    newVideoElement.setAttribute('src', videoContextPlayListElement.url)
    newVideoElement.setAttribute('crossorigin', 'anonymous')
    newVideoElement.setAttribute('webkit-playsinline', '')
    newVideoElement.setAttribute('playsinline', '')
    newVideoElement.setAttribute('data-video', '')

    const videoNode = videoCtx.video(newVideoElement, videoContextPlayListElement.offset, 4, {
        // TODO: Why set volume here?
        volume: 0.6,
        loop: false,
    })

    // eslint-disable-next-line
    videoNode._playbackRate = videoContextPlayListElement.playbackRate
    videoNode.start(videoContextPlayListElement.start)
    videoNode.stop(videoContextPlayListElement.start + videoContextPlayListElement.duration)

    videoNode.connect(videoCtx.destination)

    return { videoNode, videoElement: newVideoElement }
}

export const getMediaItemFromCut = (cut: Cut) => {
    return new MediaItem({
        start: cut.start,
        end: cut.end,
        text: cut.text,
        memo: '',
        originalData: cut,
        lane: 0,
    })
}

/**
 * Transform CutList to MediaItems that represent the cuts in the VideoContext player.
 */
export const transformCutListToMediaItemsForCutContext = (cutList: CutList): Array<MediaItem<Cut>> =>
    cutList
        .reduce(
            (acc: { nextStart: number; cuts: CutList }, cut) => {
                const newNextStart = acc.nextStart + t2d(cut.end) - t2d(cut.start)
                const newCut: Cut = {
                    ...cut,
                    start: d2t(acc.nextStart.toFixed(3)),
                    end: d2t(newNextStart.toFixed(3)),
                }

                return {
                    ...acc,
                    cuts: [...acc.cuts, newCut],
                    nextStart: newNextStart + CUT_PADDING_SECS,
                }
            },
            { cuts: [], nextStart: 0 }
        )
        .cuts.map(getMediaItemFromCut)
