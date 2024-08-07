import React from 'react'
import { connect } from 'react-redux'
import { selectors } from '../Config/ConfigSlice'
import { AppState, AppDispatch } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => ({
    config: selectors.selectConfig(state),
})

const mapDispatchToProps = (dispatch: AppDispatch) => ({})

type AdditionalProps = {
    // currently none
}

type ExerciseDescriptionProps = AdditionalProps &
    ReturnType<typeof mapStateToProps> &
    ReturnType<typeof mapDispatchToProps>

const ExerciseDescription: React.FC<ExerciseDescriptionProps> = ({ config }) => {
    return (
        <div>
            <h3>{config.title}</h3>
            <div dangerouslySetInnerHTML={{ __html: config.description }} />
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(ExerciseDescription)
