import { ComponentId } from 'StimulusControllers/ExercisePhaseApp/Components/Config/ConfigSlice'
import { TabsTypesEnum } from 'types'

export default function getComponentName(componentId: ComponentId) {
    switch (componentId) {
        case TabsTypesEnum.VIDEO_CODES: {
            return 'Codierungen'
        }

        case TabsTypesEnum.VIDEO_ANNOTATIONS: {
            return 'Annotationen'
        }

        case TabsTypesEnum.VIDEO_CUTTING: {
            return 'Schnitte'
        }

        default: {
            return '<Unbekannte Komponente>'
        }
    }
}
