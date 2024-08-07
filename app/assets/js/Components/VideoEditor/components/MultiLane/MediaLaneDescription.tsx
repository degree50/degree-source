import React from 'react'
import getComponentName from 'Components/VideoEditor/components/MultiLane/getComponentName'

type Props = {
    componentName: ReturnType<typeof getComponentName>
    itemCount: number
    userName?: string
    fromGroupPhase?: boolean
    isPreviousSolution?: boolean
}

const MediaLaneDescription = (props: Props) => {
    const { componentName, itemCount, userName, fromGroupPhase, isPreviousSolution } = props
    return (
        <div className="multilane__medialane-description">
            {componentName} ({itemCount}) - {isPreviousSolution ? 'aus Lösung von ' : ''}{' '}
            {fromGroupPhase ? 'Gruppe von' : ''} {userName ?? '<Unbekannter Nutzer>'}
        </div>
    )
}

export default React.memo(MediaLaneDescription)
