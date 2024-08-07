import {Controller} from 'stimulus'

function setCookie(cname, cvalue) {
    document.cookie = cname + '=' + cvalue
}

function getCookie(cname) {
    const name = cname + '='
    const decodedCookie = decodeURIComponent(document.cookie)
    const ca = decodedCookie.split(';')
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i]
        while (c.charAt(0) === ' ') {
            c = c.substring(1)
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length)
        }
    }
    return ''
}

export default class extends Controller {
    connect() {
        const sidebar = this.data.element
        const sidebarToggle = document.getElementById('sidebar-toggle')

        const toggleSidebar = (closeSidebar) => {
            if (closeSidebar) {
                sidebar.classList.add('sidebar--is-closed')
            } else {
                sidebar.classList.remove('sidebar--is-closed')
            }
        }

        sidebarToggle.onclick = (event) => {
            event.preventDefault()
            const sidebarIsOpen = getCookie('sidebarIsOpen') !== 'false'
            toggleSidebar(sidebarIsOpen)
            setCookie('sidebarIsOpen', !sidebarIsOpen)
        }
    }
}
