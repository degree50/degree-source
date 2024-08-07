import { VideoCodePrototype } from 'Components/VideoEditor/types'
import { ChangeEvent, useState } from 'react'
import { generate } from 'shortid'

const defaultColor = '#cccccc'
const createDefaultPrototype = (parentPrototypeParent?: VideoCodePrototype): VideoCodePrototype => ({
    id: generate(),
    name: '',
    color: parentPrototypeParent?.color ?? defaultColor,
    userCreated: true,
    parentId: parentPrototypeParent?.id,
    videoCodes: [],
})

export const useVideoCodePrototypeEdit = (
    initialPrototype?: VideoCodePrototype,
    prototypeParent?: VideoCodePrototype
) => {
    const [transientVideoCodePrototype, setTransientVideoCodePrototype] = useState<VideoCodePrototype>(
        initialPrototype ?? createDefaultPrototype(prototypeParent)
    )

    const handleNameChange = (ev: ChangeEvent<HTMLInputElement>) => {
        if (transientVideoCodePrototype) {
            setTransientVideoCodePrototype({
                ...transientVideoCodePrototype,
                name: ev.target.value,
            })
        }
    }

    const handleColorChange = (ev: ChangeEvent<HTMLInputElement>) => {
        if (transientVideoCodePrototype) {
            setTransientVideoCodePrototype({
                ...transientVideoCodePrototype,
                color: ev.target.value ?? defaultColor,
            })
        }
    }

    return {
        transientVideoCodePrototype,
        handleNameChange,
        handleColorChange,
    }
}
