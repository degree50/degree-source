import React, { FC, ReactNode } from 'react'
import CloseButton from './OverlayContainer/CloseButton'
import { useModal, useOverlay } from '@react-aria/overlays'
import { useDialog } from '@react-aria/dialog'

type Props = {
    closeCallback: () => void
    children: React.ReactNode
    title: string
    fullWidth?: boolean
    footerContent?: ReactNode
}

const Overlay: FC<Props> = (props) => {
    // Handle interacting outside the dialog and pressing
    // the Escape key to close the modal.
    const overlayRef: React.RefObject<HTMLDivElement> = React.useRef(null)
    const { overlayProps } = useOverlay(
        {
            isOpen: true,
            isDismissable: true,
            onClose: props.closeCallback,
        },
        overlayRef
    )

    const { modalProps } = useModal()
    const { dialogProps } = useDialog({ role: 'dialog' }, overlayRef)

    return (
        <div className="video-editor__overlay" aria-labelledby="overlay-title">
            <div
                className={`video-editor__overlay__wrapper ${
                    props.fullWidth ? 'video-editor__overlay__wrapper--fullWidth' : ''
                }`}
                ref={overlayRef}
                {...overlayProps}
                {...dialogProps}
                {...modalProps}
            >
                <header className="video-editor__overlay__header">
                    <h3 id="overlay-title">{props.title}</h3>
                    <CloseButton onClick={props.closeCallback} />
                </header>
                <main className="video-editor__overlay__content">{props.children}</main>
                {props.footerContent !== undefined && (
                    <footer className="video-editor__overlay__footer">{props.footerContent}</footer>
                )}
            </div>
        </div>
    )
}

export default React.memo(Overlay)
