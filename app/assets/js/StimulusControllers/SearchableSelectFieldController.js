import {Controller} from 'stimulus'

/**
 * SearchableSelectFieldController
 *
 * This controller is used to make a list of checkboxes searchable.
 * Used to filter the options for students/teachers when editing a course.
 */
export default class extends Controller {
    connect() {
        const searchableSelectField = this.data.element
        const searchField = searchableSelectField.querySelector('.searchable-select-field__input')
        const searchList = searchableSelectField.querySelector('.searchable-select-field__list')
        // on search field change
        searchField.oninput = (event) => {
            const searchValue = event.target.value
            if (searchValue === '') {
                // show all checkboxes
                searchList.querySelectorAll('.form-check').forEach((checkboxDiv) => {
                    checkboxDiv.style.display = 'block'
                })
            }

            // find all matching checkboxes by label
            searchList.querySelectorAll('.form-check label').forEach((label) => {
                const labelValue = label.innerText
                if (labelValue.toLowerCase().includes(searchValue.toLowerCase())) {
                    label.parentElement.style.display = 'block'
                } else {
                    label.parentElement.style.display = 'none'
                }
            })
        }
    }
}
