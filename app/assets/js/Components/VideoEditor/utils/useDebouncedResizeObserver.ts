import { useState, useMemo } from 'react'
import useResizeObserver from 'use-resize-observer'
import debounce from 'lodash/debounce'

export const useDebouncedResizeObserver = (
    $containerRef: React.RefObject<HTMLElement> | undefined = undefined,
    wait: number
) => {
    const [size, setSize] = useState({})

    const onResize = useMemo(() => debounce(setSize, wait, { leading: true }), [wait])
    // bit weird code, because given the onResize handler the observer does not return width or height.
    // But in order to use this hook outside we initialize width and height with 0.
    // Otherwise, an empty object would be returned.
    const { width = 0, height = 0, ref } = useResizeObserver({ ref: $containerRef, onResize })

    return { width, height, ...size, ref }
}
