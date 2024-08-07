import React, { useMemo, useRef } from 'react'
import { connect } from 'react-redux'
import { watchModals } from '@react-aria/aria-modal-polyfill'
import { OverlayProvider } from '@react-aria/overlays'
import { I18nProvider } from '@react-aria/i18n'
import { useDebouncedResizeObserver } from 'Components/VideoEditor/utils/useDebouncedResizeObserver'
import VideoAnalysis from 'StimulusControllers/ExercisePhaseApp/Domain/ExercisePhases/VideoAnalysis'
import {
    ConfigStateSlice,
    selectors as configSelectors,
} from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import CuttingSolutions from './CuttingSolutions'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import MaterialSolution from 'StimulusControllers/SolutionsApp/MaterialSolution'

const mapStateToProps = (state: ConfigStateSlice) => ({
    activePhaseType: configSelectors.selectPhaseType(state),
})

const mapDispatchToProps = {}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const getSolutionComponentByPhaseType = (phaseType: ExercisePhaseTypesEnum) => {
    switch (phaseType) {
        case ExercisePhaseTypesEnum.VIDEO_ANALYSIS:
            return <VideoAnalysis />
        case ExercisePhaseTypesEnum.VIDEO_CUTTING:
            return <CuttingSolutions />
        case ExercisePhaseTypesEnum.MATERIAL:
            return <MaterialSolution />
        default:
            throw new Error(`No SolutionApp available for ExercisePhase with type "${phaseType}"`)
    }
}

const SolutionsApp = (props: Props) => {
    // react-aria-modal watches a container element for aria-modal nodes and
    // hides the rest of the dom from screen readers with aria-hidden when one is open.
    watchModals()

    const ref: React.RefObject<HTMLDivElement> = useRef(null)

    const { height } = useDebouncedResizeObserver(ref, 500)

    const PhaseComponent = useMemo(
        () => getSolutionComponentByPhaseType(props.activePhaseType),
        [props.activePhaseType]
    )

    return (
        <I18nProvider locale="de-DE">
            <OverlayProvider className={'exercise-phase__inner solutions-container'}>
                <div className={'exercise-phase__content'} ref={ref}>
                    {height > 0 && PhaseComponent}
                </div>
            </OverlayProvider>
        </I18nProvider>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(SolutionsApp))
