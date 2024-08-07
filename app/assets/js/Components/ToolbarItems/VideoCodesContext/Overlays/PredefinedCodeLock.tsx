import React from 'react'

const PredefinedCodeLock = () => {
    const label = 'Dies ist ein Vorgegebener Video-Code und kann nicht bearbeitet werden.'
    return <i className={'video-code__locked fas fa-lock'} title={label} aria-label={label} />
}

export default React.memo(PredefinedCodeLock)
