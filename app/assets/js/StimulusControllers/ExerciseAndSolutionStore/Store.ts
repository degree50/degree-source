import { Action, configureStore, ThunkAction } from '@reduxjs/toolkit'
import createSagaMiddleware from 'redux-saga'
import { RootReducer } from './rootSlice'

export const sagaMiddleWare = createSagaMiddleware()

export const store = configureStore({
    reducer: RootReducer,
    middleware: (getDefaultMiddleware) => [...getDefaultMiddleware(), sagaMiddleWare],
    devTools: {
        name: 'ExercisePhaseApp',
    },
})

export type AppState = ReturnType<typeof store.getState>
export type AppThunk<ReturnType = void> = ThunkAction<ReturnType, AppState, unknown, Action<string>>

export type AppDispatch = typeof store.dispatch
