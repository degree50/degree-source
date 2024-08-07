import AllMediaItemsMenu from 'Components/ToolbarItems/AllMediaItemsContext/AllMediaItemsMenu'
import AufgabeMenu from 'Components/ToolbarItems/Aufgabe/AufgabeMenu'
import FilterMenu from 'Components/ToolbarItems/FilterContext/FilterMenu'
import ShortCutsMenu from 'Components/ToolbarItems/ShortCutsContext/ShortCutsMenu'
import TeamMenu from 'Components/ToolbarItems/Team/TeamMenu'
import ZusatzAttachmentMenu from 'Components/ToolbarItems/ZusatzAttachment/ZusatzAttachmentMenu'

const MaterialEditorToolbar = () => {
    return (
        <div className="video-editor__toolbar">
            <AllMediaItemsMenu />
            <AufgabeMenu />
            <ZusatzAttachmentMenu />
            <TeamMenu />
            <FilterMenu />
            <ShortCutsMenu />
        </div>
    )
}

export default MaterialEditorToolbar
