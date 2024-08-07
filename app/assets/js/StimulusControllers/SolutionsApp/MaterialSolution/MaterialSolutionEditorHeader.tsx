import { memo, ReactNode } from 'react'
import Button from 'Components/Button/Button'

type Props = {
    onNext: () => void
    onPrev: () => void
    canSelectNext: boolean
    canSelectPrev: boolean
    nextLabel: string
    prevLabel: string
    children: ReactNode
}

const MaterialSolutionEditorHeader = (props: Props) => {
    return (
        <div className="material-solution__solution-header">
            <Button
                className="button button--type-primary"
                onPress={props.onPrev}
                isDisabled={!props.canSelectPrev}
                title={props.prevLabel}
            >
                <i className=" fa-regular fa-chevron-left"></i>
            </Button>
            {props.children}
            <Button
                className="button button--type-primary"
                onPress={props.onNext}
                isDisabled={!props.canSelectNext}
                title={props.nextLabel}
            >
                <i className=" fa-regular fa-chevron-right"></i>
            </Button>
        </div>
    )
}

export default memo(MaterialSolutionEditorHeader)
