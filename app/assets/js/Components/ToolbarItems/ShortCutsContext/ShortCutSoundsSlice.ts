import { createSlice, PayloadAction } from '@reduxjs/toolkit'

export type ShortCutSoundState = {
    isSoundEnabled: boolean
}

const initialState: ShortCutSoundState = {
    isSoundEnabled: false,
}

const ShortCutSoundsSlice = createSlice({
    name: 'ShortCutSoundOptions',
    initialState,
    reducers: {
        setShortCutSoundsState: (state: ShortCutSoundState, action: PayloadAction<ShortCutSoundState>) =>
            action.payload,
        setIsSoundEnabled: (state: ShortCutSoundState, action: PayloadAction<boolean>): ShortCutSoundState => {
            return {
                ...state,
                isSoundEnabled: action.payload,
            }
        },
    },
})

export default ShortCutSoundsSlice.reducer
export const { setIsSoundEnabled, setShortCutSoundsState } = ShortCutSoundsSlice.actions

export const selectShortCutSoundState = (state: { shortCutSoundOptions: ShortCutSoundState }) =>
    state.shortCutSoundOptions
export const selectIsSoundEnabled = (state: { shortCutSoundOptions: ShortCutSoundState }) =>
    state.shortCutSoundOptions.isSoundEnabled
