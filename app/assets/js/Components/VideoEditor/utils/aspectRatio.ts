/**
 * Compute height with aspect ratio.
 * Formula: newHeight = (h / w) * newWidth
 */
export const getAspectRatioHeight = (originalWidth: number, originalHeight: number, newWidth: number) =>
    (originalHeight / originalWidth) * newWidth

/**
 * Compute width with aspect ratio.
 * Formula: newWidth = (w / h) * newHeight
 */
export const getAspectRatioWidth = (originalWidth: number, originalHeight: number, newHeight: number) =>
    (originalWidth / originalHeight) * newHeight
