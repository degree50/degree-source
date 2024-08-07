import Button from 'Components/Button/Button'
import React from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

export const prefix = 'ALL_MEDIA_ITEMS'

export const AllMediaItemsOverlayIds = {
    all: `${prefix}/all`,
}

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
}

const connector = connect(undefined, mapDispatchToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux

const AllMediaItemsMenu = (props: Props) => {
    const { setOverlay } = props

    const handleClick = () => {
        setOverlay({ overlayId: AllMediaItemsOverlayIds.all, closeOthers: true })
    }

    return (
        <div className="video-editor-menu">
            <Button
                title="Zeige Liste aller Elemente"
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handleClick}
            >
                <i className="fas fa-list" />
            </Button>
        </div>
    )
}

export default connector(React.memo(AllMediaItemsMenu))
