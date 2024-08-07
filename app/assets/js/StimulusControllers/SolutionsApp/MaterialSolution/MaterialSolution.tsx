import MaterialSolutionEditor from 'StimulusControllers/SolutionsApp/MaterialSolution/MaterialSolutionEditor'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { connect, ConnectedProps } from 'react-redux'
import { memo, useEffect } from 'react'
import MaterialSolutionToolbar from 'Components/MaterialEditor/Toolbar/MaterialSolutionToolbar'
import MaterialSolutionEditorHeader from 'StimulusControllers/SolutionsApp/MaterialSolution/MaterialSolutionEditorHeader'
import OverlayContainer from 'Components/ToolbarItems/components/OverlayContainer'

const mapStateToProps = (state: AppState) => ({
    solutionIds: selectors.videoEditor.filter.selectVisiblePreviousSolutionIds(state),
    selectedSolutionId: selectors.data.materialSolution.selectSelectedSolutionId(state),
    comparedSolutionId: selectors.data.materialSolution.selectComparedSolutionId(state),
    canSelectNextSolution: selectors.data.materialSolution.selectCanSelectNextSolution(state),
    canSelectPreviousSolution: selectors.data.materialSolution.selectCanSelectPreviousSolution(state),
    canCompareNextSolution: selectors.data.materialSolution.selectCanCompareNextSolution(state),
    canComparePreviousSolution: selectors.data.materialSolution.selectCanComparePreviousSolution(state),
    shouldCompare: selectors.data.materialSolution.selectShouldCompare(state),
    isReadonly: selectors.data.materialSolution.selectIsReadonly(state),
})

const mapDispatchToProps = {
    setSelectedSolutionId: actions.data.materialSolution.setSelectedSolutionId,
    setComparedSolutionId: actions.data.materialSolution.setComparedSolutionId,
}

const connector = connect(mapStateToProps, mapDispatchToProps)

type Props = ConnectedProps<typeof connector>

/**
 * This is the main entry point for the material solution view
 */
const MaterialSolution = (props: Props) => {
    const {
        solutionIds,
        selectedSolutionId,
        comparedSolutionId,
        canSelectNextSolution,
        canSelectPreviousSolution,
        setSelectedSolutionId,
        setComparedSolutionId,
        canCompareNextSolution,
        canComparePreviousSolution,
        isReadonly,
    } = props

    useEffect(() => {
        if (!selectedSolutionId || !solutionIds.includes(selectedSolutionId)) {
            setSelectedSolutionId(solutionIds[0])
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedSolutionId, solutionIds])

    const handleSelectNextSolution = () => {
        if (selectedSolutionId && canSelectNextSolution) {
            setSelectedSolutionId(solutionIds[solutionIds.indexOf(selectedSolutionId) + 1])
        }
    }

    const handleSelectPreviousSolution = () => {
        if (selectedSolutionId && canSelectPreviousSolution) {
            setSelectedSolutionId(solutionIds[solutionIds.indexOf(selectedSolutionId) - 1])
        }
    }

    const handleCompareNextSolution = () => {
        if (comparedSolutionId && canCompareNextSolution) {
            setComparedSolutionId(solutionIds[solutionIds.indexOf(comparedSolutionId) + 1])
        }
    }

    const handleComparePreviousSolution = () => {
        if (comparedSolutionId && canComparePreviousSolution) {
            setComparedSolutionId(solutionIds[solutionIds.indexOf(comparedSolutionId) - 1])
        }
    }

    return (
        <div className="material-solution" data-test-id="materialSolution">
            <div className="material-solution__editors">
                <div className="material-solution__editor">
                    {selectedSolutionId && (
                        <MaterialSolutionEditor
                            solutionId={selectedSolutionId}
                            isReadonly={isReadonly}
                            renderHeader={(children) => (
                                <MaterialSolutionEditorHeader
                                    canSelectNext={canSelectNextSolution}
                                    canSelectPrev={canSelectPreviousSolution}
                                    onNext={handleSelectNextSolution}
                                    onPrev={handleSelectPreviousSolution}
                                    nextLabel="Nächste Lösung auswählen"
                                    prevLabel="Vorherige Lösung auswählen"
                                    children={children}
                                />
                            )}
                        />
                    )}
                </div>
                {props.shouldCompare && (
                    <div className="material-solution__editor">
                        {comparedSolutionId && (
                            <MaterialSolutionEditor
                                solutionId={comparedSolutionId}
                                isReadonly={true}
                                renderHeader={(children) => (
                                    <MaterialSolutionEditorHeader
                                        canSelectNext={canCompareNextSolution}
                                        canSelectPrev={canComparePreviousSolution}
                                        onNext={handleCompareNextSolution}
                                        onPrev={handleComparePreviousSolution}
                                        nextLabel="Nächste Lösung vergleichen"
                                        prevLabel="Vorherige Lösung vergleichen"
                                        children={children}
                                    />
                                )}
                            />
                        )}
                    </div>
                )}
            </div>
            <MaterialSolutionToolbar />
            <OverlayContainer />
        </div>
    )
}

export default connector(memo(MaterialSolution))
