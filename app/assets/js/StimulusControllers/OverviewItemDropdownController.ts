import { Controller } from 'stimulus'

export default class OverviewItemDropdownController extends Controller {
    connect() {
        const dropdownToggle = this.element.querySelector('button.dialog-toggle') as HTMLButtonElement
        const dialog = this.element.querySelector('dialog') as HTMLDialogElement

        dialog.addEventListener('click', (e) => {
            e.stopPropagation()
            if (e.target === dialog) {
                dialog.close()
            }
        })

        dropdownToggle.addEventListener('click', (e) => {
            dialog.showModal()

            const menu = dialog.querySelector('div') as HTMLDivElement
            const rect = dropdownToggle.getBoundingClientRect()

            // eslint-disable-next-line functional/immutable-data
            menu.style.top = `${rect.bottom + 8}px`
            // eslint-disable-next-line functional/immutable-data
            menu.style.left = `${rect.left + 8 - menu.getBoundingClientRect().width}px`
        })
    }
}
