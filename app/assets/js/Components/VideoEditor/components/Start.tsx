import React from 'react'

type Props = {
    start: string
}

const Start = ({ start }: Props) => {
    return <div>Von: {start}</div>
}

export default React.memo(Start)
