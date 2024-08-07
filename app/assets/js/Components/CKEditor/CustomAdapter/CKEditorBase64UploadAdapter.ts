/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module upload/adapters/base64uploadadapter
 */

/* globals window */

import ImageHelper from './imageCompressor'
import Plugin from '@ckeditor/ckeditor5-core/src/plugin'
import FileRepository, { FileLoader } from '@ckeditor/ckeditor5-upload/src/filerepository'

/**
 * A plugin that converts images inserted into the editor into [Base64 strings](https://en.wikipedia.org/wiki/Base64)
 * in the {@glink builds/guides/integration/saving-data editor output}.
 *
 * This kind of image upload does not require server processing – images are stored with the rest of the text and
 * displayed by the web browser without additional requests.
 *
 * Check out the {@glink features/image-upload/image-upload comprehensive "Image upload overview"} to learn about
 * other ways to upload images into CKEditor 5.
 *
 * @extends Plugin
 */
export default class Base64UploadAdapter extends Plugin {
    /**
     * @inheritDoc
     */
    static get requires() {
        return [FileRepository]
    }

    /**
     * @inheritDoc
     */
    static get pluginName() {
        return 'Base64UploadAdapter'
    }

    /**
     * @inheritDoc
     */
    init() {
        // @ts-ignore
        // eslint-disable-next-line functional/immutable-data
        this.editor.plugins.get(FileRepository).createUploadAdapter = (loader) => new Adapter(loader)
    }
}

/**
 * The upload adapter that converts images inserted into the editor into Base64 strings.
 *
 * @private
 * @implements UploadAdapter
 */
class Adapter {
    private readonly loader: FileLoader
    private readonly imageHelper: ImageHelper
    private reader: FileReader | null = null
    /**
     * Creates a new adapter instance.
     *
     * @param {FileLoader} loader
     */
    constructor(loader: FileLoader) {
        /**
         * `FileLoader` instance to use during the upload.
         *
         * @member {FileLoader} #loader
         */
        this.loader = loader
        this.imageHelper = new ImageHelper()
    }

    /**
     * Starts the upload process.
     *
     * @see UploadAdapter#upload
     * @returns {Promise}
     */
    upload() {
        return new Promise((resolve, reject) => {
            // eslint-disable-next-line functional/immutable-data
            const reader = (this.reader = new window.FileReader())

            reader.addEventListener('load', async () => {
                const base64Image = await this.imageHelper.resizeBase64Image(reader.result)
                resolve({ default: base64Image })
            })

            reader.addEventListener('error', (err) => {
                reject(err)
            })

            reader.addEventListener('abort', () => {
                reject()
            })

            this.loader.file.then((file: any) => {
                reader.readAsDataURL(file)
            })
        })
    }

    /**
     * Aborts the upload process.
     *
     * @see UploadAdapter#abort
     * @returns {Promise}
     */
    abort() {
        // @ts-ignore
        this.reader.abort()
    }
}
