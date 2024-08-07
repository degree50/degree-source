import React from 'react'

type Props = {
    children: React.ReactNode
}

const MediaLaneToolbarItem = (props: Props) => {
    return <div className="media-lane-toolbar__item">{props.children}</div>
}

export default React.memo(MediaLaneToolbarItem)
