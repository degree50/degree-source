import React, { ChangeEvent, FocusEventHandler, memo } from 'react'
import { connect } from 'react-redux'
import { AppState } from '../../../../StimulusControllers/ExerciseAndSolutionStore/Store'
import {
    allShortCutModifiers,
    selectShortCutConfigurationById,
    ShortCutId,
    ShortCutModifierId,
    toggleModifierForShortCut,
    setKeyForShortCut,
    selectHotKeyByShortCutId,
} from '../ShortCutsSlice'
import { persistShortCuts } from '../ShortCutsSaga'
import Accordion from '../../../Accordion/Accordion'

const modifierToLabelMap: Record<ShortCutModifierId, string> = {
    [ShortCutModifierId.CTRL]: 'Strg (Ctrl)',
    [ShortCutModifierId.ALT]: 'Alt (Win/Linux)',
    [ShortCutModifierId.OPTION]: 'Option (Mac)',
    [ShortCutModifierId.SHIFT]: 'Umschalt (Shift)',
}

const shortCutIdToLabelMap: Record<ShortCutId, string> = {
    [ShortCutId.TOGGLE_PLAY]: 'Video Wiedergabe/Pause',
    [ShortCutId.SET_CURRENT_TIME_AS_START_VALUE]: 'Aktuelle Player-Zeit als Startzeit verwenden',
    [ShortCutId.SET_CURRENT_TIME_AS_END_VALUE]: 'Aktuelle Player-Zeit als Endzeit verwenden',
    [ShortCutId.CREATE_ANNOTATION]: 'Neue Annotation erstellen',
    [ShortCutId.CREATE_VIDEO_CODE]: 'Neue Codierung erstellen',
    [ShortCutId.CREATE_VIDEO_CUT]: 'Neuen Schnitt erstellen',
    [ShortCutId.SET_VIDEO_PLAYER_TIME]: 'Zu bestimmter Zeit im Video Springen',
    [ShortCutId.TOGGLE_VIDEO_FAVORITE]: 'Video zu/von Favoriten hinzufügen/entfernen',
    [ShortCutId.SHOW_GESAMTLISTE]: 'Gesamtliste anzeigen',
}

type OwnProps = {
    shortCutId: ShortCutId
}

const mapStateToProps = (state: AppState, ownProps: OwnProps) => ({
    shortCutConfiguration: selectShortCutConfigurationById(state, ownProps.shortCutId),
    shortCutText: selectHotKeyByShortCutId(state, ownProps.shortCutId),
})

const mapDispatchToProps = {
    toggleModifierForShortCut,
    setKeyForShortCut,
    persistShortCuts,
}

type Props = OwnProps & ReturnType<typeof mapStateToProps> & typeof mapDispatchToProps

const ShortCutConfiguration = (props: Props) => {
    const handleKeyFocus: FocusEventHandler<HTMLInputElement> = (ev) => {
        ev.target.select()
    }

    const handleKeyInput = (ev: ChangeEvent<HTMLInputElement>) => {
        const newKey = ev.target.value

        // prevent empty value
        if (newKey !== '') {
            props.setKeyForShortCut({ shortCutId: props.shortCutId, key: newKey })
            props.persistShortCuts()
        }

        //
        /**
         * WHY: Hack: Pressing `delete` or `carriage return` somehow prevents the browser from selecting the input value again.
         *
         * HOW I think it works:
         *  This "Hack" with setTimeout registers the ev.target.select() call further down the stack so whatever the
         *  browser is doing right after delete or carriage return is pressed.. this callback is executed after it.
         */
        setTimeout(() => {
            ev.target.select()
        }, 0)
    }

    const title = (
        <span className="short-cut-configuration__title">
            <span>{shortCutIdToLabelMap[props.shortCutId]}</span>
            <span className="hot-key-text">({props.shortCutText})</span>
        </span>
    )

    const inputId = `${props.shortCutId}-key`

    return (
        <Accordion title={title} buttonTitleClose="Bearbeitung beenden" buttonTitleOpen="TastenKombination bearbeiten">
            <div className="short-cut-configuration">
                <div className="short-cut-configuration__modifier-list">
                    {allShortCutModifiers.map((modifierId) => {
                        const id = `shortCut-${props.shortCutId}-modifier--${modifierId}`

                        const enabled = props.shortCutConfiguration.modifiers[modifierId].enabled
                        const handleChange = () => {
                            props.toggleModifierForShortCut({
                                shortCutId: props.shortCutId,
                                modifierId: modifierId,
                            })
                            props.persistShortCuts()
                        }

                        return (
                            <label
                                key={id}
                                htmlFor={id}
                                className="short-cut-configuration__modifier highlight-focus-within"
                            >
                                <input id={id} type="checkbox" checked={enabled} onChange={handleChange} />
                                {modifierToLabelMap[modifierId]}
                            </label>
                        )
                    })}
                </div>
                <label htmlFor={inputId}>
                    Taste:
                    <input
                        id={inputId}
                        type="text"
                        maxLength={1}
                        value={props.shortCutConfiguration.key}
                        onInput={handleKeyInput}
                        onFocus={handleKeyFocus}
                        className="short-cut-configuration__key"
                    />
                </label>
            </div>
        </Accordion>
    )
}

export default connect(mapStateToProps, mapDispatchToProps)(memo(ShortCutConfiguration))
