import { Controller } from 'stimulus'
import Axios from 'axios'
import { getColorName } from 'ntc-ts'

export default class extends Controller {
    connect() {
        const formSubmitButton = document.getElementById('add-new-video-code')
        const videoCodeNameInput = document.getElementById('video-code-name')
        const endpoint = this.data.get('endpoint')
        const updateOnSuccess = this.data.get('update')
        const videoCodesList = document.getElementById(updateOnSuccess)

        /**
         * WHY:
         * We want screen reader to read the color as name instead of hex value to improve a11y UX.
         * (issue: https://gitlab.sandstorm.de/degree-4.0/code/-/issues/241)
         *
         * This is achieved by adding aria labels for items in the video codes list where we translate
         * the hex value to color name using ntc-ts (ts implementation of NameThatColor).
         *
         * We have to call this function every time the list changes because we get pre-rendered html from the API.
         *
         * The ntc-ts library does the conversion from hex value to color name (exact match or approximately).
         *
         * Example: getColorName('#000000').name === 'black'
         *
         */
        const updateVideoCodeListAriaLabels = () => {
            videoCodesList.querySelectorAll('li.video-codes-list-item').forEach((el) => {
                el.setAttribute(
                    'aria-label',
                    `
                    Video-Code.
                    Name: ${el.getAttribute('data-code-name')}.
                    Farbe: ${getColorName(el.getAttribute('data-code-color')).name}.
                `
                )
            })
        }

        const updateVideoCodesList = (videoCodesList) => {
            const updateEndPoint = videoCodesList.getAttribute('data-update-endpoint')

            Axios.post(updateEndPoint)
                .then(function (response) {
                    videoCodesList.innerHTML = response.data
                    updateVideoCodeListAriaLabels()
                })
                .catch(function (e) {
                    console.error('>>>>> update attachment list failed', e)
                })
        }

        /**
         * WHY:
         * We want to read the colors that are picked with a screen reader.
         * So we hook into the "change" event of the color picker and
         * translate the color via ntc-ts into a human-readable name.
         *
         * For more info:
         * @see updateVideoCodeListAriaLabels
         */
        const nativeColorInput = this.element.querySelector('input[type="color"]')
        nativeColorInput.setAttribute('aria-label', `Gewählte Farbe: ${getColorName(nativeColorInput.value).name}`)
        nativeColorInput.addEventListener('change', (ev) => {
            ev.target.setAttribute('aria-label', `Gewählte Farbe: ${getColorName(ev.target.value).name}`)
        })

        // initially disable the submit button
        formSubmitButton.setAttribute('disabled', '')

        videoCodeNameInput.addEventListener('input', (ev) => {
            if (ev.target.value === '') {
                formSubmitButton.setAttribute('disabled', '')
            } else {
                formSubmitButton.removeAttribute('disabled')
            }
        })

        formSubmitButton.onclick = () => {
            const color = document.getElementById('video-code-color').value
            const name = document.getElementById('video-code-name').value

            formSubmitButton.setAttribute('disabled', '')

            if (color && name) {
                Axios.post(endpoint, {
                    color: color,
                    name: name,
                })
                    .then(function () {
                        updateVideoCodesList(videoCodesList)
                        videoCodeNameInput.value = ''
                    })
                    .catch(function (e) {
                        console.error('>>>>> add video-code failed', e)
                        formSubmitButton.removeAttribute('disabled')
                    })
            }
        }

        updateVideoCodeListAriaLabels()
    }
}
