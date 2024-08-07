import { Controller } from 'stimulus'
const Dropzone = require('dropzone/dist/min/dropzone.min')
import 'dropzone/dist/dropzone.css'
import Axios from 'axios'

Dropzone.autoDiscover = false

/**
 * Responsible for the audio description upload dropzone form element.
 * For further information have a look at UploadListener.php
 */
export default class extends Controller {
    connect() {
        const endpoint = this.data.get('endpoint')
        const removeEndpoint = this.data.get('removeEndpoint')
        const videoId = this.data.get('videoId')
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
            acceptedFiles: '.mp3',
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
                        id: videoId,
                        target: 'audio_description',

                        dzuuid: chunk.file.upload.uuid,
                        dzchunkindex: chunk.index,
                        dztotalfilesize: chunk.file.size,
                        dzchunksize: this.options.chunkSize,
                        dztotalchunkcount: chunk.file.upload.totalChunkCount,
                        dzchunkbyteoffset: chunk.index * this.options.chunkSize,
                    }
                }

                return {
                    id: videoId,
                    target: 'audio_description',
                }
            },
            removedfile: function(file) {
                Axios.post(removeEndpoint)
                    .then(function() {
                        let _ref
                        return (_ref = file.previewElement) != null
                            ? _ref.parentNode.removeChild(file.previewElement)
                            : void 0
                    })
                    .catch(function(e) {
                        console.error('>>>>> remove audio description failed', e)
                    })
            },
        })
    }
}



