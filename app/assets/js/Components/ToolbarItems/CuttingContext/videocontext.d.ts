// TODO refine typings
declare module 'videocontext' {
    export default class VideoContext {
        static DEFINITIONS: any
        static EVENTS: {
            UPDATE: 'update'
            STALLED: 'stalled'
            ENDED: 'ended'
            CONTENT: 'content'
            NO_CONTENT: 'no-content'
        }
        static STATE: {
            PLAYING: 0
            PAUSED: 1
            STALLED: 2
            ENDED: 3
            BROKEN: 4
        }

        canvas: HTMLElement | null
        compositor: (effect: any) => any
        destination: Record<string, unknown>
        currentTime: number
        registerCallback: (event: any, callback: () => void) => void
        state: number
        volume: number
        duration: number
        playbackRate: number
        play: () => void
        pause: () => void
        video: (el: HTMLVideoElement, offset: number, preloadTime: number, config: Record<string, unknown>) => any // FIXME
        reset: () => void

        constructor(canvas: HTMLElement | null)
    }
}
