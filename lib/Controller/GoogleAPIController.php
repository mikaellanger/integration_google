<?php
/**
 * Nextcloud - google
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2020
 */

namespace OCA\Google\Controller;

use OCP\App\IAppManager;
use OCP\Files\IAppData;
use OCP\AppFramework\Http\DataDisplayResponse;

use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\IServerContainer;
use OCP\IL10N;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCP\AppFramework\Http\ContentSecurityPolicy;

use Psr\Log\LoggerInterface;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Google\Service\GoogleAPIService;
use OCA\Google\AppInfo\Application;

class GoogleAPIController extends Controller {


	private $userId;
	private $config;
	private $dbconnection;
	private $dbtype;

	public function __construct($AppName,
								IRequest $request,
								IServerContainer $serverContainer,
								IConfig $config,
								IL10N $l10n,
								IAppManager $appManager,
								IAppData $appData,
								LoggerInterface $logger,
								GoogleAPIService $googleAPIService,
								$userId) {
		parent::__construct($AppName, $request);
		$this->userId = $userId;
		$this->AppName = $AppName;
		$this->l10n = $l10n;
		$this->appData = $appData;
		$this->serverContainer = $serverContainer;
		$this->config = $config;
		$this->logger = $logger;
		$this->googleAPIService = $googleAPIService;
		$this->accessToken = $this->config->getUserValue($this->userId, Application::APP_ID, 'token', '');
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getImportPhotosInformation(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$response = new DataResponse([
			'importing_photos' => $this->config->getUserValue($this->userId, Application::APP_ID, 'importing_photos', '') === '1',
			'last_import_timestamp' => (int) $this->config->getUserValue($this->userId, Application::APP_ID, 'last_import_timestamp', '0'),
			'nb_imported_photos' => (int) $this->config->getUserValue($this->userId, Application::APP_ID, 'nb_imported_photos', '0'),
		]);
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getImportDriveInformation(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$response = new DataResponse([
			'importing_drive' => $this->config->getUserValue($this->userId, Application::APP_ID, 'importing_drive', '') === '1',
			'last_drive_import_timestamp' => (int) $this->config->getUserValue($this->userId, Application::APP_ID, 'last_drive_import_timestamp', '0'),
			'nb_imported_files' => (int) $this->config->getUserValue($this->userId, Application::APP_ID, 'nb_imported_files', '0'),
		]);
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getPhotoNumber(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->getPhotoNumber($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getContactNumber(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->getContactNumber($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getCalendarList(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->getCalendarList($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function getDriveSize(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->getDriveSize($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function importPhotos(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->startImportPhotos($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function importDrive(): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->startImportDrive($this->accessToken, $this->userId);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $calId
	 * @param string $calName
	 * @param ?string $color
	 * @return DataResponse
	 */
	public function importCalendar(string $calId, string $calName, ?string $color = null): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->importCalendar($this->accessToken, $this->userId, $calId, $calName, $color);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param ?string $uri
	 * @param int $key
	 * @param ?string $newAddressBookName
	 * @return DataResponse
	 */
	public function importContacts(?string $uri = '', int $key, ?string $newAddressBookName = ''): DataResponse {
		if ($this->accessToken === '') {
			return new DataResponse(null, 400);
		}
		$result = $this->googleAPIService->importContacts($this->accessToken, $this->userId, $uri, $key, $newAddressBookName);
		if (isset($result['error'])) {
			$response = new DataResponse($result['error'], 401);
		} else {
			$response = new DataResponse($result);
		}
		return $response;
	}
}
