import React from 'react'
import { useOverlay, usePreventScroll, useModal, OverlayContainer } from '@react-aria/overlays'
import { useDialog } from '@react-aria/dialog'
import { FocusScope } from '@react-aria/focus'
import Button from 'Components/Button/Button'

type Props = {
    children: React.ReactChild
    title: string
    isVisible: boolean
    closeModal: () => void
}

const Modal = (props: Props) => {
    // Handle interacting outside the dialog and pressing
    // the Escape key to close the modal.
    const modalRef: React.RefObject<HTMLDivElement> = React.useRef(null)
    const { overlayProps } = useOverlay(
        {
            isOpen: props.isVisible,
            isDismissable: true,
            onClose: props.closeModal,
        },
        modalRef
    )

    // Prevent scrolling while the modal is open, and hide content
    // outside the modal from screen readers.
    usePreventScroll()
    const { modalProps } = useModal()
    const { dialogProps } = useDialog({ role: 'dialog' }, modalRef)

    // react-aria places the modal at the bottom of the body
    return (
        <OverlayContainer>
            <div className="modal" aria-labelledby="modal-title">
                <FocusScope contain restoreFocus autoFocus>
                    <div className={'modal__inner'} ref={modalRef} {...overlayProps} {...dialogProps} {...modalProps}>
                        <header className={'modal__header'}>
                            <h3 id="modal-title">{props.title}</h3>
                        </header>
                        <div className={'modal__content'}>{props.children}</div>
                        <footer className={'modal__footer'}>
                            <Button onPress={props.closeModal} className={'button button--type-primary'}>
                                Schließen
                            </Button>
                        </footer>
                    </div>
                </FocusScope>
            </div>
        </OverlayContainer>
    )
}

export default React.memo(Modal)
