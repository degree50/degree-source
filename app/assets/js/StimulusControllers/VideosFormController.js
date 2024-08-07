import {Controller} from "stimulus"
import Axios from 'axios'

export default class extends Controller {
    // workaround controller to simulate radio buttons instead of checkboxes for the video selection
    // as long as we only support one video
    connect() {
        const checkboxes = this.element.getElementsByTagName("input")
        Object.values(checkboxes).forEach((checkbox) => {
            checkbox.onchange = ((event) => {
                if (event.currentTarget.checked) {
                    Object.values(checkboxes).forEach((otherCheckbox) => {
                        if (otherCheckbox !== checkbox) {
                            otherCheckbox.checked = false;
                        }
                    })
                }
            })
        })
    }
}
