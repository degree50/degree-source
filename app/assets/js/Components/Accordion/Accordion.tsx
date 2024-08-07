import React, { memo, ReactNode, useMemo, useState } from 'react'
import Button from '../Button/Button'
import shortid from 'shortid'

type Props = {
    className?: string
    title: string | ReactNode
    buttonTitleClose: string
    buttonTitleOpen: string
    children: ReactNode
}

const Accordion = (props: Props) => {
    const [isOpen, setIsOpen] = useState(false)

    const handleClick = useMemo(() => () => setIsOpen((value) => !value), [])

    const className = `accordion${isOpen ? ' accordion--is-open' : ''} ${props.className ?? ''}`.trim()

    const labelId = useMemo(() => shortid(), [])
    const regionId = useMemo(() => shortid(), [])

    return (
        <div className={className}>
            <p tabIndex={1} className="accordion__title highlight-focus-within--outline">
                <span id={labelId}>{props.title}</span>
                <Button
                    className="button button--type-outline-primary"
                    title={isOpen ? props.buttonTitleClose : props.buttonTitleOpen}
                    onPress={handleClick}
                    aria-expanded={isOpen}
                    aria-controls={isOpen ? regionId : undefined}
                >
                    <i className="fas fa-angle-right" />
                </Button>
            </p>
            <div
                id={regionId}
                role="region"
                aria-labelledby={labelId}
                className="accordion__content"
                aria-hidden={!isOpen}
            >
                {props.children}
            </div>
        </div>
    )
}

export default memo(Accordion)
