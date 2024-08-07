import { Controller } from 'stimulus'
import 'dropzone/dist/dropzone.css'
import Axios from 'axios'

const Dropzone = require('dropzone/dist/min/dropzone.min')

Dropzone.autoDiscover = false

export const updateAttachmentList = () => {
    const attachmentList = document.getElementById('attachment-list')
    const updateEndPoint = attachmentList.getAttribute('data-update-endpoint')

    Axios.post(updateEndPoint)
        .then(function (response) {
            attachmentList.innerHTML = response.data
        })
        .catch(function (e) {
            console.error('>>>>> update attachment list failed', e)
        })
}

export default class extends Controller {
    connect() {
        const endpoint = this.data.get('endpoint')
        const removeEndpoint = this.data.get('remove-endpoint')
        const id = this.data.get('id')
        const phaseId = this.data.get('phase-id')
        const uploadLabel = this.data.get('label')
        const updateOnSuccess = this.data.get('update')
        const attachmentList = document.getElementById(updateOnSuccess)

        this.element.classList.add('dropzone')

        // found on https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
        const allowedMimeTypes = [
          'image/*',
          'application/pdf',
          // .doc
          'application/msword',
          // .docx
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          // .odp
          'application/vnd.oasis.opendocument.presentation',
          // .ods
          'application/vnd.oasis.opendocument.spreadsheet',
          // .odt
          'application/vnd.oasis.opendocument.text',
          // .ppt
          'application/vnd.ms-powerpoint',
          // .pptx
          'application/vnd.openxmlformats-officedocument.presentationml.presentation',
          // .xls
          'application/vnd.ms-excel',
          // .xlsx
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ].join(',')

        new Dropzone(this.element, {
            url: endpoint,
            timeout: 2 * 60 * 1000, // 2 minutes
            chunking: true,
            chunkSize: 5000000, // 5 MB
            retryChunks: true,
            dictDefaultMessage: uploadLabel,
            maxFiles: 20,
            maxFilesize: 10000, // 10 GB
            acceptedFiles: allowedMimeTypes,
            addRemoveLinks: true,
            params: function params(files, xhr, chunk) {
                if (chunk) {
                    return {
                        id: id,
                        target: 'attachment',
                        phaseId: phaseId,

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
                    target: 'attachment',
                    phaseId: phaseId,
                }
            },
            success: function (file, response) {
                // add attachmentId to the file to eventually delete it after direct upload
                file.attachmentId = response.attachmentId

                // update list of attachment
                updateAttachmentList()

                // This return statement is necessary to remove progress bar after uploading.
                file.previewElement.classList.add('dz-success')
            },
            error: function (file, error) {
                let errorMessage = ""
                if (typeof error === 'string') {
                    errorMessage = error
                } else {
                    errorMessage = JSON.stringify(error)
                }

                // add error class to the file
                file.previewElement.classList.add('dz-error')
                //  get child element by class of file.previewElement
                const errorElement = file.previewElement.getElementsByClassName('dz-error-message')[0]
                errorElement.innerHTML = errorMessage
            },
            removedfile: function (file) {
                // only remove uploaded files
                if (file.status !== "error") {
                    Axios.post(removeEndpoint, {
                        attachmentId: file.attachmentId,
                    })
                        .then(function () {
                            // update list of attachment
                            updateAttachmentList()
                        })
                        .catch(function (e) {
                            console.error('>>>>> remove attachment failed', e)
                        })
                }

                let _ref
                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0
            },
        })
    }
}
