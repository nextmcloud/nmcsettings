const allowedPersonalSections = ['account', 'sessions', 'appearance']

function isHiddenPersonalSection(href) {
	const match = href.match(/\/settings\/user(?:\/([^/?#]+))?/)
	return match !== null && !allowedPersonalSections.includes(match[1])
}

// NC 34+ renders the navigation with Vue: no data-section-* attributes, and Vue owns the nodes, so hide instead of remove
function filterVueNavigation(navigation) {
	navigation.querySelectorAll('li.app-navigation-entry-wrapper').forEach(function(entry) {
		const link = entry.querySelector('a')
		if (link && isHiddenPersonalSection(link.getAttribute('href'))) {
			entry.style.display = 'none'
		}
	})

	if (!navigation.querySelector('a[href*="/settings/admin"]')) {
		navigation.querySelectorAll('.app-navigation-caption').forEach(function(caption) {
			caption.style.display = 'none'
		})
	}
}

function whenVueNavigationRendered(callback) {
	const findNavigation = () => document.querySelector('#app-navigation-vue:has(li.app-navigation-entry-wrapper)')
	const navigation = findNavigation()
	if (navigation) {
		callback(navigation)
		return
	}

	const observer = new MutationObserver(function() {
		const rendered = findNavigation()
		if (rendered) {
			observer.disconnect()
			callback(rendered)
		}
	})
	observer.observe(document.body, { childList: true, subtree: true })
}

window.addEventListener('DOMContentLoaded', function() {

	const settingsBody = document.getElementById('body-settings')
	if (!settingsBody) return

	if (document.querySelector('[data-section-type]') === null) {
		whenVueNavigationRendered(filterVueNavigation)
	}

	// removes app navigation elements
	document.querySelectorAll('[data-section-type]').forEach(function(element) {
		const type = element.attributes['data-section-type'].value
		if (type === 'personal') {
			const id = element.attributes['data-section-id'].value
			if (id !== 'account' && id !== 'sessions' && id !== 'appearance') {
				element.remove()
			}
		}
	})

	// shows hidden app navigation elements in NC25
	document.querySelectorAll('#app-navigation li a').forEach(function(element) {
		const href = element.href
		const admin = '/admin'
		const account = '/account'
		const sessions = '/sessions'
		const themes = '/appearance'

		if (href.includes(admin) || href.includes(account) || href.includes(sessions) || href.includes(themes)) {
			if (href.includes(admin)) {
				document.querySelectorAll('.app-navigation-caption').forEach(function(caption) {
					caption.style.display = 'flex'
				})
			}
			element.parentElement.style.display = 'flex'
		}
	})
})
