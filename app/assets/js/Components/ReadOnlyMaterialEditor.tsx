import React from 'react'
import { useAppSelector } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import MaterialEditor from './MaterialEditor'
import { selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { useShortCuts } from 'Components/ToolbarItems/ShortCutsContext/useShortCuts'

const ReadOnlyMaterialEditor = () => {
    useShortCuts()

    const material = useAppSelector(selectors.data.selectMaterialOfCurrentSolution)

    return <MaterialEditor material={material?.material} readonly />
}

export default React.memo(ReadOnlyMaterialEditor)
