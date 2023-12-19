import Vue from 'vue'
import App from '../components/UserThemes.vue'

// bind to window
Vue.prototype.OC = OC
Vue.prototype.t = t

const View = Vue.extend(App)
const theming = new View()
theming.$mount('#theming')

theming.$on('update:background', () => {
	// Refresh server-side generated theming CSS
	[...document.head.querySelectorAll('link.theme')].forEach(theme => {
		const url = new URL(theme.href)
		url.searchParams.set('v', Date.now())
		const newTheme = theme.cloneNode()
		newTheme.href = url.toString()
		newTheme.onload = () => theme.remove()
		document.head.append(newTheme)
	})
})
