import { MediaItemTypeWithTypeInformation, MediaItem as MediaItemClass } from 'Components/VideoEditor/types'
import React from 'react'
import MediaItemLabel from './MediaItemLabel'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'

type Props = {
    onMouseDown: (event: React.MouseEvent, side: HandleSide) => void
    onTouchStart: (event: React.TouchEvent, side: HandleSide) => void
    item: MediaItemClass<MediaItemTypeWithTypeInformation>
    showTextInMediaItems: boolean
}

const Body = (props: Props) => {
    const { onMouseDown, onTouchStart, item, showTextInMediaItems } = props

    const handleOnTouchStart = (event: React.TouchEvent) => {
        onTouchStart(event, 'center')
    }

    const handleOnMouseDown = (event: React.MouseEvent) => {
        onMouseDown(event, 'center')
    }

    return (
        <div
            className="video-editor__media-items__text"
            onTouchStart={handleOnTouchStart}
            onMouseDown={handleOnMouseDown}
        >
            <MediaItemLabel item={item} showTextInMediaItems={showTextInMediaItems} />
        </div>
    )
}

export default React.memo(Body)
