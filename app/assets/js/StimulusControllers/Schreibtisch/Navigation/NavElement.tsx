import React from 'react'
import { useLocation } from 'react-router'
import Link from './Link'

type Props = {
    children: React.ReactNode
    to: string
}

const NavElement = (props: Props) => {
    const { to, children } = props

    const location = useLocation()
    const isActive = location.pathname.includes(to)

    return (
        <li className={`sidebar-item ${isActive ? 'sidebar-item--active' : ''}`}>
            <Link to={to}>{children}</Link>
        </li>
    )
}

export default React.memo(NavElement)
