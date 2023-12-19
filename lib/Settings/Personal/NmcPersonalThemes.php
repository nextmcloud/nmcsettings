<?php

declare(strict_types=1);

namespace OCA\NMCSettings\Settings\Personal;

use OCA\Theming\ITheme;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\ThemingDefaults;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;
use OCP\Util;

class NmcPersonalThemes implements ISettings {

	private IConfig $config;
	private ThemesService $themesService;
	private IInitialStateService $initialStateService;
	private ThemingDefaults $themingDefaults;

	public function __construct(IConfig $config,
		ThemesService $themesService,
		IInitialStateService $initialStateService,
		ThemingDefaults $themingDefaults
	) {
		$this->config = $config;
		$this->themesService = $themesService;
		$this->initialStateService = $initialStateService;
		$this->themingDefaults = $themingDefaults;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$enforcedTheme = $this->config->getSystemValueString('enforce_theme', '');

		$themes = array_map(function ($theme) {
			return [
				'id' => $theme->getId(),
				'type' => $theme->getType(),
				'title' => $theme->getTitle(),
				'enableLabel' => $theme->getEnableLabel(),
				'description' => $theme->getDescription(),
				'enabled' => $this->themesService->isEnabled($theme),
			];
		}, $this->themesService->getThemes());

		if ($enforcedTheme !== '') {
			$themes = array_filter($themes, function ($theme) use ($enforcedTheme) {
				return $theme['type'] !== ITheme::TYPE_THEME || $theme['id'] === $enforcedTheme;
			});
		}

		$isUserThemingDisabled = $this->themingDefaults->isUserThemingDisabled();
		$isUserThemingDisabled = true;

		$this->initialStateService->provideInitialState('theming', 'themes', array_values($themes));
		$this->initialStateService->provideInitialState('theming', 'enforceTheme', $enforcedTheme);
		$this->initialStateService->provideInitialState('theming', 'isUserThemingDisabled', $isUserThemingDisabled);
		
		Util::addScript('nmcsettings', 'nmcsettings-themes');

		return new TemplateResponse('nmcsettings', 'settings/personal/themes');
	}

	/** {@inheritDoc} */
	public function getSection(): string {
		return 'themes';
	}

	/** {@inheritDoc} */
	public function getPriority(): int {
		return 50;
	}
}
