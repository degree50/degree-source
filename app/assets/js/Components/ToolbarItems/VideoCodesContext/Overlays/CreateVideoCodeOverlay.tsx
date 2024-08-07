import { FC, memo } from 'react'
import { connect } from 'react-redux'
import { generate } from 'shortid'
import TimeInput from 'Components/TimeInput'
import TextField from 'Components/VideoEditor/components/TextField'
import VideoCodeSelection from './VideoCodePrototypeSelection'
import Button from 'Components/Button/Button'
import { secondToTime } from 'Components/VideoEditor/utils/time'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import { useVideoCodeEdit } from './useVideoCodeEdit'
import { VideoCode } from 'Components/VideoEditor/types'
import { ShortCutId } from '../../ShortCutsContext/ShortCutsSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = (state: AppState) => ({
    currentTime: selectors.videoEditor.player.selectSyncPlayPosition(state),
    duration: selectors.config.selectVideos(state)[0].duration,
    prototypes: selectors.data.selectVideoCodePrototypesOfCurrentSolutionFlattened(state),
    currentSolutionId: selectors.data.solutions.selectCurrentId(state),
})

const mapDispatchToProps = {
    appendVideoCode: actions.data.videoCodes.append,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CreateVideoCodeOverlay: FC<Props> = (props) => {
    const { currentTime, duration } = props
    const initialPrototypeId = props.prototypes[0]?.id

    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.create)
    }

    // transient videoCode
    // current as start
    // some default delta for end
    const initialVideoCode: VideoCode = {
        id: generate(),
        start: secondToTime(currentTime),
        end: secondToTime(Math.min(currentTime + duration / 10, duration)),
        text: '',
        memo: '',
        color: null,
        idFromPrototype: initialPrototypeId,
        solutionId: props.currentSolutionId,
    }

    const {
        transientVideoCode,
        handleStartTimeChange,
        handleEndTimeChange,
        handleMemoChange,
        updateSelectedCode,
        minAllowedStartTime,
        maxAllowedStartTime,
        minAllowedEndTime,
        maxAllowedEndTime,
    } = useVideoCodeEdit(duration, initialVideoCode)

    if (props.prototypes.length < 1) {
        return (
            <Overlay closeCallback={close} title="Neue Codierung">
                <p tabIndex={0}>Keine Codes vorhanden!</p>
            </Overlay>
        )
    }

    if (!transientVideoCode) {
        close()
        return null
    }

    const handleSave = () => {
        props.appendVideoCode(transientVideoCode)
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
            <Button className="button button--type-outline-primary" onPress={close} title="Codierung Verwerfen">
                Verwerfen
            </Button>
            <Button className="button button--type-primary" onPress={handleSave} title="Codierung Speichern">
                Speichern
            </Button>
        </>
    )

    return (
        <Overlay closeCallback={close} title="Neue Codierung" footerContent={footerContent}>
            <div className="time-input-wrapper">
                <TimeInput
                    label="Start"
                    hoursLabel="Start Stunden"
                    minutesLabel="Start Minuten"
                    secondsLabel="Start Sekunden"
                    value={transientVideoCode.start}
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
                    value={transientVideoCode.end}
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
            <VideoCodeSelection
                defaultPrototypeId={initialPrototypeId}
                onSelect={updateSelectedCode}
                selectedPrototypeId={transientVideoCode.idFromPrototype}
            />
            <br />

            <label htmlFor="memo">Memo</label>
            <TextField id="memo" text={transientVideoCode.memo} updateText={handleMemoChange} />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(CreateVideoCodeOverlay))
