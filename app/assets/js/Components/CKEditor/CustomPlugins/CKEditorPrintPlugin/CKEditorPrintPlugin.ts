import Plugin from '@ckeditor/ckeditor5-core/src/plugin'
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview'
// @ts-ignore -- WHY: typings missing
import faPrint from '@fortawesome/fontawesome-pro/svgs/regular/print.svg'
import type { Locale } from '@ckeditor/ckeditor5-utils'

// WHY: each editor will use its own iframe for printing
const createPrintIframe = () => {
    const iframe = document.createElement('iframe')
    // a11y
    iframe.setAttribute('aria-hidden', 'true')
    iframe.setAttribute('tabindex', '-1')
    // styles
    iframe.classList.add('ckeditor__print-plugin--iframe')

    return iframe
}

export default class CKEditorPrintPlugin extends Plugin {
    iframe: HTMLIFrameElement = createPrintIframe()

    init() {
        document.body.appendChild(this.iframe)
        const ui = this.editor.ui

        ui.componentFactory.add('print', (locale: Locale) => {
            const view = new ButtonView(locale)

            view.set({
                label: 'Dokument Drucken',
                icon: faPrint,
                tooltip: true,
            })

            // Callback executed once the image is clicked.
            view.on('execute', () => {
                // prepare all css we have on the site to be added to the iframe document
                const styleLinkElementsAsString = [...document.querySelectorAll('link')]
                    .filter((linkElement) => linkElement.href.endsWith('.css'))
                    .map((linkElement) => `<link rel="stylesheet" href="${linkElement.href}" type="text/css">`)
                    .join('')

                // add editor content to iframe document body
                // eslint-disable-next-line functional/immutable-data
                this.iframe.srcdoc =
                    `<html lang="de"><head><title>${document.title}</title>${styleLinkElementsAsString}</head><body class="ck-content">` +
                    this.editor.data.get() +
                    "<script>window.addEventListener( 'DOMContentLoaded', () => { window.print(); } );</script></body></html>"
            })

            return view
        })
    }
}
