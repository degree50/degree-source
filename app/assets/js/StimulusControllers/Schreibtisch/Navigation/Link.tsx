import React from 'react'
import { NavLink } from 'react-router-dom'

type Props = {
    children: React.ReactNode
    to: string
}

const Link = (props: Props) => {
    const { children, to } = props
    return (
        <NavLink to={to} className="nav-link">
            {children}
        </NavLink>
    )
}

export default React.memo(Link)
