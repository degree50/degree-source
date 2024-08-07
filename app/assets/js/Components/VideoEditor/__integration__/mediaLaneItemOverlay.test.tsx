/**
 * @jest-environment jsdom
 */

import { render, screen, fireEvent, waitFor } from '../../../../../tests/jestUtils'
import MediaItemComponent from '../components/MediaLane/MediaItems/MediaItem'
import { initialRenderConfig } from '../MediaLaneRenderConfigSlice'
import { MediaItem, MediaItemTypeEnum } from '../types'
import '@testing-library/jest-dom'
import ReactDOM from 'react-dom'
import { useState } from 'react'
import { RootReducer } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

jest.mock('@react-aria/overlays', () => {
    const originalModule = jest.requireActual('@react-aria/overlays')

    return {
        __esModule: true,
        // Mock arias overlay container and useModal(), because it doesn't work without additional <script /> tags and
        // would therefore blow up our testcase
        ...originalModule,
        useModal: () => ({}),
        OverlayContainer: ({ children }: { children: React.ReactChild }) => {
            const domEl = document.getElementById('modal-root')

            if (!domEl) return null

            return ReactDOM.createPortal(children, domEl)
        },
    }
})

jest.mock('@react-stately/overlays', () => {
    const originalModule = jest.requireActual('@react-stately/overlays')

    return {
        __esModule: true,
        ...originalModule,
        // Mock useOverlayTriggerState() with a regular useState() implementation
        useOverlayTriggerState: () => {
            const [state, setState] = useState(false)

            return {
                isOpen: state,
                open: () => setState(true),
                close: () => setState(false),
            }
        },
    }
})

describe('Medialane item', () => {
    const BASE_PROPS = {
        showTextInMediaItems: true,
        updateMediaItem: () => undefined,
        renderConfig: initialRenderConfig,
        setPlayPosition: () => undefined,
        checkMediaItem: () => false,
        onItemMouseDown: () => undefined,
        onItemTouchStart: () => undefined,
    }

    const modalRoot = document.createElement('div')
    modalRoot.setAttribute('id', 'modal-root')
    document.body.appendChild(modalRoot)

    it('should open correct annotation overlay on click on info icon', async () => {
        const annotation = {
            id: 'test-annotation',
            start: '00:00:00.000',
            end: '00:00:00.901',
            text: 'Test-Annotation',
            memo: 'Test-Annotation Memo',
            color: null,
            solutionId: 'test-solution',
        }

        const itemID = 'test-annotation'

        const config = {
            reducer: RootReducer,
            preloadedState: {
                data: {
                    solutions: {
                        byId: {
                            'test-solution': {
                                solutionData: {
                                    annotations: [itemID],
                                    videoCodes: [],
                                    cutList: [],
                                    videoCodePrototypes: [],
                                },
                                id: 'test-solution',
                                userName: 'admin@sandstorm.de',
                                userId: 'test-user',
                                cutVideo: null,
                                fromGroupPhase: false,
                            },
                        },
                        current: 'test-solution',
                        previous: [],
                    },
                    annotations: {
                        byId: {
                            [annotation.id]: annotation,
                        },
                    },
                },
            },
        }

        render(
            <MediaItemComponent
                {...BASE_PROPS}
                id={itemID}
                item={
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:00.901',
                        text: 'Test-Annotation',
                        memo: 'Test-Annotation Memo',
                        color: null,
                        originalData: {
                            ...annotation,
                            type: MediaItemTypeEnum.annotation,
                        },
                        lane: 0,
                    })
                }
            />,
            config
        )

        // Assert that the overlay is not open yet, by checking, that
        // the memo text is not present on the document
        expect(screen.queryByText(/Test-Annotation Memo/i)).toBe(null)

        const infoButton = screen.getByTestId('media-item-info-button')
        fireEvent.click(infoButton)

        expect(await waitFor(() => screen.getByText(/Test-Annotation Memo/i))).toBeVisible()

        fireEvent.click(screen.getByText(/schließen/i))

        expect(screen.queryByText(/Test-Annotation Memo/i)).toBe(null)
    })

    it('should open correct videoCode overlay on click on info icon', async () => {
        const videoCode = {
            id: 'test-videoCode',
            start: '00:00:00.000',
            end: '00:00:00.901',
            text: 'Test-VideoCode',
            memo: 'Test-VideoCode Memo',
            color: null,
            solutionId: 'test-solution',
        }

        const itemID = 'test-videoCode'

        const config = {
            reducer: RootReducer,
            preloadedState: {
                data: {
                    solutions: {
                        byId: {
                            'test-solution': {
                                solutionData: {
                                    videoCodes: [itemID],
                                    annotations: [],
                                    cutList: [],
                                    videoCodePrototypes: [],
                                },
                                id: 'test-solution',
                                userName: 'admin@sandstorm.de',
                                userId: 'test-user',
                                cutVideo: null,
                                fromGroupPhase: false,
                            },
                        },
                        current: 'test-solution',
                        previous: [],
                    },
                    videoCodes: {
                        byId: {
                            [videoCode.id]: videoCode,
                        },
                    },
                },
            },
        }

        render(
            <MediaItemComponent
                {...BASE_PROPS}
                id={itemID}
                item={
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:00.901',
                        text: 'Test-VideoCode',
                        memo: 'Test-VideoCode Memo',
                        color: null,
                        originalData: {
                            ...videoCode,
                            type: MediaItemTypeEnum.videoCode,
                        },
                        lane: 0,
                    })
                }
            />,
            config
        )

        // Assert that the overlay is not open yet, by checking, that
        // the memo text is not present on the document
        expect(screen.queryByText(/Test-VideoCode Memo/i)).toBe(null)

        const infoButton = screen.getByTestId('media-item-info-button')
        fireEvent.click(infoButton)

        expect(await waitFor(() => screen.getByText(/Test-VideoCode Memo/i))).toBeVisible()
        expect(await waitFor(() => screen.getByText(/Video Codierung/i))).toBeVisible()

        fireEvent.click(screen.getByText(/schließen/i))

        expect(screen.queryByText(/Test-VideoCode Memo/i)).toBe(null)
    })

    it('should open correct cut overlay on click on info icon', async () => {
        const cut = {
            id: 'test-cut',
            start: '00:00:00.000',
            end: '00:00:00.901',
            text: 'Test-Cut',
            memo: 'Test-Cut Memo',
            color: null,
            solutionId: 'test-solution',
        }

        const itemID = 'test-cut'

        const config = {
            reducer: RootReducer,
            preloadedState: {
                data: {
                    solutions: {
                        byId: {
                            'test-solution': {
                                solutionData: {
                                    cuts: [itemID],
                                    annotations: [],
                                    cutList: [],
                                    cutPrototypes: [],
                                },
                                id: 'test-solution',
                                userName: 'admin@sandstorm.de',
                                userId: 'test-user',
                                cutVideo: null,
                                fromGroupPhase: false,
                            },
                        },
                        current: 'test-solution',
                        previous: [],
                    },
                    cuts: {
                        byId: {
                            [cut.id]: cut,
                        },
                    },
                },
            },
        }

        render(
            <MediaItemComponent
                {...BASE_PROPS}
                id={itemID}
                item={
                    new MediaItem({
                        start: '00:00:00.000',
                        end: '00:00:00.901',
                        text: 'Test-Cut',
                        memo: 'Test-Cut Memo',
                        color: null,
                        originalData: {
                            ...cut,
                            type: MediaItemTypeEnum.cut,
                        },
                        lane: 0,
                    })
                }
            />,
            config
        )

        // Assert that the overlay is not open yet, by checking, that
        // the memo text is not present on the document
        expect(screen.queryByText(/Test-Cut Memo/i)).toBe(null)

        const infoButton = screen.getByTestId('media-item-info-button')
        fireEvent.click(infoButton)

        expect(await waitFor(() => screen.getByText(/Test-Cut Memo/i))).toBeVisible()
        expect(await waitFor(() => screen.getAllByText(/Schnitt/i).length)).toBe(2)

        fireEvent.click(screen.getByText(/schließen/i))

        expect(screen.queryByText(/Test-Cut Memo/i)).toBe(null)
    })
})
