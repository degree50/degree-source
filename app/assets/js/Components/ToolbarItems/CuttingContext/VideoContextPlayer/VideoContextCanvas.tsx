import React, { FC, memo, RefObject } from 'react'

type Props = {
    $canvasRef: RefObject<HTMLCanvasElement>
    canvasWidth: number
    canvasHeight: number
}

const VideoContextCanvas: FC<Props> = (props) => {
    return (
        <div className={'video-context-player__canvas'}>
            <canvas ref={props.$canvasRef} width={props.canvasWidth} height={props.canvasHeight} />
        </div>
    )
}

export default memo(VideoContextCanvas)
