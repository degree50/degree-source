import React, { useRef } from 'react'
import { MediaItem as MediaItemClass, MediaItemType } from '../../../types'
import { RenderConfig } from '../MediaTrack'
import { useModalHook } from 'Components/Modal/useModalHook'
import Button from 'Components/Button/Button'
import MediaItemLabel from './MediaItem/MediaItemLabel'

type OwnProps = {
    item: MediaItemClass<MediaItemType>
    id: number | string
    renderConfig: RenderConfig
    isPlayedBack?: boolean
    amountOfLanes?: number
    showTextInMediaItems: boolean
}

type Props = OwnProps

const MediaItem = ({ item, id, renderConfig, isPlayedBack, amountOfLanes = 0, showTextInMediaItems = true }: Props) => {
    const itemRef = useRef<HTMLDivElement>(null)

    const mediaItemHeight = 100 / (amountOfLanes + 1)

    const positionLeft =
        renderConfig.padding * renderConfig.gridGap +
        (item.startTime - renderConfig.timelineStartTime) * renderConfig.gridGap * 10

    const { showModal: showMemoModal, RenderModal: RenderMemoModal } = useModalHook()

    const width = (item.endTime - item.startTime) * renderConfig.gridGap * 10

    return (
        <div
            ref={itemRef}
            className={[
                'video-editor__media-items__item',
                'video-editor__media-items__item--read-only',
                isPlayedBack ? 'video-editor__media-items__item--highlight' : '',
            ]
                .join(' ')
                .trim()}
            key={id}
            title={item.text}
            style={{
                backgroundColor: item.color ? item.color : '',
                left: positionLeft,
                width,
                top: item.lane * mediaItemHeight + '%',
                height: mediaItemHeight + '%',
            }}
        >
            {item.memo ? (
                <Button onPress={showMemoModal} className={'video-editor__media-items__memo-toggle'}>
                    <i className={'fas fa-info'} />
                </Button>
            ) : null}

            <div className="video-editor__media-items__text">
                <MediaItemLabel item={item} showTextInMediaItems={showTextInMediaItems} />
            </div>

            <RenderMemoModal title={'Memo'}>{item.memo}</RenderMemoModal>
        </div>
    )
}

export default React.memo(MediaItem)
