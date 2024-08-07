import Button from 'Components/Button/Button'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { memo } from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { t2d } from 'duration-time-conversion'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import End from 'Components/VideoEditor/components/End'
import Start from 'Components/VideoEditor/components/Start'
import { AnnotationOverlayIds } from '../AnnotationsMenu'
import { AnnotationId } from '../AnnotationsSlice'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import CopyToClipboard from 'react-copy-to-clipboard'
import { annotationWithCreatorNameAsRichtext } from 'Components/VideoEditor/composedSelectors/annotations'

type OwnProps = {
    annotationId: AnnotationId
    index: number
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => {
    const item = selectors.data.annotations.selectAnnotationById(state, ownProps)
    const canEdit = selectors.selectUserCanEditSolution(state, {
        solutionId: item.solutionId,
    })
    const isFromGroupPhase = selectors.data.solutions.selectSolutionFromGroupPhase(state, item.solutionId)

    return {
        item,
        canEdit,
        isFromCurrentSolution: selectors.data.selectAnnotationIsFromCurrentSolution(state, ownProps),
        creatorName: selectors.data.selectCreatorNameForAnnotation(state, ownProps),
        phaseType: selectors.config.selectPhaseType(state),
        isFromGroupPhase,
    }
}

const mapDispatchToProps = {
    setOverlay: actions.videoEditor.overlay.setOverlay,
    setCurrentlyEditedElementId: actions.videoEditor.overlay.setCurrentlyEditedElementId,
    setPlayPosition: actions.videoEditor.player.setPlayPosition,
}

const connector = connect(mapStateToProps, mapDispatchToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux & OwnProps

const AnnotationListItem = (props: Props) => {
    const { item, index, setCurrentlyEditedElementId, setOverlay, phaseType, isFromGroupPhase } = props

    const isFromPreviousSolution = !props.isFromCurrentSolution

    const handleRemove = () => {
        setCurrentlyEditedElementId(item.id)
        setOverlay({ overlayId: AnnotationOverlayIds.remove, closeOthers: false })
    }

    const handleEdit = () => {
        setCurrentlyEditedElementId(item.id)
        setOverlay({ overlayId: AnnotationOverlayIds.edit, closeOthers: false })
    }

    const handleJumpToPosition = () => {
        props.setPlayPosition(t2d(item.start))
    }

    const element = `${index + 1}. Element`
    const description = `Annotationstext: ${item.text}`
    const creatorDescription = `Annotation ${isFromPreviousSolution ? 'aus Lösung' : ''} von: ${
        isFromGroupPhase ? 'Gruppe von ' : ''
    }${props.creatorName}`
    const start = `Von: ${item.start}`
    const end = `Bis: ${item.end}`
    const memo = `${item.memo.length > 0 ? `Memo: ${item.memo}` : ''}`

    const ariaLabel = `
        ${element}

        ${description}
        Annotationstext zu Ende.

        ${creatorDescription}

        ${start}
        ${end}

        ${memo}
    `

    const asRichtext = annotationWithCreatorNameAsRichtext({
        ...item,
        creatorName: props.creatorName,
        isFromPreviousSolution,
    })

    return (
        <li className="annotation-list-item" tabIndex={0} aria-label={ariaLabel} data-focus-id={item.id}>
            <p>Annotationstext: {item.text}</p>
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

                {props.isFromCurrentSolution && (
                    <>
                        <Button
                            className="button button--type-danger button--size-small"
                            onPress={handleRemove}
                            title="Annotation Löschen"
                        >
                            Löschen
                        </Button>
                        <Button
                            className="button button--type-primary button--size-small"
                            onPress={handleEdit}
                            title="Annotation Bearbeiten"
                        >
                            Bearbeiten
                        </Button>
                    </>
                )}
            </div>
        </li>
    )
}

export default connector(memo(AnnotationListItem))
