import { FocusScope } from '@react-aria/focus'
import { connect } from 'react-redux'
import Button from 'Components/Button/Button'
import React, { FC, memo, ReactNode, useCallback, useMemo, useState } from 'react'
import { actions } from '../PlayerSlice'
import { generate } from 'shortid'

type OwnProps = {
    className?: string
    label?: string
    ariaLabel: string
    children: ReactNode
    icon?: ReactNode
    key?: string
    disabled?: boolean
    pauseVideo?: boolean
    small?: boolean
    closeMenuOnItemSelect?: boolean
}

const mapDispatchToProps = {
    setPauseVideo: actions.setPause,
}

type Props = typeof mapDispatchToProps & OwnProps

const MenuButton: FC<Props> = ({
    children,
    className,
    label,
    ariaLabel,
    icon,
    key,
    disabled = false,
    pauseVideo = false,
    small = false,
    setPauseVideo,
    closeMenuOnItemSelect,
}) => {
    const [isOpen, setIsOpen] = useState(false)

    const open = useCallback(() => {
        setIsOpen(true)
    }, [])
    const close = useCallback(() => {
        setIsOpen(false)
    }, [])

    const toggleMenu = useCallback(() => {
        if (pauseVideo) {
            setPauseVideo(true)
        }

        if (isOpen) {
            close()
        } else {
            open()
        }
    }, [isOpen, close, open, pauseVideo, setPauseVideo])

    const handleKeyDown = useCallback(
        (ev: React.KeyboardEvent<HTMLElement>) => {
            switch (ev.key) {
                case 'Escape': {
                    ev.preventDefault()
                    ev.stopPropagation()
                    close()
                    return false
                }
            }
        },
        [close]
    )

    const classes = `button button--type-primary ${
        disabled ? 'disabled' : ``
    } menu-button video-editor__toolbar__button ${small ? 'button--size-small' : ''} ${className}`

    const focusScopeKey = useMemo(() => key ?? generate(), [key])

    if (disabled) {
        return (
            <div className="menu__wrapper">
                <Button title={ariaLabel} className={classes} isDisabled>
                    {icon} {label}
                </Button>
            </div>
        )
    }

    return (
        <div className="menu__wrapper">
            <Button className={classes} onPress={toggleMenu} title={ariaLabel}>
                {icon} {label}
            </Button>
            {isOpen && (
                // WHY use mouseDown and touchStart instead of click:
                //   This fixes an issue with Chrome Dev Tools where a click event is triggered after a touch event.
                //   This causes the menu to close immediately after opening.
                //   By using mouseDown and touchStart, we can ignore this additional click event.
                <div className="menu__backdrop" onMouseDown={close} onTouchStart={close} />
            )}
            {isOpen && (
                <FocusScope autoFocus contain restoreFocus key={focusScopeKey}>
                    <div
                        className="menu"
                        onKeyDown={handleKeyDown}
                        onClick={() => {
                            if (closeMenuOnItemSelect) {
                                close()
                            }
                        }}
                    >
                        {children}
                    </div>
                </FocusScope>
            )}
        </div>
    )
}

export default connect(undefined, mapDispatchToProps)(memo(MenuButton))
