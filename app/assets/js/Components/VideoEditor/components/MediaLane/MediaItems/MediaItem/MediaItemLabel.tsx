import React from 'react'
import { MediaItem, MediaItemType } from '../../../../types'

type Props = {
    item: MediaItem<MediaItemType>
    showTextInMediaItems: boolean
}

const MediaItemLabel = ({ item, showTextInMediaItems }: Props) => {
    // Used for video-codes
    const titleToAcronym = (title: string) => {
        const titleWords = title.toLowerCase().split(' ')
        return titleWords.map((word) => word.charAt(0).toUpperCase()).join('')
    }

    return (
        <span>
            {showTextInMediaItems
                ? item.text.split(/\r?\n/).map((line: string, index: number) => <p key={index}>{line}</p>)
                : titleToAcronym(item.text)}
        </span>
    )
}

export default MediaItemLabel
