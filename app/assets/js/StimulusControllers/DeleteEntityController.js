import {Controller} from "stimulus"
import Axios from 'axios'
import {updateAttachmentList} from "StimulusControllers/AttachmentUploadController";

export default class extends Controller {
    connect() {
        // TODO pop modal to confirm deletion
        const endpoint = this.element.getAttribute('href');
        const fileName = this.element.getAttribute('data-filename');
        const button = this.element;

        button.onclick = ((event) => {
            event.preventDefault();
            button.setAttribute('disabled', '')
            button.classList.add('disabled');

            Axios.get(endpoint).then(function () {
                // remove entry
                button.parentElement.remove()

                // remove element from dropzone
                const dropzone = document.querySelector('.dropzone')
                const uploadedFilesInDropzone = dropzone.querySelectorAll('.dz-filename')
                uploadedFilesInDropzone.forEach((el) => {
                    if (el.textContent === fileName) {
                        el.closest('.dz-preview').remove()
                        // if this was the one and only file, remove the dropzone class to reset the dropzone
                        if (uploadedFilesInDropzone.length === 1) {
                            dropzone.classList.remove('dz-started')
                        }
                    }
                })
                updateAttachmentList()

                // event for tests to listen to
                const event = new Event('attachment-deleted')
                window.dispatchEvent(event)
            }).catch(function (e) {
                console.error('>>>>> remove entity failed', e)
            }).finally(function () {
                button.removeAttribute('disabled')
            })
        });

    }
}
