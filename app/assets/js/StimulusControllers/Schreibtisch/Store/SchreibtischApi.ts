import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'
import { Course, Exercise, Fachbereich, Material, MaterialId, VideoFavorite, VideoFavoriteId } from '../types'

export const BASE_URL = `${window.location.origin.toString()}`
export const SERVER_BASE_URL = `${BASE_URL}/schreibtisch`

/****************
 * RTK Queries *
 ***************/

export const SchreibtischApi = createApi({
    baseQuery: fetchBaseQuery({
        baseUrl: SERVER_BASE_URL,
        credentials: 'include',
    }),
    reducerPath: 'schreibtischApi',
    tagTypes: ['Exercises', 'Videos', 'Material', 'Fachbereich', 'Course'],
    endpoints: (build) => ({
        exercises: build.query<Array<Exercise>, void>({
            query: () => 'exercises',
            providesTags: ['Exercises'],
        }),

        videoFavorites: build.query<Array<VideoFavorite>, void>({
            query: () => 'video-favorites',
            providesTags: ['Videos'],
        }),

        toggleVideoFavorite: build.mutation<void, VideoFavoriteId>({
            query: (videoFavoriteId) => ({
                url: `video-favorites/toggle/${videoFavoriteId}`,
                method: 'POST',
            }),
            invalidatesTags: ['Videos'],
        }),

        material: build.query<Array<Material>, void>({
            query: () => 'material',
            providesTags: ['Material'],
        }),

        updateMaterial: build.mutation<void, { material: string; id: MaterialId }>({
            query: (material) => ({
                url: `material/update/${material.id}`,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },

                body: { material: material.material },
            }),
            invalidatesTags: ['Material'],
        }),

        fachbereich: build.query<Record<Fachbereich['id'], Fachbereich>, void>({
            query: () => 'fachbereiche',
            providesTags: ['Fachbereich'],
        }),

        course: build.query<Record<Course['id'], Course>, void>({
            query: () => 'courses',
            providesTags: ['Course'],
        }),
    }),
})

// NOTE:
// RTK Query updates the redux store and these hooks will be informed about
// their state subscriptions.
// using a query of the same endpoint will also be re-rendered (e.g. components using useGetExercisesQuery())
export const {
    useExercisesQuery,
    useVideoFavoritesQuery,
    useToggleVideoFavoriteMutation,
    useMaterialQuery,
    useUpdateMaterialMutation,
    useFachbereichQuery,
    useCourseQuery,
} = SchreibtischApi
