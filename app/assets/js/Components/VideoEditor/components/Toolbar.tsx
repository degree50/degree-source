import AllMediaItemsMenu from 'Components/ToolbarItems/AllMediaItemsContext/AllMediaItemsMenu'
import AnnotationsMenu from 'Components/ToolbarItems/AnnotationsContext/AnnotationsMenu'
import AufgabeMenu from 'Components/ToolbarItems/Aufgabe/AufgabeMenu'
import CuttingMenu from 'Components/ToolbarItems/CuttingContext/CuttingMenu'
import FilterMenu from 'Components/ToolbarItems/FilterContext/FilterMenu'
import ShortCutsMenu from 'Components/ToolbarItems/ShortCutsContext/ShortCutsMenu'
import TeamMenu from 'Components/ToolbarItems/Team/TeamMenu'
import ToggleVideoFavorite from 'Components/ToolbarItems/ToggleVideoFavorite'
import VideoCodesMenu from 'Components/ToolbarItems/VideoCodesContext/VideoCodesMenu'
import ZusatzAttachmentMenu from 'Components/ToolbarItems/ZusatzAttachment/ZusatzAttachmentMenu'
import React, { FC, ReactNode } from 'react'
import classNames from 'classnames'

type OwnProps = {
    hidden?: boolean
    children?: ReactNode
}

const Toolbar: FC<OwnProps> = (props) => {
    const className = classNames('video-editor__toolbar', {
        'video-editor__toolbar--hidden': props.hidden,
    })

    return (
        <div className={className}>
            <AllMediaItemsMenu />
            <AnnotationsMenu />
            <VideoCodesMenu />
            <CuttingMenu />
            <AufgabeMenu />
            <ZusatzAttachmentMenu />
            <TeamMenu />
            <FilterMenu />
            <ToggleVideoFavorite />
            <ShortCutsMenu />
            {props.children}
        </div>
    )
}

export default Toolbar
