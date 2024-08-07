import React, { useCallback } from 'react'
import { selectors, actions } from 'Components/VideoEditor/VideoEditorSlice'
import { connect } from 'react-redux'
import { FilterStateSlice } from 'Components/ToolbarItems/FilterContext/FilterSlice'
import getComponentName from 'Components/VideoEditor/components/MultiLane/getComponentName'

const mapStateToProps = (state: FilterStateSlice) => {
    return {
        components: selectors.filter.selectComponents(state),
    }
}

const mapDispatchToProps = {
    toggleComponent: actions.filter.toggleComponent,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ActiveComponents = (props: Props) => {
    const handleToggle = useCallback(
        (ev: React.ChangeEvent<HTMLInputElement>) => {
            props.toggleComponent(ev.currentTarget.value)
        },
        [props.toggleComponent]
    )

    return (
        <ul className="filter-list">
            {props.components.map((c) => (
                <li key={c.id}>
                    <label htmlFor={c.id}>
                        <input
                            id={c.id}
                            type="checkbox"
                            key={c.id}
                            checked={c.isVisible}
                            onChange={handleToggle}
                            value={c.id}
                        />
                        {getComponentName(c.id)}
                    </label>
                </li>
            ))}
        </ul>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(React.memo(ActiveComponents))
