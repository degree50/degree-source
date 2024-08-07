import { memo } from 'react'
import { useMeter } from '@react-aria/meter'
import { AriaMeterProps } from '@react-types/meter'

const Meter = (props: AriaMeterProps) => {
    const { label, showValueLabel = !!label, value = 0, minValue = 0, maxValue = 100 } = props
    const { meterProps, labelProps } = useMeter(props)

    // Calculate the width of the progress bar as a percentage
    const percentage = (value - minValue) / (maxValue - minValue)
    const barWidth = `${Math.round(percentage * 100)}%`

    return (
        <div className="meter" {...meterProps}>
            <div className="meter__label">
                {label && <span {...labelProps}>{label}</span>}
                {showValueLabel && <span>{meterProps['aria-valuetext']}</span>}
            </div>
            <div className="meter__bar">
                <div style={{ width: barWidth }} />
            </div>
        </div>
    )
}

export default memo(Meter)
