import Button from 'Components/Button/Button'
import CKEditor from 'Components/CKEditor'
import React, { useCallback, useEffect, useState } from 'react'
import { NavLink, useLocation, useParams } from 'react-router-dom'
import { ROUTE_EDIT_MATERIAL, ROUTE_MATERIALIEN } from '../Schreibtisch'
import { useMaterialQuery, useUpdateMaterialMutation } from '../Store/SchreibtischApi'

const MaterialEditor = () => {
    const { pathname } = useLocation()

    useEffect(() => {
        if (pathname.includes(ROUTE_EDIT_MATERIAL)) {
            // eslint-disable-next-line functional/immutable-data
            document.title = 'Schreibtisch - Materialien | Editieren'
        }
    }, [pathname])

    const { id } = useParams()
    const { material } = useMaterialQuery(undefined, {
        selectFromResult: ({ data }) => ({
            material: data?.find((material) => material.id === id),
        }),
    })

    const [transientMaterial, setTransientMaterial] = useState(material?.material)
    const [updateMaterialOnServer, { isLoading }] = useUpdateMaterialMutation()

    const onChange = useCallback(
        (updatedMaterial: string) => {
            setTransientMaterial(updatedMaterial)
        },
        [setTransientMaterial]
    )

    const saveMaterial = useCallback(() => {
        if (material) {
            updateMaterialOnServer({
                material: transientMaterial ?? material.material,
                id: material.id,
            })
        }
    }, [transientMaterial, updateMaterialOnServer, material])

    return (
        <div className="material-editor__wrapper">
            <div className="material-editor" data-test-id="materialEditor">
                {material !== undefined ? (
                    <CKEditor value={material.material} onChange={onChange} />
                ) : (
                    <div>Es ist kein Material hinterlegt...</div>
                )}
                <footer className="material-editor__footer">
                    <div className="button-group">
                        <NavLink to={ROUTE_MATERIALIEN} className="button button--type-outline-primary">
                            Zurück
                        </NavLink>

                        <a href={material?.originalExercisePhaseUrl} className="button button--type-outline-primary">
                            Zur ursprünglichen Phase
                        </a>
                    </div>

                    <Button className="button button--type-primary" onPress={saveMaterial}>
                        {isLoading ? <i className="fas fa-spinner fa-spin"></i> : 'Speichern'}
                    </Button>
                </footer>
            </div>
        </div>
    )
}

export default React.memo(MaterialEditor)
