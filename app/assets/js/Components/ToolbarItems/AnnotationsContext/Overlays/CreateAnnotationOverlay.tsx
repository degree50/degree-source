import Button from 'Components/Button/Button'
import TextField from 'Components/VideoEditor/components/TextField'
import TimeInput from 'Components/TimeInput'
import { Annotation } from 'Components/VideoEditor/types'
import { secondToTime } from 'Components/VideoEditor/utils/time'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import React, { FC, memo } from 'react'
import { connect } from 'react-redux'
import { generate } from 'shortid'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import Overlay from '../../components/Overlay'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import { useAnnotationEdit } from './useAnnotationEdit'
import { ShortCutId } from '../../ShortCutsContext/ShortCutsSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => ({
    currentTime: selectors.videoEditor.player.selectSyncPlayPosition(state),
    duration: selectors.config.selectVideos(state)[0].duration,
    currentSolutionId: selectors.data.solutions.selectCurrentId(state),
})

const mapDispatchToProps = {
    appendAnnotation: actions.data.annotations.append,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CreateAnnotationOverlay: FC<Props> = (props) => {
    const { currentTime, duration } = props

    // currentTime as start
    const start = secondToTime(currentTime)
    // some default delta for end
    const end = secondToTime(Math.min(currentTime + duration / 10, duration))

    // transient annotation
    const initialAnnotation: Annotation = {
        id: generate(),
        start,
        end,
        text: '',
        memo: '',
        color: null,
        solutionId: props.currentSolutionId,
    }

    const {
        transientAnnotation,
        handleStartTimeChange,
        handleEndTimeChange,
        updateText,
        updateMemo,
        minAllowedStartTime,
        maxAllowedStartTime,
        minAllowedEndTime,
        maxAllowedEndTime,
    } = useAnnotationEdit(duration, initialAnnotation)

    const close = () => {
        props.closeOverlay(AnnotationOverlayIds.create)
    }

    if (!transientAnnotation) {
        close()
        return null
    }

    const handleSave = () => {
        props.appendAnnotation(transientAnnotation)
        props.syncSolution()
        close()
    }

    const handleUseCurrentTimeForStartValue = () => {
        handleStartTimeChange(secondToTime(currentTime))
    }

    const handleUseCurrentTimeForEndValue = () => {
        handleEndTimeChange(secondToTime(currentTime))
    }

    const footerContent = (
        <>
            <Button className="button button--type-outline-primary" onPress={close} title="Annotation Verwerfen">
                Verwerfen
            </Button>
            <Button className="button button--type-primary" onPress={handleSave} title="Annotation Speichern">
                Speichern
            </Button>
        </>
    )

    return (
        <Overlay closeCallback={close} title="Neue Annotation" footerContent={footerContent}>
            <div className="time-input-wrapper">
                <TimeInput
                    label="Start"
                    value={transientAnnotation.start}
                    min={minAllowedStartTime}
                    max={maxAllowedStartTime}
                    onChange={handleStartTimeChange}
                    hoursLabel="Start Stunden"
                    minutesLabel="Start Minuten"
                    secondsLabel="Start Sekunden"
                />
                <Button
                    className="button button--type-link"
                    onPress={handleUseCurrentTimeForStartValue}
                    title={'Aktuelle Zeit als Startzeit übernehmen'}
                    data-short-cut-id={ShortCutId.SET_CURRENT_TIME_AS_START_VALUE}
                >
                    <i className="fas fa-stopwatch" /> Aktuelle Zeit als Startzeit übernehmen
                </Button>
            </div>
            <div className="time-input-wrapper">
                <TimeInput
                    label="Ende"
                    value={transientAnnotation.end}
                    min={minAllowedEndTime}
                    max={maxAllowedEndTime}
                    onChange={handleEndTimeChange}
                    hoursLabel="Ende Stunden"
                    minutesLabel="Ende Minuten"
                    secondsLabel="Ende Sekunden"
                />
                <Button
                    className="button button--type-link"
                    onPress={handleUseCurrentTimeForEndValue}
                    title={'Aktuelle Zeit als Endzeit übernehmen'}
                    data-short-cut-id={ShortCutId.SET_CURRENT_TIME_AS_END_VALUE}
                >
                    <i className="fas fa-stopwatch" /> Aktuelle Zeit als Endzeit übernehmen
                </Button>
            </div>
            <hr />
            <label htmlFor="text">Annotationstext</label>
            <TextField id="text" text={transientAnnotation.text} updateText={updateText} />
            <br />
            <label htmlFor="memo">Memo</label>
            <TextField id="memo" text={transientAnnotation.memo} updateText={updateMemo} />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(CreateAnnotationOverlay))
