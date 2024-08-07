import { CustomVideoControlConfig } from 'Components/VideoEditor/useCustomVideoJsComponent'
import { setPlayerTimeControlAction } from './SetPlayerTimeControlSaga'

export const SetPlayerTimeControl: CustomVideoControlConfig = {
    controlText: 'Springe zu Zeit in Video',
    ariaLabel: 'Springe zu Zeit in Video',
    iconClassNames: ['far', 'fa-stopwatch'],
    action: setPlayerTimeControlAction(),
    indexPosition: 1,
}
