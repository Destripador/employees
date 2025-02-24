<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IGroupManager;
use OCA\Empleados\Db\configuracionesMapper;

abstract class BaseController extends Controller {

    protected $userSession;
    protected $groupManager;
    protected $configuracionesMapper;
    protected $configParams = []; // 🔹 Aquí almacenaremos la configuración

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        IGroupManager $groupManager,
        configuracionesMapper $configuracionesMapper // 🔹 Agregamos el Mapper de configuración
    ) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->configuracionesMapper = $configuracionesMapper;

        $this->checkAccess(); // 🔥 Validar accesos automáticamente
        $this->loadConfigParams(); // 🔥 Cargar configuración automáticamente
    }

    private function checkAccess(): void {
        $allowedGroups = ['admin', 'empleados', 'recursos_humanos'];
        $user = $this->userSession->getUser();

        if (!$user) {
            throw new OCSForbiddenException("❌ Debes estar autenticado para acceder a este módulo.");
        }

        $userGroups = $this->groupManager->getUserGroups($user);
        if (!$userGroups || count($userGroups) === 0) {
            throw new OCSForbiddenException("⚠️ No perteneces a ningún grupo permitido para acceder.");
        }

        foreach ($userGroups as $group) {
            $groupId = $group->getGID();
            if ($groupId && in_array($groupId, $allowedGroups)) {
                return; // ✅ Acceso permitido
            }
        }

        throw new OCSForbiddenException("🚫 No tienes permiso para acceder a este módulo. Contacta al administrador.");
    }

    /**
     * 🔥 Carga la configuración automáticamente para todos los controladores.
     */
    private function loadConfigParams(): void {
        $configMap = array_column($this->configuracionesMapper->GetConfig(), 'Data', 'Nombre');

        $this->configParams = [
            "usuario_almacenamiento" => $configMap['usuario_almacenamiento'] ?? null,
            "automatic_save_note" => $configMap['automatic_save_note'] ?? null,
        ];
    }

    /**
     * 📌 Permite que los controladores accedan a la configuración cargada.
     */
    public function getConfigParams(): array {
        return $this->configParams;
    }
}
