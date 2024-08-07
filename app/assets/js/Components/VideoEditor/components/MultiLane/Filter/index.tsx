import React, { useState } from 'react'
import ActiveComponents from './ActiveComponents'
import ActivePreviousSolutions from './ActivePreviousSolutions'

const Filter = () => {
    const [showFilter, setShowFilter] = useState(false)
    const toggleShowFilter = () => setShowFilter(!showFilter)

    return (
        <div className="multilane-filter-container">
            {showFilter && (
                <div className="multilane-filter__content">
                    <h2>Aktive Komponenten:</h2>
                    <ActiveComponents />
                    <h2>Lösungen:</h2>
                    <ActivePreviousSolutions />
                </div>
            )}

            <button
                className="button button--type-grey button--size-small multilane-filter-container__toggle"
                onClick={toggleShowFilter}
            >
                <i className={showFilter ? 'fas fa-chevron-left' : 'fas fa-chevron-right'} />
            </button>
        </div>
    )
}

export default React.memo(Filter)
