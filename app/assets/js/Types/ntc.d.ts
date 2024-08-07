/**
 * WHY:
 * With this Feature we are able to convert color hex values to color names.
 * Those can be read by screen readers to provide a better a11y UX regarding colors.
 *
 * Colors are most commonly used in VideoCodePrototypes.
 */

/**
 * WHY not use typings of ntc-ts itself:
 * The library is written in TS and exports typings as well but TS can not resolve those typings correctly
 * probably due to the _way_ the typings are exported.
 * We could open an issue or even a PR for it, but for now this will do.
 *
 * see repo: https://github.com/Danetag/ntc-ts
 * see original typings here: "node-modules/ntc-ts/dist/src/index.d.ts"
 */

declare module 'ntc-ts' {
    export type COLOR = Array<string | number>

    export const ORIGINAL_COLORS: COLOR[]

    export function initColors(_colors: COLOR[]): void

    export type GetColorNameResult = {
        exactMatch: boolean
        name: string
        rgb: string | null
    }

    export function getColorName(hexValue?: string): GetColorNameResult
}
