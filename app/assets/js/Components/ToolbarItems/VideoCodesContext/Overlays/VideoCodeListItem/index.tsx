import Button from 'Components/Button/Button'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { memo } from 'react'
import { connect } from 'react-redux'
import PrototypeInformation from './PrototypeInformation'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { t2d } from 'duration-time-conversion'
import { getColorName } from 'ntc-ts'
import End from 'Components/VideoEditor/components/End'
import Start from 'Components/VideoEditor/components/Start'
import { VideoCodeOverlayIds } from '../../VideoCodesMenu'
import { VideoCodeId } from '../../VideoCodesSlice'
import CopyToClipboard from 'react-copy-to-clipboard'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { videoCodeAsRichtext } from 'Components/VideoEditor/composedSelectors/videoCodes'

type OwnProps = {
    videoCodeId: VideoCodeId
    index: number
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => {
    const item = selectors.data.videoCodes.selectVideoCodeById(state, ownProps)
    const canEdit = selectors.selectUserCanEditSolution(state, {
        solutionId: item.solutionId,
    })
    const videoCodePrototype = item.idFromPrototype
        ? selectors.data.videoCodePrototypes.selectPrototypeById(state, {
              videoCodeId: item.idFromPrototype,
          })
        : undefined

    const parentVideoCodePrototype = videoCodePrototype?.parentId
        ? selectors.data.videoCodePrototypes.selectPrototypeById(state, {
              videoCodeId: videoCodePrototype.parentId,
          })
        : undefined

    const isFromGroupPhase = selectors.data.solutions.selectSolutionFromGroupPhase(state, item.solutionId)

    return {
        item,
        canEdit,
        videoCodePrototype,
        parentVideoCodePrototype,
        isFromCurrentSolution: selectors.data.selectVideoCodeIsFromCurrentSolution(state, ownProps),
        creatorName: selectors.data.selectCreatorNameForVideoCode(state, ownProps),
        phaseType: selectors.config.selectPhaseType(state),
        isFromGroupPhase,
        currentSolutionId: selectors.data.solutions.selectCurrentId(state),
    }
}

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
    setCurrentlyEditedElementId: actions.videoEditor.overlay.setCurrentlyEditedElementId,
    setPlayPosition: actions.videoEditor.player.setPlayPosition,
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const VideoCodeListItem = (props: Props) => {
    const {
        item,
        setCurrentlyEditedElementId,
        setOverlay,
        videoCodePrototype,
        index,
        parentVideoCodePrototype,
        phaseType,
        isFromGroupPhase,
        currentSolutionId,
    } = props

    const isFromPreviousSolution = item.solutionId !== currentSolutionId

    const handleRemove = () => {
        setCurrentlyEditedElementId(item.id)
        setOverlay({ overlayId: VideoCodeOverlayIds.remove, closeOthers: false })
    }

    const handleEdit = () => {
        setCurrentlyEditedElementId(item.id)
        setOverlay({ overlayId: VideoCodeOverlayIds.edit, closeOthers: false })
    }

    const handleJumpToPosition = () => {
        props.setPlayPosition(t2d(item.start))
    }

    const element = `${index + 1}. Element`
    const code = `Code: ${videoCodePrototype?.name ?? 'Kein Code ausgewählt'}`
    const color = `Farbe: ${videoCodePrototype?.color ? getColorName(videoCodePrototype.color).name : ''}`
    const userCreated = `${videoCodePrototype?.userCreated ? 'Selbsterstellter Code' : 'Vordefinierter Code'}`
    const subCode = `${parentVideoCodePrototype ? `Unter-Code von: ${parentVideoCodePrototype.name}` : ''}`
    const creatorDescription = `Codierung ${isFromPreviousSolution ? 'aus Lösung' : ''} von: ${
        isFromGroupPhase ? 'Gruppe von ' : ''
    }${props.creatorName}`
    const start = `Von: ${item.start}`
    const end = `Bis: ${item.end}`
    const memo = `${item.memo.length > 0 ? `Memo: ${item.memo}` : ''}`

    /**
     * WHY:
     * This will be read by the screen reader when the list element if focused (when tabbing through list).
     *
     * Example:
     * "
     *   1. Element
     *
     *   Code: Gelungener Einsatz von Medien.
     *   Farbe: Grün.
     *   Vordefinierter Video Code.
     *
     *   Codierung von student@sandstorm.de
     *
     *   Von: 00:00:00
     *   Bis: 00:04:20
     *
     *   Memo: Gut gemacht.
     * "
     * oder
     * "
     *   2. Element
     *
     *   Code: Smart Whiteboard.
     *   Farbe: Grün.
     *   Selbsterstellter Video Code.
     *   Unter-Code von Gelungener Einsatz von Medien.
     *
     *   Codierung von student2@sandstorm.de
     *
     *   Von: 00:01:23
     *   Bis: 00:02:42
     *
     *   Memo: Klasse Einsatz des Smart Whiteboards.
     * "
     */
    const ariaLabel = `
        ${element}

        ${code}
        ${color}
        ${userCreated}
        ${subCode}

        ${creatorDescription}

        ${start}
        ${end}

        ${memo}
    `

    const asRichText = videoCodeAsRichtext({
        videoCode: item,
        videoCodePrototype,
        parentVideoCodePrototype,
        creatorName: props.creatorName,
        isFromPreviousSolution: !props.isFromCurrentSolution,
    })

    return (
        <li tabIndex={0} aria-label={ariaLabel}>
            <PrototypeInformation videoCodePrototype={videoCodePrototype} />
            <p>{creatorDescription}</p>
            <Start start={item.start} />
            <End end={item.end} />
            {item.memo.length > 0 && <p>Memo: {item.memo}</p>}

            <div className="button-group">
                {phaseType === ExercisePhaseTypesEnum.MATERIAL ? (
                    <CopyToClipboard
                        text={asRichText}
                        options={{
                            format: 'text/plain',
                        }}
                    >
                        <Button
                            className="button button--type-outline-primary button--size-small"
                            title="In Zwischenablage kopieren"
                        >
                            In Zwischenablage kopieren
                        </Button>
                    </CopyToClipboard>
                ) : (
                    <Button
                        className="button button--type-outline-primary button--size-small"
                        onPress={handleJumpToPosition}
                        title="Springe zu Position im Video"
                    >
                        Springe zu Position
                    </Button>
                )}

                {props.isFromCurrentSolution && (
                    <>
                        <Button
                            className="button button--type-danger button--size-small"
                            onPress={handleRemove}
                            title="Codierung Löschen"
                        >
                            Löschen
                        </Button>

                        <Button
                            className="button button--type-primary button--size-small"
                            onPress={handleEdit}
                            title="Codierung Bearbeiten"
                        >
                            Bearbeiten
                        </Button>
                    </>
                )}
            </div>
        </li>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(VideoCodeListItem))
