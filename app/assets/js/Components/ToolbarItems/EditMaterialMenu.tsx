import Button from 'Components/Button/Button'
import React from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => {
    const needsReview = selectors.data.selectMaterialSolutionNeedsReview(state)

    return {
        isReadonly: selectors.data.materialSolution.selectIsReadonly(state),
        needsReview,
    }
}

const mapDispatchToProps = {
    toggleIsReadonly: actions.data.materialSolution.toggleIsReadonly,
}

const connector = connect(mapStateToProps, mapDispatchToProps)

type Props = ConnectedProps<typeof connector>

const EditMaterialMenu = (props: Props) => {
    const { isReadonly, toggleIsReadonly, needsReview } = props
    const iconClassName = isReadonly ? 'fa-pen-slash' : 'fa-pen'

    const handlePress = () => {
        toggleIsReadonly()
    }

    const title = isReadonly ? 'Material bearbeiten' : 'Material nicht mehr bearbeiten'

    return (
        <div className="video-editor-menu">
            <Button
                title={title}
                className="button button--type-primary video-editor__toolbar__button"
                onPress={handlePress}
                isDisabled={!needsReview}
            >
                <i className={`fas ${iconClassName}`} />
            </Button>
        </div>
    )
}

export default connector(React.memo(EditMaterialMenu))
