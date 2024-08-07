import React from 'react'

type Props = {
    name: string
}

const PrototypeName = (props: Props) => {
    return <span className={'video-code__name'}>{props.name}</span>
}

export default React.memo(PrototypeName)
