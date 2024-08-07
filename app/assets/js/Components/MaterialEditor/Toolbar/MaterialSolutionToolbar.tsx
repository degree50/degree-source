import AufgabeMenu from 'Components/ToolbarItems/Aufgabe/AufgabeMenu'
import EditMaterialMenu from 'Components/ToolbarItems/EditMaterialMenu'
import FilterMenu from 'Components/ToolbarItems/FilterContext/FilterMenu'
import FinishReviewMenu from 'Components/ToolbarItems/FinishReviewMenu'
import MaterialSolutionMenu from 'Components/ToolbarItems/MaterialSolutionMenu'
import ShortCutsMenu from 'Components/ToolbarItems/ShortCutsContext/ShortCutsMenu'
import TeamMenu from 'Components/ToolbarItems/Team/TeamMenu'
import ZusatzAttachmentMenu from 'Components/ToolbarItems/ZusatzAttachment/ZusatzAttachmentMenu'

const MaterialSolutionToolbar = () => {
    return (
        <div className="video-editor__toolbar">
            <EditMaterialMenu />
            <FinishReviewMenu />
            <MaterialSolutionMenu />
            <AufgabeMenu />
            <ZusatzAttachmentMenu />
            <TeamMenu />
            <FilterMenu />
            <ShortCutsMenu />
        </div>
    )
}

export default MaterialSolutionToolbar
