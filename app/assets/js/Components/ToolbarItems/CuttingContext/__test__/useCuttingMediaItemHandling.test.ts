/**
 * @jest-environment jsdom
 */

// import for the jest.mock below
import { act, renderHook } from '@testing-library/react-hooks'
import { MediaItem } from 'Components/VideoEditor/types'

import { useCuttingMediaItemHandling } from '../util'

const { useMediaItemHandling } = jest.requireActual('Components/VideoEditor/utils/useMediaItemHandling')

const originalUpdateMediaItemsSpy = jest.fn()

jest.mock('Components/VideoEditor/utils/useMediaItemHandling', () => {
    return {
        useMediaItemHandling: jest.fn((value) => {
            return {
                ...useMediaItemHandling(value),
                updateMediaItems: originalUpdateMediaItemsSpy,
            }
        }),
    }
})

describe('useCuttingMediaItemHandling()', () => {
    const setMediaItemsSpy = jest.fn()
    const updateCallbackSpy = jest.fn()
    const setPlayPositionSpy = jest.fn()

    const itemA = new MediaItem({
        start: '00:00:00.000',
        end: '00:00:05.000',
        text: 'A',
        memo: 'Some test memo...',
        color: null,
        originalData: {
            id: 'testItemA',
            start: '00:00:00.000',
            end: '00:00:05.000',
            color: null,
            text: 'A',
            memo: 'Some test memo...',
            lane: 0,
            offset: 0,
            playbackRate: 1,
            url: '/some.mp4',
            idFromPrototype: null,
        },
        lane: 0,
    })

    const itemB = new MediaItem({
        start: '00:00:03.000',
        end: '00:00:05.000',
        text: 'B',
        memo: 'Some test memo...',
        color: null,
        originalData: {
            id: 'testItemB',
            start: '00:00:03.000',
            end: '00:00:05.000',
            color: null,
            text: 'B',
            memo: 'Some test memo...',
            lane: 0,
            offset: 0,
            playbackRate: 1,
            url: '/some.mp4',
            idFromPrototype: null,
        },
        lane: 0,
    })

    const itemC = new MediaItem({
        start: '00:00:05.010',
        end: '00:00:07.010',
        text: 'C',
        memo: 'Some test memo...',
        color: null,
        originalData: {
            id: 'testItemC',
            start: '00:00:05.010',
            end: '00:00:07.010',
            color: null,
            text: 'C',
            memo: 'Some test memo...',
            lane: 0,
            offset: 0,
            playbackRate: 1,
            url: '/some.mp4',
            idFromPrototype: null,
        },
        lane: 0,
    })

    const baseConfig = {
        userId: 'testUser',
        currentEditorId: 'testUser',
        mediaItems: [itemA],
        readOnly: false,
        setCutList: setMediaItemsSpy,
        updateCallback: updateCallbackSpy,
        playerSyncPlayPosition: 0,
        setPlayPosition: setPlayPositionSpy,
        updateCondition: true,
    }

    afterEach(() => {
        jest.clearAllMocks()
    })

    describe('updateMediaItem()', () => {
        it('should do nothing, if item is not part of mediaItems', () => {
            // @ts-ignore
            const { result } = renderHook(() => useCuttingMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemB, { start: '00:00:03.000' })
            })

            // Should've been called once after rendering
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledTimes(1)
        })

        it('it should update offset', () => {
            // @ts-ignore
            const { result } = renderHook(() => useCuttingMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemA, { start: '00:00:03.000' })
            })

            // One call after rendering and one after the update
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledTimes(2)
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledWith(
                [
                    new MediaItem({
                        start: '00:00:03.000',
                        end: '00:00:05.010',
                        text: 'A',
                        memo: 'Some test memo...',
                        color: null,
                        originalData: {
                            id: 'testItemA',
                            start: '00:00:00.000',
                            end: '00:00:05.000',
                            offset: 3,
                            color: null,
                            text: 'A',
                            memo: 'Some test memo...',
                            lane: 0,
                            playbackRate: 1,
                            url: '/some.mp4',
                            idFromPrototype: null,
                        },
                        lane: 0,
                    }),
                ],
                false
            )
        })

        it('should adjust end with rounded duration < 1', () => {
            // @ts-ignore
            const { result } = renderHook(() => useCuttingMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemA, { end: '00:00:00.000' })
            })

            // One call after rendering and one after the update
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledTimes(2)
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledWith(
                [
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:01.000',
                        text: 'A',
                        memo: 'Some test memo...',
                        color: null,
                        originalData: {
                            id: 'testItemA',
                            start: '00:00:00.000',
                            end: '00:00:05.000',
                            offset: 0,
                            color: null,
                            text: 'A',
                            memo: 'Some test memo...',
                            lane: 0,
                            playbackRate: 1,
                            url: '/some.mp4',
                            idFromPrototype: null,
                        },
                        lane: 0,
                    }),
                ],
                false
            )
        })

        it('should adjust end with rounded duration > 1', () => {
            // @ts-ignore
            const { result } = renderHook(() => useCuttingMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemA, { end: '00:00:03.056' })
            })

            // One call after rendering and one after the update
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledTimes(2)
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledWith(
                [
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:03.000',
                        text: 'A',
                        memo: 'Some test memo...',
                        color: null,
                        originalData: {
                            id: 'testItemA',
                            start: '00:00:00.000',
                            end: '00:00:05.000',
                            offset: 0,
                            color: null,
                            text: 'A',
                            memo: 'Some test memo...',
                            lane: 0,
                            playbackRate: 1,
                            url: '/some.mp4',
                            idFromPrototype: null,
                        },
                        lane: 0,
                    }),
                ],
                false
            )

            act(() => {
                result.current.updateMediaItem(itemA, { end: '00:00:03.556' })
            })

            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledTimes(3)
            expect(originalUpdateMediaItemsSpy).toHaveBeenCalledWith(
                [
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:04.000',
                        text: 'A',
                        memo: 'Some test memo...',
                        color: null,
                        originalData: {
                            id: 'testItemA',
                            start: '00:00:00.000',
                            end: '00:00:05.000',
                            offset: 0,
                            color: null,
                            text: 'A',
                            memo: 'Some test memo...',
                            lane: 0,
                            playbackRate: 1,
                            url: '/some.mp4',
                            idFromPrototype: null,
                        },
                        lane: 0,
                    }),
                ],
                false
            )
        })
    })
})
