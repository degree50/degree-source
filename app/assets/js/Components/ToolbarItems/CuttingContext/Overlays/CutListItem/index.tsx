import Button from 'Components/Button/Button'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { FC, memo } from 'react'
import { connect } from 'react-redux'
import PositionControls from './PositionControls'
import { syncSolutionAction } from 'StimulusControllers/ExercisePhaseApp/Components/Solution/SolutionSaga'
import { t2d } from 'duration-time-conversion'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import End from 'Components/VideoEditor/components/End'
import Start from 'Components/VideoEditor/components/Start'
import { CutOverlayIds } from '../../CuttingMenu'
import { CutId } from '../../CuttingSlice'
import CopyToClipboard from 'react-copy-to-clipboard'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { cutAsRichtext } from 'Components/VideoEditor/composedSelectors/cuts'

type OwnProps = {
    cutId: CutId
    index: number
    showPositionControls?: boolean
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => {
    const item = selectors.data.cuts.selectCutById(state, ownProps)
    const canEdit = selectors.selectUserCanEditSolution(state, {
        solutionId: item.solutionId,
    })

    const isFromGroupPhase = selectors.data.solutions.selectSolutionFromGroupPhase(state, item.solutionId)

    return {
        item,
        canEdit,
        creatorName: selectors.data.selectCreatorNameForCut(state, ownProps),
        phaseType: selectors.config.selectPhaseType(state),
        isFromGroupPhase,
        isFromCurrentSolution: selectors.data.selectCutIsFromCurrentSolution(state, ownProps),
    }
}

const mapDispatchToProps = {
    moveUp: actions.data.solutions.moveCutUp,
    moveDown: actions.data.solutions.moveCutDown,
    setOverlay: actions.videoEditor.overlay.setOverlay,
    setCurrentlyEditedElementId: actions.videoEditor.overlay.setCurrentlyEditedElementId,
    syncSolution: syncSolutionAction,
    setPlayPosition: actions.videoEditor.player.setPlayPosition,
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const CutListItem: FC<Props> = (props) => {
    const { item, phaseType, index, isFromGroupPhase } = props

    const isFromPreviousSolution = !props.isFromCurrentSolution

    const handleRemove = () => {
        props.setCurrentlyEditedElementId(item.id)
        props.setOverlay({ overlayId: CutOverlayIds.remove, closeOthers: false })
    }

    const handleEdit = () => {
        props.setCurrentlyEditedElementId(item.id)
        props.setOverlay({ overlayId: CutOverlayIds.edit, closeOthers: false })
    }

    const handleMoveUp = () => {
        props.moveUp(item.id)
        props.syncSolution()
    }

    const handleMoveDown = () => {
        props.moveDown(item.id)
        props.syncSolution()
    }

    const handleJumpToPosition = () => {
        props.setPlayPosition(t2d(item.start))
    }

    const element = `${index + 1}. Element`
    const creatorDescription = `Schnitt ${isFromPreviousSolution ? 'aus Lösung' : ''} von: ${
        isFromGroupPhase ? 'Gruppe von ' : ''
    }${props.creatorName}`
    const description = `Beschreibung: ${item.text}`
    const start = `Von: ${item.start}`
    const end = `Bis: ${item.end}`
    const memo = `${item.memo.length > 0 ? `Memo: ${item.memo}` : ''}`

    const ariaLabel = `
    ${element}

    ${description}

    ${creatorDescription}

    ${start}
    ${end}

    ${memo}
  `

    const asRichtext = cutAsRichtext({
        cut: item,
        creatorName: props.creatorName,
        isFromPreviousSolution,
    })

    return (
        <li className="cut-list-item" tabIndex={0} aria-label={ariaLabel} data-focus-id={item.id}>
            <p>Beschreibung: {item.text}</p>
            <p>{creatorDescription}</p>
            <Start start={item.start} />
            <End end={item.end} />
            <br />
            {item.memo.length > 0 && <p>Memo: {item.memo}</p>}

            <div className="button-group">
                {phaseType === ExercisePhaseTypesEnum.MATERIAL ? (
                    <CopyToClipboard
                        text={asRichtext}
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

                {props.showPositionControls && <PositionControls moveUp={handleMoveUp} moveDown={handleMoveDown} />}

                {props.canEdit && (
                    <>
                        <Button
                            className="button button--type-danger button--size-small"
                            onPress={handleRemove}
                            title="Schnitt Löschen"
                        >
                            Löschen
                        </Button>
                        <Button
                            className="button button--type-primary button--size-small"
                            onPress={handleEdit}
                            title="Schnitt Bearbeiten"
                        >
                            Bearbeiten
                        </Button>
                    </>
                )}
            </div>
        </li>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(CutListItem))
