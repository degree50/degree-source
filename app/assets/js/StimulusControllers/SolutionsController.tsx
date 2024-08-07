import { Controller } from 'stimulus'
import ReactDOM from 'react-dom'
import { Provider } from 'react-redux'
import { store, sagaMiddleWare } from './ExerciseAndSolutionStore/Store'
import { ConfigState, actions as configActions } from './ExercisePhaseApp/Components/Config/ConfigSlice'
import { setTranslations, setLocale } from 'react-i18nify'
import i18n from 'StimulusControllers/i18n'
import { normalizeFilterData } from './normalizeData'
import { actions as videoEditorActions } from 'Components/VideoEditor/VideoEditorSlice'
import SolutionsApp from './SolutionsApp'
import { initData } from 'Components/VideoEditor/initData'
import { DataState } from './ExerciseAndSolutionStore/DataSlice'
import rootSaga from './ExerciseAndSolutionStore/rootSaga'

setTranslations(i18n)
setLocale('de')

sagaMiddleWare.run(rootSaga)

class SolutionsController extends Controller {
    connect() {
        const propsAsString = this.data.get('props')
        const props = propsAsString ? JSON.parse(propsAsString) : {}

        const data = props.data as DataState
        const config = props.config as ConfigState

        // set initial Redux state
        store.dispatch(configActions.hydrateConfig(config))
        store.dispatch(configActions.setIsSolutionView())

        store.dispatch(initData(data))

        store.dispatch(videoEditorActions.filter.init(normalizeFilterData(config, data)))

        ReactDOM.render(
            <Provider store={store}>
                <SolutionsApp />
            </Provider>,
            this.element
        )
    }
}

export default SolutionsController
