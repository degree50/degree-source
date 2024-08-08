import { MediaTrackConfig } from '.'
import { secondToTime } from 'Components/VideoEditor/utils/time'

export const defaultMediaTrackConfig = {
    backgroundColor: '#454545',
    rulerBackgroundColor: '#333',
    gridColor: '#636363',
    rulerColor: '#fff',
    cursorColor: '#ff0000',
    fontSize: 13,
    fontHeight: 15,
    fontTop: 20,
    pixelRatio: 1,
    rulerHeight: 30,
}

export const updateCanvas = (canvas: HTMLCanvasElement, config: MediaTrackConfig) => {
    drawBackground(canvas, {
        backgroundColor: config.backgroundColor,
    })
    drawGrid(canvas, {
        gridNum: config.render.gridNum,
        gridGap: config.render.gridGap,
        gridColor: config.gridColor,
        pixelRatio: config.pixelRatio,
    })
    if (config.render.drawRuler) {
        drawRulerBackground(canvas, {
            rulerBackgroundColor: config.rulerBackgroundColor,
            rulerHeight: config.rulerHeight,
        })
        drawRuler(canvas, {
            rulerColor: config.rulerColor,
            pixelRatio: config.pixelRatio,
            padding: config.render.padding,
            fontSize: config.fontSize,
            fontHeight: config.fontHeight,
            fontTop: config.fontTop,
            timelineStartTime: config.render.timelineStartTime,
            gridNum: config.render.gridNum,
            gridGap: config.render.gridGap,
        })
    }
    drawCursor(canvas, {
        currentTime: config.render.currentTime,
        cursorColor: config.cursorColor,
        padding: config.render.padding,
        pixelRatio: config.pixelRatio,
        timelineStartTime: config.render.timelineStartTime,
        gridGap: config.render.gridGap,
    })
}

const drawBackground = (
    canvas: HTMLCanvasElement,
    {
        backgroundColor,
    }: {
        backgroundColor: string
    }
): void => {
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const { width, height } = canvas

    ctx.clearRect(0, 0, width, height)

    // eslint-disable-next-line
    ctx.fillStyle = backgroundColor

    ctx.fillRect(0, 0, width, height)
}

const drawGrid = (
    canvas: HTMLCanvasElement,
    {
        gridNum,
        gridGap,
        gridColor,
        pixelRatio,
    }: {
        gridNum: number
        gridGap: number
        gridColor: string
        pixelRatio: number
    }
): void => {
    const ctx = canvas.getContext('2d')

    if (!ctx) return

    const { height } = canvas

    // eslint-disable-next-line
    ctx.fillStyle = gridColor

    const adjustmentFactor = getAdjustmentFactorForGrid(gridGap, gridNum, canvas.width)

    // Vertical grid lines
    // eslint-disable-next-line
    for (let index = 0; index < gridNum; index += adjustmentFactor) {
        ctx.fillRect(gridGap * index, 0, pixelRatio, height)
    }
}

const drawRulerBackground = (
    canvas: HTMLCanvasElement,
    {
        rulerBackgroundColor,
        rulerHeight,
    }: {
        rulerBackgroundColor: string
        rulerHeight: number
    }
) => {
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const { width } = canvas

    // eslint-disable-next-line
    ctx.fillStyle = rulerBackgroundColor
    ctx.fillRect(0, 0, width, rulerHeight)
}

const drawRuler = (
    canvas: HTMLCanvasElement,
    {
        rulerColor,
        pixelRatio,
        padding,
        fontSize,
        fontHeight,
        fontTop,
        timelineStartTime,
        gridNum,
        gridGap,
    }: {
        rulerColor: string
        pixelRatio: number
        padding: number
        fontSize: number
        fontHeight: number
        fontTop: number
        timelineStartTime: number
        gridNum: number
        gridGap: number
    }
) => {
    const ctx = canvas.getContext('2d')

    if (!ctx) return

    // eslint-disable-next-line
    ctx.font = `${fontSize * pixelRatio}px Arial`

    // eslint-disable-next-line
    ctx.fillStyle = rulerColor

    const adjustmentFactor = getAdjustmentFactorForGrid(gridGap, gridNum, canvas.width)

    // eslint-disable-next-line
    let second = 0
    // eslint-disable-next-line
    for (let index = 0; index < gridNum; index += 1) {
        if (index && index >= padding && index <= gridNum - padding && index % (10 * adjustmentFactor) === 0) {
            // eslint-disable-next-line
            second += adjustmentFactor
            ctx.fillRect(gridGap * index, 0, pixelRatio, (fontHeight / 2) * pixelRatio)

            const displayTime = secondToTime(timelineStartTime + second).split('.')[0]
            ctx.fillText(displayTime, gridGap * index - fontSize * pixelRatio * 2 + pixelRatio, fontTop + 3)
        } else if (index && index % (5 * adjustmentFactor) === 0) {
            ctx.fillRect(gridGap * index, 0, pixelRatio, (fontHeight / 3) * pixelRatio)
        }
    }
}

const drawCursor = (
    canvas: HTMLCanvasElement,
    {
        currentTime,
        cursorColor,
        padding,
        pixelRatio,
        timelineStartTime,
        gridGap,
    }: {
        currentTime: number
        cursorColor: string
        padding: number
        pixelRatio: number
        timelineStartTime: number
        gridGap: number
    }
) => {
    const ctx = canvas.getContext('2d')

    if (!ctx) return

    const { height } = canvas

    // eslint-disable-next-line
    ctx.fillStyle = cursorColor
    ctx.fillRect(padding * gridGap + (currentTime - timelineStartTime) * gridGap * 10, 0, pixelRatio, height)
}

const getAdjustmentFactorForGrid = (gridGap: number, gridNum: number, canvasWidth: number) => {
    const defaultFactor = 1

    // TODO add WHY comment
    const targetGridGap = canvasWidth / 10
    const fromTargetGridGap =
        gridGap <= 10 && canvasWidth > 0
            ? Math.round((gridNum / targetGridGap + Number.EPSILON) * 1) / 1
            : defaultFactor

    return fromTargetGridGap < 1 ? defaultFactor : fromTargetGridGap
}
