<?php

declare(strict_types=1);

namespace OCA\NMCSettings\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Groupware implements ISettings {

	public function __construct() {
		
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		return new TemplateResponse('nmcsettings', 'settings-personal', []);
	}

	/** {@inheritDoc} */
	public function getSection(): string {
		return 'groupware';
	}

	/** {@inheritDoc} */
	public function getPriority(): int {
		return 0;
	}
}