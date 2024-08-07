import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import React from 'react'
import { connect } from 'react-redux'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import Button from 'Components/Button/Button'

const prefix = 'SOLUTION_FILTER'

export const SolutionFilterOverlayIds = {
    filterSolutions: `${prefix}/filterSolutions`,
}

const mapStateToProps = (state: AppState) => {
    return {
        hasNoPreviousSolutions: selectors.data.solutions.selectPreviousIds(state).length === 0,
    }
}

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const FilterMenu = (props: Props) => {
    const solutionsFilterLabel = 'Lösungs-Filter anpassen'

    return (
        <div className="video-editor-menu">
            <Button
                title={solutionsFilterLabel}
                className="button button--type-primary video-editor__toolbar__button"
                isDisabled={props.hasNoPreviousSolutions}
                onPress={() =>
                    props.setOverlay({
                        overlayId: SolutionFilterOverlayIds.filterSolutions,
                        closeOthers: true,
                    })
                }
            >
                {<i className="fas fa-filter" />}
            </Button>
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(FilterMenu))
