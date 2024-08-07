import { toast, TypeOptions } from 'react-toastify'

export function isMobile() {
    // TODO: is there something more robust?
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(window.navigator.userAgent)
}

// TODO: What is "Ext"?
export function getExt(url: string): string | undefined {
    if (url.includes('?')) {
        return getExt(url.split('?')[0])
    }

    if (url.includes('#')) {
        return getExt(url.split('#')[0])
    }

    // eslint-disable-next-line
    return url.trim().toLowerCase().split('.').pop()
}

export function notify(text = '', type: TypeOptions = 'info') {
    // info success warning error default

    // FIXME
    // @ts-ignore disable-line
    const toastFn = toast[type]

    return (
        text.trim() &&
        toastFn(text, {
            position: 'top-center',
            autoClose: 2000,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
        })
    )
}

// TODO: Unused.
// TODO: Deprecated "keyCode"
// TODO: Why check for element type and editable?
export function getKeyCode(event: KeyboardEvent): number | undefined {
    const tag = document?.activeElement?.tagName.toUpperCase()
    const editable = document?.activeElement?.getAttribute('contenteditable')
    if (tag !== 'INPUT' && tag !== 'TEXTAREA' && editable !== '' && editable !== 'true') {
        return Number(event.keyCode)
    }
}

export const clamp = (value: number, min: number, max: number) => Math.min(Math.max(min, value), max)
