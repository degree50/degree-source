import React from 'react'
import { Tab } from '../Tabs'
import { TabsTypesEnum } from 'types'
import { Translate } from 'react-i18nify'

const renderTabs = (tabs: Array<Tab>, activeTabId: TabsTypesEnum, setActiveTabId: (tab: TabsTypesEnum) => void) =>
    tabs.map((tab) => (
        <li role="presentation" key={tab.id}>
            <div role="tab" className={tab.id === activeTabId ? 'active' : ''} onClick={() => setActiveTabId(tab.id)}>
                {/* @ts-ignore */}
                <Translate value={tab.id} />
            </div>
        </li>
    ))

type Props = {
    activeTabId: TabsTypesEnum
    setActiveTabId: (tab: TabsTypesEnum) => void
    tabs: Array<Tab>
}

const EditorTabs = ({ tabs, activeTabId, setActiveTabId }: Props) => {
    return (
        <ul className="video-editor__tabs" role="tablist">
            {renderTabs(tabs, activeTabId, setActiveTabId)}
        </ul>
    )
}

export default React.memo(EditorTabs)
