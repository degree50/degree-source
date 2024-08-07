// import for the jest.mock below
import { renderHook, act } from '@testing-library/react-hooks'
import { useMediaItemHandling } from '../useMediaItemHandling'
import { MediaItem } from '../../types'

// Mock module
jest.mock('react-i18nify', () => {
    return {
        setLocale: jest.fn((value) => {
            return value
        }),
        t: jest.fn((value) => {
            return value
        }),
    }
})

// Overwrite navigator for this test suite
// @ts-ignore
// eslint-disable-next-line
global.navigator = {
    language: 'EN',
}

describe('useMediaItemHandling()', () => {
    const setMediaItemsSpy = jest.fn()
    const updateCallbackSpy = jest.fn()

    const itemA = new MediaItem({
        start: '00:00:00.000',
        end: '00:00:05.000',
        text: 'Test',
        memo: 'Some test memo...',
        originalData: {
            start: '00:00:00.000',
            end: '00:00:05.000',
            text: 'Test',
            memo: 'Some test memo...',
            lane: 0,
            otherProperty: 'something else',
        },
        lane: 0,
    })

    const baseConfig = {
        mediaItems: [itemA],
        setMediaItems: setMediaItemsSpy,
        currentTime: 0,
        timelineDuration: 10,
        updateCallback: updateCallbackSpy,
        updateCondition: true,
    }

    afterEach(() => {
        jest.clearAllMocks()
    })

    describe('currentItemIndex', () => {
        it('should return correct index for media item currently playing', () => {
            const { result } = renderHook(() => useMediaItemHandling(baseConfig))

            // Playhead is exactly at start position of our item
            expect(result.current.currentIndex).toBe(0)

            act(() => {
                // Move playhead to fourth second
                result.current.setCurrentTimeForMediaItems(4)
            })

            // Playhead should still be over item
            expect(result.current.currentTimeForMediaItems).toBe(4)
            expect(result.current.currentIndex).toBe(0)

            act(() => {
                // Move playhead to sixth second
                result.current.setCurrentTimeForMediaItems(6)
            })

            expect(result.current.currentTimeForMediaItems).toBe(6)
            // Playhead moved ahead of our only item and therefore
            // no item can be found
            expect(result.current.currentIndex).toBe(-1)
        })
    })

    describe('updateMediaItems()', () => {
        describe('updateCallback and media item setter', () => {
            it('should not be called when new items are equal to previous items', () => {
                const { result } = renderHook(() => useMediaItemHandling(baseConfig))

                act(() => result.current.updateMediaItems(baseConfig.mediaItems, false))
                expect(baseConfig.setMediaItems).not.toHaveBeenCalled()
                expect(baseConfig.updateCallback).not.toHaveBeenCalled()
            })

            it('should be called when force is true (and update condition is false)', () => {
                const { result } = renderHook(() => useMediaItemHandling({ ...baseConfig, updateCondition: false }))

                act(() =>
                    result.current.updateMediaItems(
                        [
                            ...baseConfig.mediaItems,
                            new MediaItem({
                                start: '00:00:00.000',
                                end: '00:00:05.000',
                                text: 'Test',
                                memo: 'Some test memo...',
                                originalData: {
                                    start: '00:00:00.000',
                                    end: '00:00:05.000',
                                    text: 'Test',
                                    memo: 'Some test memo...',
                                    lane: 0,
                                    otherProperty: 'something else',
                                },
                                lane: 0,
                            }),
                        ],
                        true
                    )
                )

                expect(baseConfig.setMediaItems).toHaveBeenCalled()
                expect(baseConfig.updateCallback).toHaveBeenCalled()
            })

            it('should be called when updateCondition is true', () => {
                const { result } = renderHook(() => useMediaItemHandling(baseConfig))

                act(() =>
                    result.current.updateMediaItems(
                        [
                            ...baseConfig.mediaItems,
                            new MediaItem({
                                start: '00:00:00.000',
                                end: '00:00:05.000',
                                text: 'Test',
                                memo: 'Some test memo...',
                                originalData: {
                                    start: '00:00:00.000',
                                    end: '00:00:05.000',
                                    text: 'Test',
                                    memo: 'Some test memo...',
                                    lane: 0,
                                    otherProperty: 'something else',
                                },
                                lane: 0,
                            }),
                        ],
                        false
                    )
                )

                expect(baseConfig.setMediaItems).toHaveBeenCalled()
                expect(baseConfig.updateCallback).toHaveBeenCalled()
            })

            it('should properly convert items to original structure', () => {
                const { result } = renderHook(() => useMediaItemHandling(baseConfig))

                act(() =>
                    result.current.updateMediaItems(
                        [
                            ...baseConfig.mediaItems,
                            new MediaItem({
                                start: '00:00:00.000',
                                end: '00:00:05.000',
                                text: 'Test',
                                memo: 'Some test memo...',
                                originalData: {
                                    start: '00:00:00.000',
                                    end: '00:00:05.000',
                                    text: 'Test',
                                    memo: 'Some test memo...',
                                    lane: 0,
                                    otherProperty: 'something else',
                                    anotherProperty: 'foooooo',
                                },
                                lane: 0,
                            }),
                        ],
                        false
                    )
                )

                expect(baseConfig.setMediaItems).toHaveBeenCalledWith([
                    {
                        start: '00:00:00.000',
                        end: '00:00:05.000',
                        text: 'Test',
                        memo: 'Some test memo...',
                        lane: 0,
                        otherProperty: 'something else',
                        color: null,
                    },
                    {
                        start: '00:00:00.000',
                        end: '00:00:05.000',
                        text: 'Test',
                        memo: 'Some test memo...',
                        lane: 0,
                        otherProperty: 'something else',
                        anotherProperty: 'foooooo',
                        color: null,
                    },
                ])
            })
        })

        describe('worker', () => {
            // TODO test worker.postMessage()
        })
    })

    describe('hasMediaItem()', () => {
        it('should return index if item exists', () => {
            const { result } = renderHook(() => useMediaItemHandling(baseConfig))
            expect(result.current.hasMediaItem(itemA)).toBe(0)
        })

        it('should return -1 if item does not exist', () => {
            const { result } = renderHook(() => useMediaItemHandling(baseConfig))
            expect(
                result.current.hasMediaItem(
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:05.000',
                        text: 'Test',
                        memo: 'Some test memo...',
                        originalData: {
                            start: '00:00:00.000',
                            end: '00:00:05.000',
                            text: 'Test',
                            memo: 'Some test memo...',
                            lane: 0,
                            otherProperty: 'something else',
                        },
                        lane: 0,
                    })
                )
            ).toBe(-1)
        })
    })

    describe('copyMediaItems()', () => {
        it('should return new list with identical items (but different references)', () => {
            const { result } = renderHook(() => useMediaItemHandling(baseConfig))
            const copies = result.current.copyMediaItems()

            expect(copies).toEqual(baseConfig.mediaItems)

            // Check for different references
            expect(copies[0]).not.toBe(baseConfig.mediaItems[0])
        })
    })

    describe('udpateMediaItem()', () => {
        it('should not call updatedItems, if mediaItem is not part of mediaItems', () => {
            const itemB = new MediaItem({
                start: '00:00:02.000', // end time before start time => illegal
                end: '00:00:08.000',
                text: 'Test',
                memo: 'Some test memo...',
                originalData: {
                    start: '00:00:02.000',
                    end: '00:00:10.000',
                    text: 'Test',
                    memo: 'Some test memo...',
                    lane: 0,
                    otherProperty: 'something else',
                },
                lane: 0,
            })

            const { result } = renderHook(() => useMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemB, {})
            })

            expect(setMediaItemsSpy).not.toHaveBeenCalled()
        })

        it('should call updateMediaItems() with copiedItems with updated values', () => {
            const { result } = renderHook(() => useMediaItemHandling(baseConfig))

            act(() => {
                result.current.updateMediaItem(itemA, {
                    start: '00:00:03.000',
                    end: '00:00:10.000',
                })
            })

            expect(setMediaItemsSpy).toHaveBeenCalledWith([
                {
                    start: '00:00:03.000',
                    end: '00:00:10.000',
                    text: 'Test',
                    memo: 'Some test memo...',
                    lane: 0,
                    otherProperty: 'something else',
                    color: null,
                },
            ])
        })

        it.todo('it should call notify() if cloned item is somehow illegal')
    })
})
