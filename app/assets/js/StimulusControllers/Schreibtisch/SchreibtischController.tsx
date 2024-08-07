import { Controller } from 'stimulus'
import ReactDOM from 'react-dom'
import React from 'react'
import { Provider } from 'react-redux'
import { store } from './Store/SchreibtischStore'
import Schreibtisch from 'StimulusControllers/Schreibtisch/Schreibtisch'

class SchreibtischController extends Controller {
    connect() {
        ReactDOM.render(
            <Provider store={store}>
                <Schreibtisch />
            </Provider>,
            this.element
        )
    }
}

export default SchreibtischController
