import { TabsTypesEnum } from 'types'

export type Tab = {
    id: TabsTypesEnum
}

export type Tabs = {
    [key in TabsTypesEnum]: Tab
}

export const videoEditorPlayerTabs = {
    [TabsTypesEnum.ORIGINAL_VIDEO]: {
        id: TabsTypesEnum.ORIGINAL_VIDEO,
    },
    [TabsTypesEnum.CUT_VIDEO]: {
        id: TabsTypesEnum.CUT_VIDEO,
    },
}

export const tabs = {
    [TabsTypesEnum.VIDEO_ANNOTATIONS]: {
        id: TabsTypesEnum.VIDEO_ANNOTATIONS,
    },
    [TabsTypesEnum.VIDEO_CODES]: {
        id: TabsTypesEnum.VIDEO_CODES,
    },
    [TabsTypesEnum.VIDEO_CUTTING]: {
        id: TabsTypesEnum.VIDEO_CUTTING,
    },
    [TabsTypesEnum.VIDEO_SUBTITLES]: {
        id: TabsTypesEnum.VIDEO_SUBTITLES,
    },
}

export const solutionTabs = {
    [TabsTypesEnum.SOLUTIONS]: {
        id: TabsTypesEnum.SOLUTIONS,
    },
    [TabsTypesEnum.SOLUTION_FILTERS]: {
        id: TabsTypesEnum.SOLUTION_FILTERS,
    },
}
