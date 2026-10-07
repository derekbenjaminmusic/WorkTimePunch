<?php

declare(strict_types=1);

namespace OCA\WorkTimePunch\Controller;

use OCA\WorkTimePunch\AppInfo\Application;
use OCA\WorkTimePunch\Service\PunchException;
use OCA\WorkTimePunch\Service\ClientSource;
use OCA\WorkTimePunch\Service\PunchService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use Throwable;

class PunchController extends Controller {
	public function __construct(
		IRequest $request,
		private ?string $userId,
		private PunchService $punchService,
		private LoggerInterface $logger,
		private IL10N $l10n,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	public function state(): JSONResponse {
		return new JSONResponse($this->punchService->stateForUser($this->userId));
	}

	#[NoAdminRequired]
	public function punch(string $punchAction): JSONResponse {
		try {
			return new JSONResponse($this->punchService->punch($this->userId, $punchAction, ClientSource::fromRequest($this->request)));
		} catch (PunchException $e) {
			return new JSONResponse([
				'error' => $e->getMessage(),
				'state' => $this->punchService->stateForUser($this->userId),
			], Http::STATUS_CONFLICT);
		} catch (Throwable $e) {
			$this->logger->error('WorkTimePunch action failed unexpectedly.', [
				'app' => Application::APP_ID,
				'action' => $punchAction,
				'userId' => $this->userId,
				'exception' => $e,
			]);
			return new JSONResponse([
				'error' => $this->l10n->t('WorkTimePunch could not complete the action.'),
				'state' => $this->punchService->stateForUser($this->userId),
			], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}
