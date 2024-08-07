import React from 'react'

type Props = {
    count: number
}

const ChildPrototypeCount = ({ count }: Props) => {
    const label = `${count} Untercodes`
    return (
        <div aria-label={label} title={label} className="video-code__child-count">
            {count}
        </div>
    )
}

export default React.memo(ChildPrototypeCount)
