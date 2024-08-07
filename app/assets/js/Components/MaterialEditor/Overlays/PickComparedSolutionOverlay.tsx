import { FC, memo } from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import RadioGroup from 'Components/RadioGroup/RadioGroup'
import RadioOption from 'Components/RadioGroup/RadioOption'
import Checkbox from 'Components/Checkbox'
import Overlay from 'Components/ToolbarItems/components/Overlay'
import { MaterialSolutionMenuOverlayIds } from 'Components/ToolbarItems/MaterialSolutionMenu'

const mapStateToProps = (state: AppState) => ({
    visibleSolutions: selectors.videoEditor.filter.selectVisiblePreviousSolutions(state),
    solutions: selectors.data.solutions.selectById(state),
    selectedSolutionId: selectors.data.materialSolution.selectSelectedSolutionId(state),
    comparedSolutionId: selectors.data.materialSolution.selectComparedSolutionId(state),
    shouldCompare: selectors.data.materialSolution.selectShouldCompare(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    setSelectedSolutionId: actions.data.materialSolution.setSelectedSolutionId,
    setComparedSolutionId: actions.data.materialSolution.setComparedSolutionId,
    setShouldCompare: actions.data.materialSolution.setShouldCompare,
}

const connector = connect(mapStateToProps, mapDispatchToProps)

type Props = ConnectedProps<typeof connector>

const PickComparedSolutionOverlay: FC<Props> = (props) => {
    const {
        closeOverlay,
        visibleSolutions,
        solutions,
        selectedSolutionId,
        setSelectedSolutionId,
        comparedSolutionId,
        setComparedSolutionId,
    } = props

    const handleCloseOverlay = () => {
        closeOverlay(MaterialSolutionMenuOverlayIds.compare)
    }

    const solutionOptions = visibleSolutions.map(({ id }) => (
        <RadioOption key={id} value={id}>
            {`${solutions[id].fromGroupPhase ? 'Gruppe von ' : ''}${solutions[id].userName ?? '<kein Name>'}`}
        </RadioOption>
    ))

    return (
        <Overlay title="Lösungen Auswählen und Vergleichen" closeCallback={handleCloseOverlay}>
            <Checkbox isSelected={props.shouldCompare} onChange={props.setShouldCompare}>
                Vergleichen
            </Checkbox>
            <div className="material-overlay__radio-group-wrapper">
                <RadioGroup
                    name="Auswählen"
                    label="Lösung auswählen"
                    value={selectedSolutionId}
                    onChange={setSelectedSolutionId}
                >
                    {solutionOptions}
                </RadioGroup>
                {props.shouldCompare && (
                    <RadioGroup
                        name="Vergleichen mit"
                        label="Lösung zum Vergleich auswählen"
                        value={comparedSolutionId}
                        onChange={setComparedSolutionId}
                    >
                        {solutionOptions}
                    </RadioGroup>
                )}
            </div>
        </Overlay>
    )
}

export default connector(memo(PickComparedSolutionOverlay))
