import { useState, useCallback, FC, memo, ReactNode } from 'react'
import { connect } from 'react-redux'
import ToggleChildrenButton from './ToggleChildrenButton'
import PrototypeName from './PrototypeName'
import Color from './Color'
import RemoveButton from './RemoveButton'
import { VideoCodePrototype } from 'Components/VideoEditor/types'
import ChildPrototypeCount from './ChildPrototypeCount'
import Button from 'Components/Button/Button'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import PredefinedCodeLock from '../../PredefinedCodeLock'
import { getColorName } from 'ntc-ts'
import { VideoCodeOverlayIds } from 'Components/ToolbarItems/VideoCodesContext/VideoCodesMenu'

const mapDispatchToProps = {
    openOverlay: actions.overlay.setOverlay,
    setCurrentlyEditedElementId: actions.overlay.setCurrentlyEditedElementId,
    setCurrentlyEditedElementParentId: actions.overlay.setCurrentlyEditedElementParentId,
}

export type OwnProps = {
    videoCodePrototype: VideoCodePrototype
    parentPrototype?: VideoCodePrototype
    readonly?: boolean
    children?: ReactNode
}

type Props = OwnProps & typeof mapDispatchToProps

const PrototypeEntry: FC<Props> = (props) => {
    const [showChildren, setShowChildren] = useState(false)

    const toggleChildrenVisibility = useCallback(() => {
        setShowChildren(!showChildren)
    }, [setShowChildren, showChildren])

    /**
     * WHY:
     * This will be read by the screen reader when the list element if focused (when tabbing through list).
     *
     * Example:
     * "
     *   Name: Gelungener Einsatz von Medien.
     *   Farbe: Grün.
     *   Vordefinierter Video Code.
     *   Hat 1 Untercodes.
     * "
     * oder
     * "
     *   Name: Smart Whiteboard.
     *   Farbe: Grün.
     *   Selbsterstellter Video Code.
     *   Unter-Code von Gelungener Einsatz von Medien.
     *   Hat keine Untercodes.
     * "
     */
    const ariaLabel = `
        Name: ${props.videoCodePrototype.name}.
        Farbe: ${getColorName(props.videoCodePrototype.color).name}
        ${props.videoCodePrototype.userCreated ? 'Selbst erstellter Code' : 'Vordefinierter Code'}.
        ${props.parentPrototype ? `Unter-Code von ${props.parentPrototype.name}.` : ''}
        ${
            props.videoCodePrototype.videoCodes.length > 0
                ? `Hat ${props.videoCodePrototype.videoCodes.length}`
                : 'Hat keine'
        } Untercodes.
    `

    const handleEdit = () => {
        props.setCurrentlyEditedElementId(props.videoCodePrototype.id)
        props.setCurrentlyEditedElementParentId(props.videoCodePrototype.parentId)
        props.openOverlay({
            overlayId: VideoCodeOverlayIds.editPrototype,
            closeOthers: false,
        })
    }

    const handleRemove = () => {
        props.setCurrentlyEditedElementId(props.videoCodePrototype.id)
        props.setCurrentlyEditedElementParentId(undefined)
        props.openOverlay({
            overlayId: VideoCodeOverlayIds.removePrototype,
            closeOthers: false,
        })
    }

    return (
        <li tabIndex={0} aria-label={ariaLabel} className="video-code" title={props.videoCodePrototype.name}>
            <div className={'video-code__content'}>
                <Color color={props.videoCodePrototype.color} />

                <PrototypeName name={props.videoCodePrototype.name} />

                {!props.videoCodePrototype.parentId && (
                    <ChildPrototypeCount count={props.videoCodePrototype.videoCodes.length} />
                )}

                {props.videoCodePrototype.userCreated && !props.readonly ? (
                    <>
                        <Button
                            className={'button button--type-outline-primary button--size-small'}
                            title={'Code Bearbeiten'}
                            onPress={handleEdit}
                        >
                            <i className={'fas fa-pen'} />
                        </Button>
                        <RemoveButton onClick={handleRemove} />
                    </>
                ) : (
                    <PredefinedCodeLock />
                )}

                {!props.videoCodePrototype.parentId ? (
                    <ToggleChildrenButton
                        title={`${showChildren ? 'Schließe' : 'Öffne'} Untercodeliste von ${
                            props.videoCodePrototype.name
                        }`}
                        onClick={toggleChildrenVisibility}
                        showChildren={showChildren}
                    />
                ) : null}
            </div>

            {showChildren ? props.children : null}
        </li>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(PrototypeEntry))
