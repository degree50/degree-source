import { SolutionId } from 'Components/VideoEditor/types'
import { memo, ReactNode } from 'react'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { connect, ConnectedProps } from 'react-redux'
import CKEditor from 'Components/CKEditor'
import { Material } from 'StimulusControllers/ExerciseAndSolutionStore/MaterialsSlice'
import { syncSolutionAsDozentAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { useAppDispatch } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'

type OwnProps = {
    solutionId: SolutionId
    isReadonly?: boolean
    renderHeader: (children: ReactNode) => ReactNode
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => {
    const { solutionId } = ownProps

    const solution = selectors.data.solutions.selectSolutionById(state, {
        solutionId,
    })

    const materialId = solution.solutionData.material

    return {
        material: selectors.data.materials.byId(state)[materialId],
        fromGroupPhase: solution.fromGroupPhase,
        userName: solution.userName,
    }
}

const connector = connect(mapStateToProps)

type Props = OwnProps & ConnectedProps<typeof connector>

const MaterialSolutionEditor = (props: Props) => {
    const { material, fromGroupPhase, userName, renderHeader, isReadonly } = props
    const dispatch = useAppDispatch()

    const handleChange = (newValue: string) => {
        if (material !== undefined && !isReadonly) {
            const updatedMaterial: Material = {
                ...material,
                material: newValue,
            }

            dispatch(actions.data.materials.updateMaterial({ material: updatedMaterial }))
            dispatch(syncSolutionAsDozentAction())
        }
    }

    return (
        <>
            {renderHeader(<h3>{`Lösung von:${fromGroupPhase ? ' Gruppe von' : ''} ${userName}`}</h3>)}
            <CKEditor readonly={props.isReadonly} value={material.material} onChange={handleChange} />
        </>
    )
}

export default connector(memo(MaterialSolutionEditor))
