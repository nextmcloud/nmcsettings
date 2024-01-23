<!--
  - @copyright Copyright (c) 2020 Julius Härtl <jus@bitgrid.net>
  - @copyright Copyright (c) 2022 Greta Doci <gretadoci@gmail.com>
  -
  - @author Christopher Ng <chrng8@gmail.com>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
-->

<template>
	<section>
		<NcSettingsSection :name="t('nmcsettings', 'Appearance')"
			:limit-width="false"
			class="theming">
			<p v-html="description" />

			<h2 class="subheader">
				{{ t('nmcsettings', 'Design') }}
			</h2>

			<p v-html="themedescription" />

			<div class="theming__preview-list">
				<ItemPreview v-for="theme in themes"
					:key="theme.id"
					:enforced="theme.id === enforceTheme"
					:selected="selectedTheme.id === theme.id"
					:theme="theme"
					:unique="themes.length === 1"
					type="theme"
					@change="changeTheme" />
			</div>
		</NcSettingsSection>
	</section>
</template>

<script>
import { generateOcsUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import axios from '@nextcloud/axios'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'

import ItemPreview from './ItemPreview.vue'

const availableThemes = loadState('theming', 'themes', [])
const enforceTheme = loadState('theming', 'enforceTheme', '')
const shortcutsDisabled = loadState('theming', 'shortcutsDisabled', false)

const isUserThemingDisabled = loadState('theming', 'isUserThemingDisabled')

console.debug('Available themes', availableThemes)

export default {
	name: 'UserThemes',

	components: {
		ItemPreview,
		NcSettingsSection,
	},

	data() {
		return {
			availableThemes,
			enforceTheme,
			shortcutsDisabled,
			isUserThemingDisabled,
		}
	},

	computed: {
		themes() {
			return this.availableThemes.filter(theme => theme.type === 1)
		},

		fonts() {
			return this.availableThemes.filter(theme => theme.type === 2)
		},

		// Selected theme, fallback on first (default) if none
		selectedTheme() {
			return this.themes.find(theme => theme.enabled === true) || this.themes[0]
		},

		description() {
			// using the `t` replace method escape html, we have to do it manually :/
			return t(
				'nmcsettings',
				'Universal access is very important to us. We follow web standards and check to make everything usable also without mouse, and assistive software such as screenreaders. We aim to be compliant with the {guidelines}Web Content Accessibility Guidelines{linkend} 2.1 on AA level.'
			)
				.replace('{guidelines}', this.guidelinesLink)
				.replace('{linkend}', '</a>')
		},

		guidelinesLink() {
			return '<a target="_blank" href="https://www.w3.org/WAI/standards-guidelines/wcag/" rel="noreferrer nofollow">'
		},

		themedescription() {
			return t(
				'nmcsettings',
				'You can choose between a "Light Theme" and a "Dark Theme" for the display. In standard mode, MagentaCloud adapts to the theme of your system.'
			)
		},
	},

	watch: {
		shortcutsDisabled(newState) {
			this.changeShortcutsDisabled(newState)
		},
	},

	methods: {

		changeTheme({ enabled, id }) {
			// Reset selected and select new one
			this.themes.forEach(theme => {
				if (theme.id === id && enabled) {
					theme.enabled = true
					return
				}
				theme.enabled = false
			})

			this.updateBodyAttributes()
			this.selectItem(enabled, id)
		},

		changeFont({ enabled, id }) {
			// Reset selected and select new one
			this.fonts.forEach(font => {
				if (font.id === id && enabled) {
					font.enabled = true
					return
				}
				font.enabled = false
			})

			this.updateBodyAttributes()
			this.selectItem(enabled, id)
		},

		async changeShortcutsDisabled(newState) {
			if (newState) {
				await axios({
					url: generateOcsUrl('apps/provisioning_api/api/v1/config/users/{appId}/{configKey}', {
						appId: 'theming',
						configKey: 'shortcuts_disabled',
					}),
					data: {
						configValue: 'yes',
					},
					method: 'POST',
				})
			} else {
				await axios({
					url: generateOcsUrl('apps/provisioning_api/api/v1/config/users/{appId}/{configKey}', {
						appId: 'theming',
						configKey: 'shortcuts_disabled',
					}),
					method: 'DELETE',
				})
			}
		},

		updateBodyAttributes() {
			const enabledThemesIDs = this.themes.filter(theme => theme.enabled === true).map(theme => theme.id)
			const enabledFontsIDs = this.fonts.filter(font => font.enabled === true).map(font => font.id)

			this.themes.forEach(theme => {
				document.body.toggleAttribute(`data-theme-${theme.id}`, theme.enabled)
			})
			this.fonts.forEach(font => {
				document.body.toggleAttribute(`data-theme-${font.id}`, font.enabled)
			})

			document.body.setAttribute('data-themes', [...enabledThemesIDs, ...enabledFontsIDs].join(','))
		},

		/**
		 * Commit a change and force reload css
		 * Fetching the file again will trigger the server update
		 *
		 * @param {boolean} enabled the theme state
		 * @param {string} themeId the theme ID to change
		 */
		async selectItem(enabled, themeId) {
			try {
				if (enabled) {
					await axios({
						url: generateOcsUrl('apps/theming/api/v1/theme/{themeId}/enable', { themeId }),
						method: 'PUT',
					})
				} else {
					await axios({
						url: generateOcsUrl('apps/theming/api/v1/theme/{themeId}', { themeId }),
						method: 'DELETE',
					})
				}

			} catch (err) {
				console.error(err, err.response)
				OC.Notification.showTemporary(t('nmcsettings', err.response.data.ocs.meta.message + '. Unable to apply the setting.'))
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.theming {
	.theming__preview {
		display: block;
	}
	// Limit width of settings sections for readability
	p {
		max-width: 800px;
	}

	// Proper highlight for links and focus feedback
	&::v-deep a {
		font-weight: bold;

		&:hover,
		&:focus {
			text-decoration: underline;
		}
	}

	&__preview-list {
		--gap: 30px;

		display: grid;
		margin-top: var(--gap);
		column-gap: var(--gap);
		row-gap: var(--gap);
		grid-template-columns: 1fr 1fr;
	}

	@media (max-width: 1600px) {
		.theming__preview {
			display: flex;
		}
		.theming__preview-list {
			display: flex;
			flex-direction: column;
		}
	}
}

.background {
	&__grid {
		margin-top: 30px;
	}
}
</style>
