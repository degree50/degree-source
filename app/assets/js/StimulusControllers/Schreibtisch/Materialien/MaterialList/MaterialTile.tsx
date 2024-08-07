import { format } from 'date-fns'
import React from 'react'
import { NavLink } from 'react-router-dom'
import { ROUTE_EDIT_MATERIAL } from 'StimulusControllers/Schreibtisch/Schreibtisch'
import { Material } from 'StimulusControllers/Schreibtisch/types'

type Props = {
    material: Material
}

const MaterialTile = (props: Props) => {
    const { material } = props

    const createdAt = format(new Date(material.createdAt.date), 'dd.MM.yyyy')
    const lastUpdatedAt = material.lastUpdatedAt
        ? format(new Date(material.lastUpdatedAt?.date), 'dd.MM.yyyy')
        : undefined

    return (
        <NavLink className="overview-item" to={`${ROUTE_EDIT_MATERIAL}/${material.id}`}>
            <div className="overview-item__date">
                <span className="sr-only">{lastUpdatedAt ? 'Zuletzt geändert am: ' : 'Erstellt am: '}</span>
                {lastUpdatedAt ? lastUpdatedAt : createdAt}
            </div>
            <div className="overview-item__status">
                <i aria-hidden={true} className="fas fa-square-quote"></i>
            </div>
            <div className="overview-item__title">
                <span className="sr-only">Titel des Materials: </span>
                {material.name}
            </div>
        </NavLink>
    )
}

export default React.memo(MaterialTile)
