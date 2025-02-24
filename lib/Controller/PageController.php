<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCA\Empleados\Db\configuracionesMapper;

class PageController extends BaseController {

	protected $configuracionesMapper;
	protected $session;

	public function __construct(
		IRequest $request, 
		ISession $session, 
		IUserSession $userSession, 
		IUserManager $userManager,
		configuracionesMapper $configuracionesMapper, 
		IGroupManager $groupManager
	) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $configuracionesMapper);

		$this->session = $session;
		$this->configuracionesMapper = $configuracionesMapper;
	}

	#[UseSession]
	#[NoCSRFRequired]
	#[NoAdminRequired]
	public function index(): TemplateResponse {
		$params = $this->getConfigParams();

		return new TemplateResponse(Application::APP_ID, 'index', ['config' => $params]);
	}
}
