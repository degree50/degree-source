import React, { FC, memo } from 'react'
import MultiLane from './MultiLane'

type OwnProps = {
    showMediaLane: boolean
    isFullHeight: boolean
    toggleFullHeight: () => void
}

const MediaLaneContainer: FC<OwnProps> = (props) => {
    if (props.showMediaLane) {
        return (
            <div className="media-lane-container">
                <MultiLane isFullHeight={props.isFullHeight} toggleFullHeight={props.toggleFullHeight} />
            </div>
        )
    }

    return null
}

export default memo(MediaLaneContainer)
