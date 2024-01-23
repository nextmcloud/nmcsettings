<?php

namespace OCA\NmcSettings\Sections\Personal;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class Appearance implements IIconSection {

	/** @var IL10N */
	private $l;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function getIcon() {
		return $this->urlGenerator->imagePath('nmctheme', 'settings/appearance.svg');
	}

	public function getID(): string {
		return 'appearance';
	}

	public function getName(): string {
		return $this->l->t('Appearance');
	}

	public function getPriority(): int {
		return -5;
	}
}
