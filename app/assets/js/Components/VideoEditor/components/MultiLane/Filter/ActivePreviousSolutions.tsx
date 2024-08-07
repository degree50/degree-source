import React, { useCallback } from 'react'
import { connect } from 'react-redux'
import { actions, selectors } from 'StimulusControllers/ExerciseAndSolutionStore/rootSlice'
import CheckboxWithIndeterminate, { CheckboxValue } from '../../CheckboxWithIndeterminate'
import { GlobalSolutionFilter } from 'Components/ToolbarItems/FilterContext/FilterSlice'
import { AppState } from 'StimulusControllers/ExerciseAndSolutionStore/Store'

const mapStateToProps = (state: AppState) => {
    const solutions = selectors.data.solutions.selectById(state)
    const previousSolutionsFromFilter = selectors.videoEditor.filter.selectPreviousSolutions(state)
    const previousSolutions = previousSolutionsFromFilter.map((s) => ({
        ...s,
        ...solutions[s.id],
    }))
    const globalSolutionFilter = selectors.videoEditor.filter.selectGlobalSolutionFilter(state)

    return {
        previousSolutions,
        globalSolutionFilter,
    }
}

const mapDispatchToProps = {
    togglePreviousSolution: actions.videoEditor.filter.setPreviousSolution,
    setGlobalSolutionFilter: actions.videoEditor.filter.setGlobalSolutionFilter,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ActivePreviousSolutions = (props: Props) => {
    const handleToggle = useCallback(
        (ev: React.ChangeEvent<HTMLInputElement>) => {
            props.togglePreviousSolution({
                id: ev.currentTarget.value,
                isVisible: ev.currentTarget.checked,
            })
        },
        [props]
    )

    const handleGlobalSolutionFilterChange = (value: CheckboxValue) => {
        props.setGlobalSolutionFilter(mapCheckboxValueToGlobalSolutionFilter(value))
    }

    const globalSolutionFilterCheckboxAriaLabel = `${
        props.globalSolutionFilter === GlobalSolutionFilter.ALL ? 'Alle abwählen' : 'Alle auswählen'
    }`

    return (
        <div>
            {props.previousSolutions.length > 0 ? (
                <>
                    <div className="global-filter">
                        <CheckboxWithIndeterminate
                            id="globalFilter"
                            value={mapGlobalSolutionFilterToCheckboxValue(props.globalSolutionFilter)}
                            handleChange={handleGlobalSolutionFilterChange}
                            aria-label={globalSolutionFilterCheckboxAriaLabel}
                        />
                        <label htmlFor="globalFilter">Alle</label>
                    </div>
                    <ul className="filter-list">
                        {props.previousSolutions.map((s) => (
                            <li key={s.id}>
                                <label htmlFor={s.id}>
                                    <input
                                        id={s.id}
                                        type="checkbox"
                                        key={s.id}
                                        checked={s.isVisible}
                                        onChange={handleToggle}
                                        value={s.id}
                                    />
                                    {s.fromGroupPhase ? 'Gruppe von' : ''} {s.userName}
                                </label>
                            </li>
                        ))}
                    </ul>
                </>
            ) : (
                <p>Keine Lösungen vorhanden.</p>
            )}
        </div>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(ActivePreviousSolutions))

function mapCheckboxValueToGlobalSolutionFilter(value: CheckboxValue) {
    switch (value) {
        case CheckboxValue.CHECKED:
            return GlobalSolutionFilter.ALL
        case CheckboxValue.UNCHECKED:
            return GlobalSolutionFilter.NONE
        case CheckboxValue.INDETERMINATE:
            return GlobalSolutionFilter.MIXED
    }
}

function mapGlobalSolutionFilterToCheckboxValue(value: GlobalSolutionFilter) {
    switch (value) {
        case GlobalSolutionFilter.ALL:
            return CheckboxValue.CHECKED
        case GlobalSolutionFilter.NONE:
            return CheckboxValue.UNCHECKED
        case GlobalSolutionFilter.MIXED:
            return CheckboxValue.INDETERMINATE
    }
}
