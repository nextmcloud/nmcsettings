<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NMCSettings\Settings\Personal\Security;

use OCA\EndToEndEncryption\Config as EConfig;
use OCA\EndToEndEncryption\IKeyStorage;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\IUserSession;
use OCP\Settings\ISettings;
use Psr\Container\ContainerInterface;

class NmcEndToEndEncryption implements ISettings {
	private IAppManager $appManager;
	private ContainerInterface $container;
	private IInitialStateService $initialState;
	private ?string $userId;
	private IUserSession $userSession;

	public function __construct(IAppManager $appManager, ContainerInterface $container, IInitialStateService $initialState, ?string $userId, IUserSession $userSession) {
		$this->appManager = $appManager;
		$this->container = $container;
		$this->initialState = $initialState;
		$this->userId = $userId;
		$this->userSession = $userSession;
	}

	public function getForm(): TemplateResponse {
		$user = $this->userSession->getUser();

		// e2ee classes only exist while the app is enabled, so resolve them lazily
		if (!$this->appManager->isEnabledForUser('end_to_end_encryption', $user)) {
			return new TemplateResponse('settings', 'settings/empty', [], '');
		}

		$isDisabledForUser = $this->container->get(EConfig::class)->isDisabledForUser($user);

		if ($isDisabledForUser) {
			return new TemplateResponse('settings', 'settings/empty', [], '');
		}

		assert($this->userId !== null, "We are always logged in inside the setting app");

		$keyStorage = $this->container->get(IKeyStorage::class);
		$hasKey = $keyStorage->publicKeyExists($this->userId)
			&& $keyStorage->privateKeyExists($this->userId);
			
		$this->initialState->provideInitialState('end_to_end_encryption', 'hasKey', $hasKey);

		$this->initialState->provideInitialState(
			'end_to_end_encryption', 
			'userConfig',
			['e2eeInBrowserEnabled' => false]
		);

		if (!$isDisabledForUser) {
			\OCP\Util::addStyle('end_to_end_encryption', 'end_to_end_encryption' . '-settings-personal');
			\OCP\Util::addScript('end_to_end_encryption', 'end_to_end_encryption' . '-settings-personal');
		}

		return new TemplateResponse(
			'end_to_end_encryption',
			'settings',
			["canUseApp" => !$isDisabledForUser]
		);
	}

	public function getSection(): string {
		return 'sessions';
	}

	public function getPriority(): int {
		return 5;
	}
}