import React from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import Button from 'Components/Button/Button'

export const prefix = 'MATERIAL_SOLUTION'

export const MaterialSolutionMenuOverlayIds = {
    compare: `${prefix}/compare`,
}

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
}

const connector = connect(undefined, mapDispatchToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux

const menuButtonAriaLabel = 'Lösung Auswählen und Vergleichen'

const MaterialSolutionMenu = (props: Props) => {
    const { setOverlay } = props

    return (
        <div className="video-editor-menu">
            <Button
                title={menuButtonAriaLabel}
                className="button button--type-primary video-editor__toolbar__button"
                onPress={() =>
                    setOverlay({
                        overlayId: MaterialSolutionMenuOverlayIds.compare,
                        closeOthers: true,
                    })
                }
            >
                <i className="fa-solid fa-eye" />
            </Button>
        </div>
    )
}

export default connector(React.memo(MaterialSolutionMenu))
