import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import React, { memo } from 'react'
import { connect } from 'react-redux'
import Overlay from '../../components/Overlay'
import { SolutionFilterOverlayIds } from '../FilterMenu'
import ActivePreviousSolutions from 'Components/VideoEditor/components/MultiLane/Filter/ActivePreviousSolutions'

const mapDispatchToProps = {
    closeOverlay: actions.overlay.unsetOverlay,
}

type Props = typeof mapDispatchToProps

const SolutionFilterOverlay = (props: Props) => {
    const close = () => {
        props.closeOverlay(SolutionFilterOverlayIds.filterSolutions)
    }

    return (
        <Overlay closeCallback={close} title="Aktive Lösungen">
            <ActivePreviousSolutions />
        </Overlay>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(SolutionFilterOverlay))
