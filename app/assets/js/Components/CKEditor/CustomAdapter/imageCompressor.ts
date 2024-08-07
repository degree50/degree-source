import imageCompression from 'browser-image-compression'

export default class ImageHelper {
    async resizeBase64Image(base64Image: any) {
        const options = {
            initialQuality: 0.7,
            maxSizeMB: 1.5, // (default: Number.POSITIVE_INFINITY)
            maxWidthOrHeight: 1500, // compress file ratio (default: undefined)
            useWebWorker: true, // optional, use multi-thread web worker, fallback to run in main-thread (default: true)
            maxIteration: 10, // optional, max number of iteration to compress the image (default: 10)
        }

        const fileImage = await imageCompression.getFilefromDataUrl(base64Image, 'image.jpg')
        const compressImage = await imageCompression(fileImage, options)
        return await imageCompression.getDataUrlFromFile(compressImage)
    }
}
