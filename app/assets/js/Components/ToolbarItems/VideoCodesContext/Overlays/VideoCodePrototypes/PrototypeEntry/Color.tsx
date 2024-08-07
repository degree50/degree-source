import React from 'react'

type Props = {
    color?: string
}

const Color = ({ color }: Props) => {
    return <div className={'video-code__color'} style={{ backgroundColor: color || '' }} />
}

export default React.memo(Color)
