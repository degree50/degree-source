import { FC, memo } from 'react'
import { connect } from 'react-redux'
import TimeInput from 'Components/TimeInput'
import TextField from 'Components/VideoEditor/components/TextField'
import Button from 'Components/Button/Button'
import VideoCodeSelection from './VideoCodePrototypeSelection'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { VideoCodeOverlayIds } from '../VideoCodesMenu'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { useVideoCodeEdit } from './useVideoCodeEdit'
import { selectors as configSelectors } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import { ShortCutId } from '../../ShortCutsContext/ShortCutsSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { secondToTime } from 'Components/VideoEditor/utils/time'
import Overlay from 'Components/ToolbarItems/components/Overlay'

const mapStateToProps = (state: AppState) => {
    const currentlyEditedElementId = selectors.videoEditor.overlay.currentlyEditedElementId(state)
    const videoCodesById = selectors.data.videoCodes.selectById(state)
    const videoCode = currentlyEditedElementId ? videoCodesById[currentlyEditedElementId] : undefined
    const duration = configSelectors.selectVideos(state)[0].duration
    const currentTime = selectors.videoEditor.player.selectSyncPlayPosition(state)

    return {
        videoCode,
        prototypes: selectors.data.selectVideoCodePrototypesOfCurrentSolutionFlattened(state),
        duration,
        currentTime,
    }
}

const mapDispatchToProps = {
    updateVideoCode: actions.data.videoCodes.update,
    closeOverlay: actions.videoEditor.overlay.unsetOverlay,
    syncSolution: syncSolutionAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

// TODO this should probably be consolidated into a single component with the CreateVideoCodeOverlay
const EditVideoCodeOverlay: FC<Props> = (props) => {
    const defaultPrototypeId = props.prototypes[0].id

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
    } = useVideoCodeEdit(props.duration, props.videoCode)

    const close = () => {
        props.closeOverlay(VideoCodeOverlayIds.edit)
    }

    if (!transientVideoCode) {
        close()
        return null
    }

    const handleSave = () => {
        props.updateVideoCode({ transientVideoCode })
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
        <Overlay closeCallback={close} title="Codierung bearbeiten" footerContent={footerContent}>
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
                    className="button button--type-outline-primary"
                    onPress={handleUseCurrentTimeForStartValue}
                    title={'Aktuelle Zeit als Startzeit übernehmen'}
                    data-short-cut-id={ShortCutId.SET_CURRENT_TIME_AS_START_VALUE}
                >
                    <i className="fas fa-stopwatch" />
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
                    className="button button--type-outline-primary"
                    onPress={handleUseCurrentTimeForEndValue}
                    title={'Aktuelle Zeit als Endzeit übernehmen'}
                    data-short-cut-id={ShortCutId.SET_CURRENT_TIME_AS_END_VALUE}
                >
                    <i className="fas fa-stopwatch" />
                </Button>
            </div>
            <hr />
            <VideoCodeSelection
                defaultPrototypeId={defaultPrototypeId}
                onSelect={updateSelectedCode}
                selectedPrototypeId={transientVideoCode.idFromPrototype}
            />
            <br />

            <label htmlFor="memo">Memo</label>
            <TextField id="memo" text={transientVideoCode.memo} updateText={handleMemoChange} />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(EditVideoCodeOverlay))
