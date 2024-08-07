import { useRadio } from '@react-aria/radio'
import React, { useRef } from 'react'
import PredefinedCodeLock from '../../PredefinedCodeLock'
import Color from '../../VideoCodePrototypes/PrototypeEntry/Color'
import { RadioContext } from '../RadioGroup'
import { VideoCodePrototype } from 'Components/VideoEditor/types'
import { getColorName } from 'ntc-ts'
import LockSpacer from './LockSpacer'

type Props = {
    children: React.ReactNode
    prototype: VideoCodePrototype
    parentPrototype?: VideoCodePrototype
    value: string
}

const Radio = (props: Props) => {
    const { children } = props
    const state = React.useContext(RadioContext)
    const ref = useRef(null)

    // Ignoring due to typing issue in library
    // @ts-ignore
    const { inputProps } = useRadio(props, state, ref)

    /**
     * WHY:
     * This will be read by the screen reader when the list element if focused (when tabbing through list).
     *
     * Example:
     * "
     *   Name: Gelungener Einsatz von Medien.
     *   Farbe: Grün.
     *   Vordefinierter Video Code.
     * "
     * oder
     * "
     *   Name: Smart Whiteboard.
     *   Farbe: Grün.
     *   Selbsterstellter Video Code.
     *   Unter-Code von Gelungener Einsatz von Medien.
     * "
     */
    const ariaLabel = `
        Name: ${props.prototype.name}.
        Farbe: ${getColorName(props.prototype.color).name}.
        ${props.prototype.userCreated ? 'Selbsterstellter' : 'Vordefinierter'} Video Code.
        ${props.parentPrototype ? `Unter-Code von ${props.parentPrototype.name}.` : ''}
    `

    const lockOrSpacer = (() => {
        if (!props.prototype.userCreated) {
            return <PredefinedCodeLock />
        }

        // Only Top level prototypes ever have a lock.
        // To make the UI visually consistent we add a spacer for user created
        // parent prototypes instead of the lock-icon
        if (!props.parentPrototype) {
            return <LockSpacer />
        }

        // Child-protoypes don't need either a lock or a spacer
        return null
    })()

    return (
        <li className={`video-code-select__option ${props.parentPrototype ? 'child-prototype' : ''}`}>
            <input {...inputProps} ref={ref} id={props.prototype.id} aria-label={ariaLabel} />
            <label htmlFor={props.prototype.id}>
                <Color color={props.prototype.color} />
                {lockOrSpacer}
                <span>{children}</span>
            </label>
        </li>
    )
}

export default React.memo(Radio)
