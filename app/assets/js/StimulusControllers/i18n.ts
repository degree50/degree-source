import { TabsTypesEnum } from 'types'

const de = {
    zoom: 'Timeline-Zoom',
    volume: 'Lautstärke',
    [TabsTypesEnum.VIDEO_ANNOTATIONS]: 'Videoannotationen',
    [TabsTypesEnum.VIDEO_CODES]: 'Videocodierung',
    [TabsTypesEnum.VIDEO_CUTTING]: 'Videoschnitt',
    [TabsTypesEnum.VIDEO_SUBTITLES]: 'Untertitel',
    [TabsTypesEnum.SOLUTIONS]: 'Lösungen',
    [TabsTypesEnum.SOLUTION_FILTERS]: 'Filter',
    [TabsTypesEnum.ORIGINAL_VIDEO]: 'Original',
    [TabsTypesEnum.CUT_VIDEO]: 'Geschnitten',
    timeline: 'Timeline',
    playback: 'Wiedergabe',
    context: 'Kontext',
    cutting: 'Schnitt',
    splitAtCursor: 'Am Cursor teilen',
    play: 'Abspielen',
    pause: 'Pausieren',
}

export default {
    de,
}

export const names = {
    de: 'DE',
}

export const getName = (key: string) => {
    return (
        // @ts-ignore disable-line
        {
            de: 'de',
        }[key] || 'de'
    )
}
