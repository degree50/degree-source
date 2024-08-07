import { actions } from 'Components/VideoEditor/VideoEditorSlice'
import React, { memo } from 'react'
import { connect } from 'react-redux'
import Overlay from '../../components/Overlay'
import { ShortCutsOverlayIds } from '../ShortCutsMenu'
import { shortCutIds } from '../ShortCutsSlice'
import ShortCutConfiguration from './ShortCutConfiguration'
import { AppState } from '../../../../StimulusControllers/ExerciseAndSolutionStore/Store'
import { selectIsSoundEnabled, setIsSoundEnabled } from '../ShortCutSoundsSlice'
import Checkbox from '../../../Checkbox'
import { persistSoundOptionsAction } from '../shortCutSoundsSaga'

const mapStateToProps = (state: AppState) => ({
    isSoundEnabled: selectIsSoundEnabled(state),
})

const mapDispatchToProps = {
    closeOverlay: actions.overlay.unsetOverlay,
    setIsSoundEnabled: setIsSoundEnabled,
    persistSoundOptions: persistSoundOptionsAction,
}

type Props = ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ShortCutsConfigurationOverlay = (props: Props) => {
    const close = () => {
        props.closeOverlay(ShortCutsOverlayIds.configureShortCuts)
    }

    const handleIsSoundEnabledChange = (isEnabled: boolean) => {
        props.setIsSoundEnabled(isEnabled)
        props.persistSoundOptions()
    }

    return (
        <Overlay closeCallback={close} title="Tastenkombinationen">
            <p className="short-cut-configuration__info-block">
                <i className="fas fa-info-circle" />
                <span>Hinweis: Änderungen werden sofort übernommen.</span>
            </p>
            <Checkbox isSelected={props.isSoundEnabled} onChange={handleIsSoundEnabledChange}>
                Ton aktiviert bei Verwendung der Tastenkombination
            </Checkbox>
            <hr />
            {shortCutIds.map((shortCutId) => (
                <ShortCutConfiguration shortCutId={shortCutId} key={shortCutId} />
            ))}
        </Overlay>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ShortCutsConfigurationOverlay))
