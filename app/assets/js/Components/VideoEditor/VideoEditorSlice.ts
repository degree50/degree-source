import PlayerSlice, { PlayerState, actions as playerActions, selectors as playerSelectors } from './PlayerSlice'
import { combineReducers } from '@reduxjs/toolkit'
import MediaLaneRenderConfigSlice, {
    actions as mediaLaneRenderConfigActions,
    selectors as mediaLaneRenderConfigSelectors,
} from './MediaLaneRenderConfigSlice'
import { RenderConfig } from './components/MediaLane/MediaTrack'
import OverlaySlice, {
    actions as overlayActions,
    OverlayState,
    selectors as overlaySelectors,
} from 'Components/ToolbarItems/components/OverlayContainer/OverlaySlice'
import {
    filterSlice,
    FilterState,
    selectors as filterSelectors,
} from 'Components/ToolbarItems/FilterContext/FilterSlice'

export default combineReducers({
    filter: filterSlice.reducer,
    player: PlayerSlice,
    mediaLaneRenderConfig: MediaLaneRenderConfigSlice,
    overlay: OverlaySlice,
})

export type VideoEditorState = {
    videoEditor: {
        filter: FilterState
        player: PlayerState
        mediaLaneRenderConfig: RenderConfig
        overlay: OverlayState
    }
}

export const actions = {
    filter: filterSlice.actions,
    player: playerActions,
    mediaLaneRenderConfig: mediaLaneRenderConfigActions,
    overlay: overlayActions,
}

export const selectors = {
    filter: filterSelectors,
    player: playerSelectors,
    mediaLaneRenderConfig: mediaLaneRenderConfigSelectors,
    overlay: overlaySelectors,
}
