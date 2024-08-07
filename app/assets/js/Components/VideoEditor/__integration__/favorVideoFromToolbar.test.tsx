/**
 * @jest-environment jsdom
 */
import '@testing-library/jest-dom'
import ToggleVideoFavorite from 'Components/ToolbarItems/ToggleVideoFavorite'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { RootReducer } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import { render, screen } from '../../../../../tests/jestUtils'

describe('toggleVideoFavorite-toolbar item', () => {
    const BASE_CONFIG = {
        isStudent: true,
        type: ExercisePhaseTypesEnum.VIDEO_ANALYSIS,
        videos: [
            {
                id: 'test-video',
                name: 'test-video',
                description: 'test-video',
                url: {
                    hls: 'someurl.hls',
                    mp4: 'someurl.mp4',
                    vtt: 'someurl.vtt',
                },
                duration: 20,
                isFavorite: true,
            },
        ],
    }

    it('should be disabled, if current user is not a student', () => {
        const config = {
            reducer: RootReducer,
            preloadedState: {
                config: {
                    ...BASE_CONFIG,
                    isStudent: false,
                },
            },
        }

        render(<ToggleVideoFavorite />, config)

        const toggleButton = screen.getByTestId('toggle-video-favorite')
        expect(toggleButton).toBeDisabled()
    })

    it('should be disabled if the current phase is not an analysis phase', () => {
        const config = {
            reducer: RootReducer,
            preloadedState: {
                config: {
                    ...BASE_CONFIG,
                    type: ExercisePhaseTypesEnum.VIDEO_CUTTING,
                },
            },
        }

        render(<ToggleVideoFavorite />, config)

        const toggleButton = screen.getByTestId('toggle-video-favorite')
        expect(toggleButton).toBeDisabled()
    })

    it('should not be disabled, if isStudent and phase is analysis phase', () => {
        const config = {
            reducer: RootReducer,
            preloadedState: {
                config: BASE_CONFIG,
            },
        }

        render(<ToggleVideoFavorite />, config)

        const toggleButton = screen.getByTestId('toggle-video-favorite')
        expect(toggleButton).not.toBeDisabled()
    })

    it('should show correct favorite status, if is favorite', () => {
        const config = {
            reducer: RootReducer,
            preloadedState: {
                config: BASE_CONFIG,
            },
        }

        render(<ToggleVideoFavorite />, config)

        const toggleButtonIcon = screen.getByTestId('toggle-video-favorite__icon')
        expect(toggleButtonIcon).toHaveClass('fa-solid')
    })

    it('should show correct favorite status, if is no favorite', () => {
        const config = {
            reducer: RootReducer,
            preloadedState: {
                config: {
                    ...BASE_CONFIG,
                    videos: [
                        {
                            id: 'test-video',
                            name: 'test-video',
                            description: 'test-video',
                            url: {
                                hls: 'someurl.hls',
                                mp4: 'someurl.mp4',
                                vtt: 'someurl.vtt',
                            },
                            duration: 20,
                        },
                    ],
                },
            },
        }

        render(<ToggleVideoFavorite />, config)

        const toggleButtonIcon = screen.getByTestId('toggle-video-favorite__icon')
        expect(toggleButtonIcon).toHaveClass('fa-regular')
    })
})
