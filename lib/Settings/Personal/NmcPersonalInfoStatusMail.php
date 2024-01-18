<?php

declare(strict_types=1);

namespace OCA\NMCSettings\Settings\Personal;

use OCA\MonthlyStatusEmail\Service\NotificationTrackerService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\IUserSession;
use OCP\Settings\ISettings;

class NmcPersonalInfoStatusMail implements ISettings {
	/**
	 * @var IInitialStateService
	 */
	private $initialState;
	/**
	 * @var NotificationTrackerService
	 */
	private $service;
	/**
	 * @var IUserSession
	 */
	private $userSession;

	public function __construct(
		IInitialStateService $initialState,
		NotificationTrackerService $service,
		IUserSession $userSession
	) {
		$this->initialState = $initialState;
		$this->service = $service;
		$this->userSession = $userSession;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$this->initialState->provideInitialState('monthly_status_email', 'opted-out', $this->service->find($this->userSession->getUser()->getUID())->getOptedOut());
		return new TemplateResponse('monthly_status_email', 'settings-personal', []);
	}

	/** {@inheritDoc} */
	public function getSection(): string {
		return 'account';
	}

	/** {@inheritDoc} */
	public function getPriority(): int {
		return 50;
	}
}
