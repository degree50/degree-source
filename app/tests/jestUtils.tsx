import React, { ReactNode } from 'react'
import { fireEvent, render as rtlRender, screen, waitFor } from '@testing-library/react'
import { configureStore, Reducer } from '@reduxjs/toolkit'
import { Provider } from 'react-redux'

const render = (
    ui: React.ReactElement,
    {
        reducer,
        preloadedState,
        ...renderOptions
    }: {
        reducer: Reducer
        preloadedState?: any
    }
) => {
    const store = configureStore({
        reducer,
        preloadedState,
    })

    function Wrapper({ children }: { children: ReactNode }) {
        // @ts-ignore
        return <Provider store={store}>{children}</Provider>
    }

    return rtlRender(ui, { wrapper: Wrapper, ...renderOptions })
}

// re-export everything
export * from '@testing-library/react'
// override render method
export { render, screen, fireEvent, waitFor }
