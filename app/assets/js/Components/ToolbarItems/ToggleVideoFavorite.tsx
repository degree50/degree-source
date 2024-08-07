import Button from 'Components/Button/Button'
import React, { useCallback } from 'react'
import { ExercisePhaseTypesEnum } from 'StimulusControllers/ExerciseAndSolutionStore/ExercisePhaseTypesEnum'
import { useAppDispatch, useAppSelector } from 'StimulusControllers/ExerciseAndSolutionStore/hooks'
import { selectors, actions } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'

const ToggleVideoFavorite = () => {
    const dispatch = useAppDispatch()
    const isStudent = useAppSelector(selectors.config.selectIsStudent)
    const isAnalysisPhase = useAppSelector(selectors.config.selectPhaseType) === ExercisePhaseTypesEnum.VIDEO_ANALYSIS
    const videos = useAppSelector(selectors.config.selectVideos)
    // We currently always only use the first video
    // FIXME - we should probably refactor the app, so that our backend actually only returns
    // a single video instead
    const video = videos[0]
    const isFavorite = video?.isFavorite ?? false

    const toggleVideoFavorite = useCallback(() => {
        // @ts-ignore
        dispatch(actions.config.toggleVideoFavorite(video.id))
    }, [video, dispatch])

    return (
        <div className="video-editor-menu">
            <Button
                title={isFavorite ? 'Video ent-favorisieren' : 'Video favorisieren'}
                className="button button--type-primary video-editor__toolbar__button"
                onPress={toggleVideoFavorite}
                isDisabled={!isStudent || !isAnalysisPhase}
                data-testid="toggle-video-favorite"
            >
                <i
                    className={isFavorite ? 'fa-solid fa-star' : 'fa-regular fa-star'}
                    data-testid="toggle-video-favorite__icon"
                ></i>
            </Button>
        </div>
    )
}

export default React.memo(ToggleVideoFavorite)
