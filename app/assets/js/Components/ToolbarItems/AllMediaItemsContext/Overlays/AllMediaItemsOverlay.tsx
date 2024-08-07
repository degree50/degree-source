import AnnotationListItem from 'Components/ToolbarItems/AnnotationsContext/Overlays/AnnotationListItem'
import Overlay from 'Components/ToolbarItems/components/Overlay'
import CutListItem from 'Components/ToolbarItems/CuttingContext/Overlays/CutListItem'
import VideoCodeListItem from 'Components/ToolbarItems/VideoCodesContext/Overlays/VideoCodeListItem'
import { MediaItemTypeEnum } from 'Components/VideoEditor/types'
import React from 'react'
import { connect, ConnectedProps } from 'react-redux'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'
import { AllMediaItemsOverlayIds } from '../AllMediaItemsMenu'
import Button from 'Components/Button/Button'
import CopyToClipboard from 'react-copy-to-clipboard'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'

const mapStateToProps = (state: AppState) => {
    return {
        allMediaItems: selectors.selectAllMediaItemsByStartTime(state),
        allMediaItemsAsRichtext: selectors.selectAllMediaItemsByStartTimeAsRichtext(state),
        isMaterialPhase: selectors.config.selectPhaseType(state) === ExercisePhaseTypesEnum.MATERIAL,
    }
}

const mapDispatchToProps = {
    unsetOverlay: actions.videoEditor.overlay.unsetOverlay,
}

const connector = connect(mapStateToProps, mapDispatchToProps)

type PropsFromRedux = ConnectedProps<typeof connector>

type Props = PropsFromRedux

const AllMediaItemsOverlay = (props: Props) => {
    const { unsetOverlay, allMediaItems, allMediaItemsAsRichtext, isMaterialPhase } = props

    const handleClose = () => {
        unsetOverlay(AllMediaItemsOverlayIds.all)
    }

    return (
        <Overlay
            closeCallback={handleClose}
            title="Alle Annotationen, Codierungen und Schnitte"
            footerContent={
                isMaterialPhase && (
                    <div className="video-editor__media-item-list__footer">
                        <CopyToClipboard
                            text={allMediaItemsAsRichtext}
                            options={{
                                format: 'text/plain',
                            }}
                            onCopy={handleClose}
                        >
                            <Button
                                className="button button--type-outline-primary"
                                title="Alle Elemente in Zwischenablage kopieren"
                            >
                                Alle Elemente in Zwischenablage kopieren
                            </Button>
                        </CopyToClipboard>
                    </div>
                )
            }
        >
            <ol className="video-editor__media-item-list-new">
                {allMediaItems.map((mediaItem, index) => {
                    // WHY:
                    // Our media items do not have an actual unique ID.
                    // Instead, we dynamically build an ID from the exercisePhaseTeamID and the
                    // annotation/code/cut-index. Therefore, an annotation and a code might have
                    // the same ID. By postfixing them with their type we assure, that we don't
                    // have colliding keys.
                    const key = `${mediaItem.id}_${mediaItem.type}`

                    switch (mediaItem.type) {
                        case MediaItemTypeEnum.annotation: {
                            return <AnnotationListItem key={key} annotationId={mediaItem.id} index={index} />
                        }

                        case MediaItemTypeEnum.videoCode: {
                            return <VideoCodeListItem key={key} videoCodeId={mediaItem.id} index={index} />
                        }

                        case MediaItemTypeEnum.cut: {
                            return <CutListItem key={key} cutId={mediaItem.id} index={index} />
                        }

                        default: {
                            return null
                        }
                    }
                })}
            </ol>
        </Overlay>
    )
}

export default connector(React.memo(AllMediaItemsOverlay))
