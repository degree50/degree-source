import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { CutOverlayIds } from '../CuttingMenu'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import TimeInput from 'Components/TimeInput'
import Overlay from '../../components/Overlay'
import TextField from 'Components/VideoEditor/components/TextField'
import Button from 'Components/Button/Button'
import { useCutEdit } from './useCutEdit'
import { selectors as configSelectors } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import { ShortCutId } from '../../ShortCutsContext/ShortCutsSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { secondToTime } from 'Components/VideoEditor/utils/time'

const mapStateToProps = (state: AppState) => {
    const currentlyEditedElementId = selectors.videoEditor.overlay.currentlyEditedElementId(state)
    const cutsById = selectors.data.cuts.selectById(state)
    const cut = currentlyEditedElementId ? cutsById[currentlyEditedElementId] : undefined
    const duration = configSelectors.selectVideos(state)[0].duration
    const currentTime = selectors.videoEditor.player.selectSyncPlayPosition(state)

    return {
        cut,
        duration,
        currentTime,
    }
}

const mapDispatchToProps = {
    updateCut: actions.data.cuts.update,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

// TODO this should probably be consolidated into a single component with the CreateCutOverlay
const EditCutOverlay: FC<Props> = (props) => {
    const {
        transientCut,
        handleStartTimeChange,
        handleEndTimeChange,
        updateText,
        updateMemo,
        minAllowedStartTime,
        maxAllowedStartTime,
        minAllowedEndTime,
        maxAllowedEndTime,
    } = useCutEdit(props.duration, props.cut)

    const close = () => {
        props.closeOverlay(CutOverlayIds.edit)
    }

    if (!transientCut) {
        close()
        return null
    }

    const handleSave = () => {
        props.updateCut({ transientCut })
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
        <Overlay closeCallback={close} title="Schnitt bearbeiten" footerContent={footerContent}>
            <div className="time-input-wrapper">
                <TimeInput
                    label="Start"
                    hoursLabel="Start Stunden"
                    minutesLabel="Start Minuten"
                    secondsLabel="Start Sekunden"
                    value={transientCut.start}
                    min={minAllowedStartTime}
                    max={maxAllowedStartTime}
                    onChange={handleStartTimeChange}
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
                    hoursLabel="Ende Stunden"
                    minutesLabel="End Minuten"
                    secondsLabel="Ende Sekunden"
                    value={transientCut.end}
                    min={minAllowedEndTime}
                    max={maxAllowedEndTime}
                    onChange={handleEndTimeChange}
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
            <label htmlFor="text">Beschreibung</label>
            <TextField id="text" text={transientCut.text} updateText={updateText} />
            <br />
            <label htmlFor="memo">Memo</label>
            <TextField id="memo" text={transientCut.memo} updateText={updateMemo} />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(EditCutOverlay))
