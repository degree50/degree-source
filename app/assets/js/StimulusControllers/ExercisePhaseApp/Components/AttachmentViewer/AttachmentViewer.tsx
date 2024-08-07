import React from 'react'
import { connect } from 'react-redux'
import { ConfigStateSlice, selectors } from '../Config/ConfigSlice'
import PDFViewer from 'pdf-viewer-reactjs'
import { AttachmentViewerStateSlice, selectActiveAttachment, setActiveAttachment } from './AttachmentViewerSlice'
import Button from 'Components/Button/Button'

const mapStateToProps = (state: ConfigStateSlice & AttachmentViewerStateSlice) => ({
    config: selectors.selectConfig(state),
    activeAttachment: selectActiveAttachment(state),
})

const mapDispatchToProps = {
    setActiveAttachment: setActiveAttachment,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

export type Attachment = {
    id: string
    name: string
    type: string
    url: string
}

const mapAttachmentTypeToIcon = (type: Attachment['type']) => {
    if (type.startsWith('audio/')) {
        return 'file-audio'
    }

    if (type.startsWith('video/')) {
        return 'file-video'
    }

    if (type.startsWith('image/')) {
        return 'file-image'
    }

    switch (type) {
        case 'application/pdf':
            return 'file-pdf'
        case 'application/msword':
            return 'file-word'
        case 'application/msexcel':
            return 'file-excel'
        case 'application/mspowerpoint':
            return 'file-powerpoint'
        default:
            return 'file'
    }
}

const AttachmentViewer: React.FC<Props> = (props) => {
    const attachmentTiles = props.config.attachments.map(function (attachment: Attachment) {
        return (
            <button
                key={attachment.id}
                className={'button button--type-primary tile tile--small'}
                title={attachment.name}
                aria-label={attachment.name}
                onClick={() => props.setActiveAttachment(attachment)}
            >
                <div className={'tile__content'}>
                    <i className={`tile__icon fas fa-${mapAttachmentTypeToIcon(attachment.type)}`}></i>
                    <span className="tile__title">{attachment.name}</span>
                </div>
            </button>
        )
    })

    return (
        <div className={'attachment-viewer'}>
            {props.activeAttachment ? (
                <div className={'attachment-viewer'}>
                    <header className={'attachment-viewer__header'}>
                        <h4>{props.activeAttachment.name}</h4>
                    </header>

                    <div className={'attachment-viewer__actions'}>
                        <Button
                            className={'button button--size-small button--type-outline-primary'}
                            title="Zurück zur Attachment-Übersicht"
                            onPress={() => props.setActiveAttachment(undefined)}
                        >
                            <i className={'fas fa-chevron-left'}></i>
                            <span>Zurück</span>
                        </Button>
                        <a
                            className={'button button--size-small button--type-primary'}
                            href={props.activeAttachment.url}
                            aria-label="Download"
                            title="Download"
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className={'fas fa-download'}></i>
                            <span>Download</span>
                        </a>
                    </div>

                    {props.activeAttachment.type === 'application/pdf' ? (
                        <PDFViewer
                            document={{
                                url: props.activeAttachment.url,
                            }}
                            hideZoom={true}
                            hideRotation={true}
                            css="pdf-viewer"
                            loader={
                                <h2 className={'pdf-viewer__loader'}>
                                    <i className={'fas fa-spinner fa-spin'}></i>
                                </h2>
                            }
                            alert={() => (
                                <div className={'pdf-viewer__error'}>
                                    <h4>Dokument konnte nicht geladen werden</h4>
                                </div>
                            )}
                            navigation={{
                                css: {
                                    navbarWrapper: 'pdf-viewer__navbar',
                                    previousPagebutton:
                                        'button button--size-small button--type-primary pdf-viewer__prev-page',
                                    pageIndicator: 'pdf-viewer__pages',
                                    nextPagebutton:
                                        'button button--size-small button--type-primary pdf-viewer__next-page',
                                },
                            }}
                        />
                    ) : null}
                </div>
            ) : (
                <div className={'tiles'}>
                    {attachmentTiles.length > 0 ? attachmentTiles : 'Kein Anhang zur Verfügung'}
                </div>
            )}
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(AttachmentViewer)
