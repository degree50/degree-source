import VideoCodePrototypes from './VideoCodePrototypes'
import React from 'react'
import { connect } from 'react-redux'
import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = () => ({})

const mapDispatchToProps = {
    closeOverlay: actions.overlay.unsetOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ListCodesOverlay = React.memo((props: Props) => {
    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.list)
    }

    return (
        <Overlay closeCallback={close} title="Codesystem">
            <VideoCodePrototypes />
        </Overlay>
    )
})

export default connect(mapStateToProps, mapDispatchToProps)(ListCodesOverlay)
