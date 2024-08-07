import { useState, useEffect } from 'react'
import { Player } from '../types'

export const useMutablePlayer = (worker?: Worker) => {
    // Player instance
    const [player, setPlayer] = useState<Player | undefined>(undefined)

    // Run only once
    useEffect(() => {
        if (player && worker && !worker.onmessage) {
            // eslint-disable-next-line
            worker.onmessage = (event) => {
                player.subtitle.switch(event.data)
            }
        }
    }, [player, worker])

    return {
        player,
        setPlayer,
    }
}
