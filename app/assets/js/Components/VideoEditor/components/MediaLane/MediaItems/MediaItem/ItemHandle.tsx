import React from 'react'
import { HandleSide } from 'Components/VideoEditor/components/MediaLane/MediaItems/types'

type Props = {
    side: HandleSide
    width: number
    onMouseDown: (event: React.MouseEvent, side: HandleSide) => void
    onTouchStart: (event: React.TouchEvent, side: HandleSide) => void
    onClick: React.MouseEventHandler<HTMLDivElement>
}

const ItemHandle = (props: Props) => {
    const { side, width, onMouseDown, onTouchStart, onClick } = props

    const handleOnTouchStart = (event: React.TouchEvent) => {
        onTouchStart(event, side)
    }

    const handleOnMouseDown = (event: React.MouseEvent) => {
        onMouseDown(event, side)
    }

    return (
        <div
            className="video-editor__media-item__handle"
            style={{
                [side]: 0,
                width,
            }}
            onMouseDown={handleOnMouseDown}
            onTouchStart={handleOnTouchStart}
            onClick={onClick}
        >
            <i className={'fa fa-chevron-' + props.side}></i>
        </div>
    )
}

export default React.memo(ItemHandle)
