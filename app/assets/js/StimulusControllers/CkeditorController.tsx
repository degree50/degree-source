import { Controller } from 'stimulus'
import ReactDOM from 'react-dom'
import CKEditorStandalone from 'Components/CKEditor/CKEditorStandalone'
class CKEditorController extends Controller {
    connect() {
        /**
         * WHY: We use a form field to pass the value of CKEditor to symfony forms.
         *
         * HINT: The 'required' attribute will still fire, because we do not "remove" the original form field from
         *       DOM or "sight" (e.g. via "visibility: hidden;", "display: none;" or "height: 0;").
         *       The original element will still be responsible for validation this way, but the UX will suggest
         *       to the user, that it is actually the CKEditor.
         *
         * TODO: Once there is a suitable, less hacky, other way to do this -> do it.
         */
        const originalFormField = this.element as HTMLTextAreaElement
        const ckEditorRoot = document.createElement('div')

        // place the editor before the original field
        originalFormField.insertAdjacentElement('beforebegin', ckEditorRoot)

        // pass the server hydrated value into the CKEditor as initial value
        const initialValue = originalFormField.value

        // pass changes to from ckeditor to original form field so that the form can pick it up on submit
        const handleChange = (value: string) => {
            originalFormField.setAttribute('value', value)
        }

        // If for some reason the original form field is being focussed -> focus the ckeditor
        originalFormField.addEventListener('focus', () =>
            getContentEditableElementFromInsideElement(ckEditorRoot)?.focus()
        )

        // Mimic the browser behaviour that set's the focus on an input when it's label element is clicked
        document
            .querySelector<HTMLLabelElement>(`label[for="${originalFormField.id}"]`)
            ?.addEventListener('click', () => {
                getContentEditableElementFromInsideElement(ckEditorRoot)?.focus()
            })

        const handleReady = () => {
            // Add "aria-required" attribute to editable content element from "required" attribute of original form field
            getContentEditableElementFromInsideElement(ckEditorRoot)?.setAttribute('aria-required', 'true')
        }

        originalFormField.classList.add('ckeditor-loaded')

        ReactDOM.render(
            <CKEditorStandalone initialValue={initialValue} onChange={handleChange} onReady={handleReady} />,
            ckEditorRoot
        )
    }
}

export default CKEditorController

/**
 * The ckeditor's main content element has the attribute 'contenteditable="true"'
 *
 * @param element The element in which scope we search for the content editable element we want to focus
 */
function getContentEditableElementFromInsideElement(element: HTMLElement) {
    return element.querySelector<HTMLElement>('[contenteditable="true"]')
}
