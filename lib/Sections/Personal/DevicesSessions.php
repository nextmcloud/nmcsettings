<?php

namespace OCA\NmcSettings\Sections\Personal;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class DevicesSessions implements IIconSection {
	private IL10N $l;
	private IURLGenerator $urlGenerator;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('nmctheme', 'settings/replacement.svg');
	}

	public function getID(): string {
		return 'sessions';
	}

	public function getName(): string {
		return $this->l->t('Devices & sessions');
	}

	public function getPriority(): int {
		return -10;
	}
}
