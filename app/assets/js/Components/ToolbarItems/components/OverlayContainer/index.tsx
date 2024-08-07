import { FocusScope } from '@react-aria/focus'
import { OverlayContainer as AriaOverlayContainer } from '@react-aria/overlays'
import { selectors, VideoEditorState } from 'Components/VideoEditor/VideoEditorSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import PickComparedSolutionOverlay from 'Components/MaterialEditor/Overlays/PickComparedSolutionOverlay'
import { AllMediaItemsOverlayIds } from 'Components/ToolbarItems/AllMediaItemsContext/AllMediaItemsMenu'
import AllMediaItemsOverlay from 'Components/ToolbarItems/AllMediaItemsContext/Overlays/AllMediaItemsOverlay'
import { AnnotationOverlayIds } from 'Components/ToolbarItems/AnnotationsContext/AnnotationsMenu'
import ActiveAnnotationsOverlay from 'Components/ToolbarItems/AnnotationsContext/Overlays/ActiveAnnotationsOverlay'
import AllAnnotationsOverlay from 'Components/ToolbarItems/AnnotationsContext/Overlays/AllAnnotationsOverlay'
import CreateAnnotationOverlay from 'Components/ToolbarItems/AnnotationsContext/Overlays/CreateAnnotationOverlay'
import DeleteAnnotationOverlay from 'Components/ToolbarItems/AnnotationsContext/Overlays/DeleteAnnotationOverlay'
import EditAnnotationOverlay from 'Components/ToolbarItems/AnnotationsContext/Overlays/EditAnnotationOverlay'
import AufgabeOverlay, { AUFGABE_OVERLAY_ID } from 'Components/ToolbarItems/Aufgabe/AufgabeOverlay'
import { CutOverlayIds } from 'Components/ToolbarItems/CuttingContext/CuttingMenu'
import ActiveCutsOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/ActiveCutsOverlay'
import AllCutsOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/AllCutsOverlay'
import CreateCutOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/CreateCutOverlay'
import CutlistOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/CutlistOverlay'
import CutPreviewOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/CutPreviewOverlay'
import DeleteCutOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/DeleteCutOverlay'
import EditCutOverlay from 'Components/ToolbarItems/CuttingContext/Overlays/EditCutOverlay'
import { SolutionFilterOverlayIds } from 'Components/ToolbarItems/FilterContext/FilterMenu'
import SolutionFilterOverlay from 'Components/ToolbarItems/FilterContext/Overlays/SolutionFilterOverlay'
import SetVideoPlayerTimeOverlay, {
    SetVideoPlayerTimeOverlayId,
} from 'Components/ToolbarItems/SetVideoPlayerTimeContext/Overlays/SetVideoPlayerTimeOverlay'
import ShortCutsConfigurationOverlay from 'Components/ToolbarItems/ShortCutsContext/Overlays/ShortCutsConfigurationOverlay'
import { ShortCutsOverlayIds } from 'Components/ToolbarItems/ShortCutsContext/ShortCutsMenu'
import TeamOverlay, { TEAM_OVERLAY_ID } from 'Components/ToolbarItems/Team/TeamOverlay'
import ActiveVideoCodesOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/ActiveVideoCodesOverlay'
import AllVideoCodesOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/AllVideoCodesOverlay'
import CreateVideoCodeOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/CreateVideoCodeOverlay'
import DeleteVideoCodeOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/DeleteVideoCodeOverlay'
import EditVideoCodeOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/EditVideoCodeOverlay'
import DeleteVideoCodePrototypeOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/EditVideoCodeOverlay/DeleteVideoCodePrototypeOverlay'
import EditVideoCodePrototypeOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/EditVideoCodePrototypeOverlay/EditVideoCodePrototypeOverlay'
import ListCodesOverlay from 'Components/ToolbarItems/VideoCodesContext/Overlays/ListCodesOverlay'
import { VideoCodeOverlayIds } from 'Components/ToolbarItems/VideoCodesContext/VideoCodesMenu'
import ZusatzAttachmentOverlay, {
    ZUSATZ_ATTACHMENTS_OVERLAY_ID,
} from 'Components/ToolbarItems/ZusatzAttachment/ZusatzAttachmentOverlay'
import { MaterialSolutionMenuOverlayIds } from 'Components/ToolbarItems/MaterialSolutionMenu'

const mapOverlayIdToOverlayContent = (id?: string) => {
    switch (id) {
        // All media items
        case AllMediaItemsOverlayIds.all: {
            return <AllMediaItemsOverlay />
        }

        // Annotations
        case AnnotationOverlayIds.active: {
            return <ActiveAnnotationsOverlay />
        }
        case AnnotationOverlayIds.create:
            return <CreateAnnotationOverlay />
        case AnnotationOverlayIds.all:
            return <AllAnnotationsOverlay />
        case AnnotationOverlayIds.edit:
            return <EditAnnotationOverlay />
        case AnnotationOverlayIds.remove:
            return <DeleteAnnotationOverlay />

        // VideoCodes
        case VideoCodeOverlayIds.list: {
            return <ListCodesOverlay />
        }
        case VideoCodeOverlayIds.editPrototype: {
            return <EditVideoCodePrototypeOverlay />
        }
        case VideoCodeOverlayIds.removePrototype: {
            return <DeleteVideoCodePrototypeOverlay />
        }
        case VideoCodeOverlayIds.active: {
            return <ActiveVideoCodesOverlay />
        }
        case VideoCodeOverlayIds.create:
            return <CreateVideoCodeOverlay />
        case VideoCodeOverlayIds.all:
            return <AllVideoCodesOverlay />
        case VideoCodeOverlayIds.edit:
            return <EditVideoCodeOverlay />
        case VideoCodeOverlayIds.remove:
            return <DeleteVideoCodeOverlay />

        // Cuts
        case CutOverlayIds.active: {
            return <ActiveCutsOverlay />
        }
        case CutOverlayIds.create: {
            return <CreateCutOverlay />
        }
        case CutOverlayIds.all: {
            return <AllCutsOverlay />
        }
        case CutOverlayIds.allByCutOrder: {
            return <CutlistOverlay />
        }
        case CutOverlayIds.edit: {
            return <EditCutOverlay />
        }
        case CutOverlayIds.remove: {
            return <DeleteCutOverlay />
        }
        case CutOverlayIds.cutPreview: {
            return <CutPreviewOverlay />
        }

        // Others
        case TEAM_OVERLAY_ID: {
            return <TeamOverlay />
        }
        case AUFGABE_OVERLAY_ID: {
            return <AufgabeOverlay />
        }
        case ZUSATZ_ATTACHMENTS_OVERLAY_ID: {
            return <ZusatzAttachmentOverlay />
        }

        // Filter
        case SolutionFilterOverlayIds.filterSolutions: {
            return <SolutionFilterOverlay />
        }

        // ShortCuts
        case ShortCutsOverlayIds.configureShortCuts: {
            return <ShortCutsConfigurationOverlay />
        }

        // SetVideoPlayerTime
        case SetVideoPlayerTimeOverlayId: {
            return <SetVideoPlayerTimeOverlay />
        }

        case MaterialSolutionMenuOverlayIds.compare: {
            return <PickComparedSolutionOverlay />
        }

        default:
            return undefined
    }
}

const mapStateToProps = (state: VideoEditorState) => ({
    isVisible: selectors.overlay.isVisible(state),
    overlayIds: selectors.overlay.overlayIds(state),
})

type Props = ReturnType<typeof mapStateToProps>

const OverlayContainer: FC<Props> = (props) => {
    const isOpen = props.isVisible && props.overlayIds.length > 0

    return (
        <AriaOverlayContainer
            style={{
                display: isOpen ? 'block' : 'none',
                position: 'absolute',
                top: 0,
                height: '100vh',
                width: '100vw',
            }}
        >
            {isOpen &&
                props.overlayIds.map((overlayId) => (
                    <FocusScope autoFocus contain restoreFocus key={overlayId}>
                        {mapOverlayIdToOverlayContent(overlayId)}
                    </FocusScope>
                ))}
        </AriaOverlayContainer>
    )
}

export default connect(mapStateToProps)(memo(OverlayContainer))
