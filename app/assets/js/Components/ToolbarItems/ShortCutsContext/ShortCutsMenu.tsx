import { memo } from 'react'
import { connect } from 'react-redux'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import Button from '../../Button/Button'

const prefix = 'SHORT_CUTS'
export const ShortCutsOverlayIds = {
    configureShortCuts: `${prefix}/configureShortCuts`,
}

const mapDispatchToProps = {
    setOverlay: actions.overlay.setOverlay,
}

type Props = typeof mapDispatchToProps

const ShortCutsMenu = (props: Props) => {
    const handleClick = () =>
        props.setOverlay({
            overlayId: ShortCutsOverlayIds.configureShortCuts,
            closeOthers: true,
        })

    return (
        <div className="video-editor-menu">
            <Button
                title="Tastenkombination zur Bedienung"
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handleClick}
            >
                <i className="fas fa-keyboard" />
            </Button>
        </div>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(ShortCutsMenu))
