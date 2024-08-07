import { Controller } from 'stimulus'
const Dropzone = require('dropzone/dist/min/dropzone.min')
import 'dropzone/dist/dropzone.css'
import Axios from 'axios'

Dropzone.autoDiscover = false

/**
 * Responsible for the video upload dropzone form element.
 * For further information have a look at UploadListener.php
 */
export default class extends Controller {
    connect() {
        // Submit button inside the footer
        const formSubmitButton = document.getElementById('mediathek_video_form_save')
        formSubmitButton.setAttribute('disabled', '')
        const endpoint = this.data.get('endpoint')
        const removeEndpoint = this.data.get('removeEndpoint')
        const id = this.data.get('id')
        const uploadLabel = this.data.get('label')

        this.element.classList.add('dropzone')

        new Dropzone(this.element, {
            url: endpoint,
            timeout: 2 * 60 * 1000, // 2 minutes
            chunking: true,
            chunkSize: 5000000, // 5 MB
            retryChunks: true,
            dictDefaultMessage: uploadLabel,
            maxFiles: 1,
            maxFilesize: 10000, // 10 GB
            // allow mp4 and mov
            acceptedFiles: 'video/mp4,video/quicktime',
            addRemoveLinks: true,
            init: function() {
                this.on('addedfile', function(file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[0])
                    }
                })
            },
            params: function params(files, xhr, chunk) {
                if (chunk) {
                    return {
                        id: id,
                        target: 'video',

                        dzuuid: chunk.file.upload.uuid,
                        dzchunkindex: chunk.index,
                        dztotalfilesize: chunk.file.size,
                        dzchunksize: this.options.chunkSize,
                        dztotalchunkcount: chunk.file.upload.totalChunkCount,
                        dzchunkbyteoffset: chunk.index * this.options.chunkSize,
                    }
                }

                return {
                    id: id,
                    target: 'video',
                }
            },
            success: function() {
                formSubmitButton.removeAttribute('disabled')
            },
            removedfile: function(file) {
                Axios.post(removeEndpoint)
                    .then(function() {
                        formSubmitButton.setAttribute('disabled', '')
                        let _ref
                        return (_ref = file.previewElement) != null
                            ? _ref.parentNode.removeChild(file.previewElement)
                            : void 0
                    })
                    .catch(function(e) {
                        console.error('>>>>> remove video failed', e)
                    })
            },
        })
    }
}
