import { memo, useState } from 'react'
import Overlay from '../../components/Overlay'
import {
    actions as videoEditorActions,
    selectors as videoEditorSelectors,
    VideoEditorState,
} from 'Components/VideoEditor/VideoEditorSlice'
import TimeInput from '../../../TimeInput'
import {
    ConfigStateSlice,
    selectors as configSelectors,
} from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import Button from '../../../Button/Button'
import { connect } from 'react-redux'
import { secondToTime, timeToSecond } from 'Components/VideoEditor/utils/time'

export const SetVideoPlayerTimeOverlayId = 'SET_VIDEO_PLAYER_TIME'

const mapStateToProps = (state: VideoEditorState & ConfigStateSlice) => ({
    currentTime: videoEditorSelectors.player.selectSyncPlayPosition(state),
    duration: configSelectors.selectVideos(state)[0].duration,
})

const mapDispatchToProps = {
    closeOverlay: videoEditorActions.overlay.unsetOverlay,
    setPlayerTime: videoEditorActions.player.setPlayPosition,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const SetVideoPlayerTimeOverlay = (props: Props) => {
    const [time, setTime] = useState(secondToTime(props.currentTime))
    const maxAllowedTime = secondToTime(props.duration)
    const minAllowedTime = '00:00:00.000'

    const close = () => {
        props.closeOverlay(SetVideoPlayerTimeOverlayId)
    }

    const handleCommit = () => {
        props.setPlayerTime(timeToSecond(time))
        close()
    }

    const footer = (
        <>
            <Button className="button button--type-outline-primary" onPress={close} title="Abbrechen">
                Abbrechen
            </Button>
            <Button
                className="button button--type-primary"
                onPress={handleCommit}
                title="Zu eingegebener Zeit springen"
            >
                Zu Zeit Springen
            </Button>
        </>
    )

    return (
        <Overlay closeCallback={close} title="Zu Zeitstempel springen" footerContent={footer}>
            <TimeInput
                label="Zeiteingabe"
                value={time}
                onChange={setTime}
                max={maxAllowedTime}
                min={minAllowedTime}
                hoursLabel="Stunden"
                minutesLabel="Minuten"
                secondsLabel="Sekunden"
                showMs={false}
            />
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(SetVideoPlayerTimeOverlay))
