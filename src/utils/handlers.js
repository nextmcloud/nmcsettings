import { showError } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'

import logger from '../logger.js'

/**
 * @param {import('axios').AxiosError} error the error
 * @param {string?} message the message to display
 */
export const handleError = (error, message) => {
	let fullMessage = ''

	if (message) {
		fullMessage += message
	}

	if (error.response?.status === 429) {
		if (fullMessage) {
			fullMessage += '\n'
		}
		fullMessage += t('nmcsettings', 'There were too many requests from your network. Retry later or contact your administrator if this is an error.')
	}

	showError(fullMessage)
	logger.error(fullMessage || t('Error'), error)
}
