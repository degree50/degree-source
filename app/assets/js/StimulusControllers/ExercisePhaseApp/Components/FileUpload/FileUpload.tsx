import React, { useCallback } from 'react'
import { useDropzone } from 'react-dropzone'
import 'dropzone/dist/dropzone.css'

const FileUpload = () => {
    const onDrop = useCallback(() => {
        // TODO: implement!
        // send files to server
    }, [])
    const { getRootProps, getInputProps, isDragActive } = useDropzone({ onDrop })

    return (
        <div className={'dropzone'} {...getRootProps()}>
            <input {...getInputProps()} />
            <div className={'dz-message'}>
                {isDragActive ? (
                    <p>Drop the files here ...</p>
                ) : (
                    <p>Drag 'n' drop some files here, or click to select files</p>
                )}
            </div>
        </div>
    )
}

export default FileUpload
