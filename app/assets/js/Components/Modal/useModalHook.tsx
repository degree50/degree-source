import React from 'react'
import { useOverlayTriggerState } from '@react-stately/overlays'

import Modal from './Modal'

// Renders a modal to the modal root and handles the visibility state
// of this modal.
//
// NOTE: Each modal you want to render should use a separate hook!!!
// Otherwise, your modals will share their visibility state which might lead
// to overlapping and un-closable elements.
export const useModalHook = () => {
    const state = useOverlayTriggerState({})

    const showModal = () => state.open()
    const hideModal = () => state.close()

    const RenderModal = ({ children, title }: { children: React.ReactChild; title: string }) => (
        <React.Fragment>
            {state.isOpen && (
                <Modal closeModal={hideModal} isVisible title={title}>
                    {children}
                </Modal>
            )}
        </React.Fragment>
    )

    return {
        showModal,
        hideModal,
        RenderModal,
    }
}
