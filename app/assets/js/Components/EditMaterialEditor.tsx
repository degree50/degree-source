import React, { useRef } from 'react'
import { useAppDispatch, useAppSelector } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { Material } from 'StimulusControllers/ExerciseAndSolutionStore/MaterialsSlice'
import MaterialEditor from './MaterialEditor'
import { ExercisePhaseStatus } from './VideoEditor/types'
import { useShortCuts } from 'Components/ToolbarItems/ShortCutsContext/useShortCuts'

const EditMaterialEditor = () => {
    useShortCuts()

    const material = useAppSelector(selectors.data.selectMaterialOfCurrentSolution)

    const userIsCurrentEditor = useAppSelector(selectors.selectUserIsCurrentEditor)
    const currentSolutionStatus = useAppSelector(selectors.data.solutions.selectCurrentSolutionStatus)

    const isReadonly = !userIsCurrentEditor || currentSolutionStatus !== ExercisePhaseStatus.IN_BEARBEITUNG

    const initialMaterial = useRef(material?.material ?? '')

    const dispatch = useAppDispatch()

    const handleChange = (newValue: string) => {
        if (material !== undefined) {
            const updatedMaterial: Material = {
                ...material,
                material: newValue,
            }

            dispatch(actions.data.materials.updateMaterial({ material: updatedMaterial }))
            dispatch(syncSolutionAction())
        }
    }

    return <MaterialEditor material={initialMaterial.current} onChange={handleChange} readonly={isReadonly} />
}

export default React.memo(EditMaterialEditor)
