import { Action, configureStore, ThunkAction } from '@reduxjs/toolkit'
import createSagaMiddleware from 'redux-saga'
import { RootSlice } from './RootSlice'
import { SchreibtischApi } from 'StimulusControllers/Schreibtisch/Store/SchreibtischApi'

export const sagaMiddleWare = createSagaMiddleware()

export const store = configureStore({
    reducer: RootSlice,
    middleware: (getDefaultMiddleware) => [...getDefaultMiddleware(), sagaMiddleWare, SchreibtischApi.middleware],
    devTools: {
        name: 'SchreibtischApp',
    },
})

export type AppState = ReturnType<typeof store.getState>
export type AppThunk<ReturnType = void> = ThunkAction<ReturnType, AppState, unknown, Action<string>>

export type AppDispatch = typeof store.dispatch
