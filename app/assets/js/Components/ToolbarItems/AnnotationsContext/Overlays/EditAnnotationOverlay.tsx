import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import React, { FC, memo } from 'react'
import { connect } from 'react-redux'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import TimeInput from 'Components/TimeInput'
import { useAnnotationEdit } from './useAnnotationEdit'
import Overlay from '../../components/Overlay'
import TextField from 'Components/VideoEditor/components/TextField'
import Button from 'Components/Button/Button'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { secondToTime } from 'Components/VideoEditor/utils/time'
import { ShortCutId } from 'Components/ToolbarItems/ShortCutsContext/ShortCutsSlice'

const mapStateToProps = (state: AppState) => {
    const currentlyEditedElementId = selectors.videoEditor.overlay.currentlyEditedElementId(state)
    const annotationsById = selectors.data.annotations.selectById(state)
    const annotation = currentlyEditedElementId ? annotationsById[currentlyEditedElementId] : undefined
    const duration = selectors.config.selectVideos(state)[0].duration
    const currentTime = selectors.videoEditor.player.selectSyncPlayPosition(state)

    return {
        annotation,
        duration,
        currentTime,
    }
}

const mapDispatchToProps = {
    updateAnnotation: actions.data.annotations.update,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

// TODO: this should probably be consolidated into a single component with the CreateAnnotationOverlay
const EditAnnotationOverlay: FC<Props> = (props) => {
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
    } = useAnnotationEdit(props.duration, props.annotation)

    const close = () => {
        props.closeOverlay(AnnotationOverlayIds.edit)
    }

    if (!transientAnnotation) {
        close()
        return null
    }

    const handleSave = () => {
        props.updateAnnotation({ transientAnnotation })
        props.syncSolution()
        close()
    }

    const handleUseCurrentTimeForStartValue = () => {
        handleStartTimeChange(secondToTime(props.currentTime))
    }

    const handleUseCurrentTimeForEndValue = () => {
        handleEndTimeChange(secondToTime(props.currentTime))
    }

    const footerContent = (
        <>
            <Button className="button button--type-outline-primary" onPress={close} title="Änderungen Verwerfen">
                Verwerfen
            </Button>
            <Button className="button button--type-primary" onPress={handleSave} title="Änderungen Speichern">
                Speichern
            </Button>
        </>
    )

    return (
        <Overlay closeCallback={close} title="Annotation bearbeiten" footerContent={footerContent}>
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

export default connect(mapStateToProps, mapDispatchToProps)(memo(EditAnnotationOverlay))
