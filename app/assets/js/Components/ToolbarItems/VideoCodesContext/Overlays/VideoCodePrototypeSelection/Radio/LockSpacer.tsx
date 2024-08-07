import React from 'react'

/**
 * This is only used for spacing between Radio elements in parent prototypes which are user created and therefore do not have a Lock-icon
 */
const LockSpacer = () => {
    return <div className="video-code-select__option__lock-spacer"></div>
}

export default React.memo(LockSpacer)
