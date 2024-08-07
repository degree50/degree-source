import { VideoJsPlayer } from 'video.js'
import { useEffect } from 'react'

/**
 * PATCH: patch videoJs keyDown handling to enable key down events when player controls are focussed
 */
export const usePatchVideoJsToMakeHotKeysWork = (player: VideoJsPlayer | undefined) => {
    useEffect(() => {
        if (player) {
            // save the original event handler
            // @ts-ignore
            const originalHandler = player.handleKeyDown.bind(player)

            // overwrite the `handleKeyDown` method of the Component prototype
            // @ts-ignore
            // eslint-disable-next-line
            player.handleKeyDown = function (event: KeyboardEvent) {
                // call original handler to ensure normal videojs behavior and bind the current instance of player to it
                originalHandler(event)
                // dispatch new Event on document which will be caught by hotkey-js
                document.dispatchEvent(new KeyboardEvent(event.type, { ...event }))
            }
        }
    }, [player])
}
