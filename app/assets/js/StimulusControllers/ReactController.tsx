import { Controller } from 'stimulus'
import { Provider } from 'react-redux'
import ReactDOM from 'react-dom'
import widgets from '../Widgets/Index'
import { store, sagaMiddleWare } from './ExerciseAndSolutionStore/Store'
import rootSaga from './ExerciseAndSolutionStore/rootSaga'

sagaMiddleWare.run(rootSaga)

class ReactController extends Controller {
    connect() {
        const widgetName = this.data.get('widget')
        const propsAsString = this.data.get('props')

        // @ts-ignore
        const ReactWidget = widgets[widgetName]
        const props = propsAsString ? JSON.parse(propsAsString) : {}

        ReactDOM.render(
            <Provider store={store}>
                <ReactWidget {...props} />
            </Provider>,
            this.element
        )
    }
}

export default ReactController
