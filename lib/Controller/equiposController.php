<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IUserSession;
use OCP\IUserManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\equiposMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\equipos;
use OCA\Empleados\Db\configuraciones;
use OCA\Empleados\UploadException;
use OCP\IGroupManager;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de equipos de empleados en Nextcloud.
 */
class equiposController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $equiposMapper;
    protected $configuracionesMapper;
    protected $l10n;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        equiposMapper $equiposMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
		IGroupManager $groupManager
    ) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $empleadosMapper, $configuracionesMapper);

        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->equiposMapper = $equiposMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;
    }

    /**
     * Obtiene la lista de empleados y usuarios.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetUserLists(): array {
        return [
            'Empleados' => $this->empleadosMapper->GetUserLists(),
            'Users' => $this->empleadosMapper->getAllUsers(),
        ];
    }

    /**
     * Obtiene la lista de equipos en formato clave-valor.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEquiposFix(): array {
        return array_map(fn($equipo) => [
            'value' => $equipo['Id_equipos'],
            'label' => $equipo['Nombre'],
        ], $this->equiposMapper->GetEquiposList());
    }

    /**
     * Obtiene la lista de empleados.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosList(): array {
        return ['Empleados' => $this->empleadosMapper->GetUserLists()];
    }

    /**
     * Obtiene la lista de empleados con nombres corregidos si están vacíos.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosListFix(): array {
        return array_map(fn($empleado) => [
            'id' => $empleado['uid'],
            'displayName' => $empleado['displayname'] ?: $empleado['uid'],
            'user' => $empleado['uid'],
            'showUserStatus' => false,
        ], $this->empleadosMapper->GetUserLists());
    }

    /**
     * Activa un empleado.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function ActivarEmpleado(string $id_user): string {
        try {
            $timestamp = date('Y-m-d');
            $user = new empleados();
            $user->setid_user($id_user);
            $user->setcreated_at($timestamp);
            $user->setupdated_at($timestamp);
            $this->empleadosMapper->insert($user);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Elimina un empleado por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function EliminarEmpleado(int $id_empleados): string {
        try {
            $this->empleadosMapper->deleteByIdEmpleado($id_empleados);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Obtiene la lista de equipos.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEquiposList(): array {
        return $this->equiposMapper->GetEquiposList();
    }

    /**
     * Exporta la lista de equipos a un archivo XLSX.
     */
    public function ExportListEquipos(): array {
        $equipos = $this->equiposMapper->GetEquiposList();
        $books = [['Id_equipo', 'Nombre', 'created_at', 'updated_at']];

        foreach ($equipos as $equipo) {
            $books[] = [
                $equipo['Id_equipo'],
                $equipo['Nombre'],
                $equipo['created_at'],
                $equipo['updated_at'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('equipos.xlsx');
        return $books;
    }

    /**
     * Importa la lista de equipos desde un archivo XLSX.
     */
    public function ImportListEquipos(): void {
        $file = $this->getUploadedFile('equipofileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->equiposMapper->updateEquipos((string) $row[0], (string) $row[1]);
                } else {
                    $timestamp = date('Y-m-d');
                    $equipo = new equipos();
                    $equipo->setnombre((string) $row[1]);
                    $equipo->setcreated_at($timestamp);
                    $equipo->setupdated_at($timestamp);
                    $this->equiposMapper->insert($equipo);
                }
            }
        }
    }

    /**
     * Elimina un equipo por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function EliminarEquipo(int $id_equipo): string {
        try {
            $this->equiposMapper->EliminarEquipo((string) $id_equipo);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Guarda cambios en los equipos.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GuardarCambioEquipo(int $Id_Equipo, string $Id_jefe_equipo, string $Nombre): void {
        $this->equiposMapper->updateEquipos((string) $Id_Equipo, $Id_jefe_equipo, $Nombre);
    }

    /**
     * Crea un nuevo equipo.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearEquipo(string $nombre, string $jefe): void {
        $timestamp = date('Y-m-d');
        $equipo = new equipos();
        $equipo->setnombre($nombre);
        $equipo->setid_jefe_equipo($jefe);
        $equipo->setcreated_at($timestamp);
        $equipo->setupdated_at($timestamp);
        $this->equiposMapper->insert($equipo);
    }

    /**
     * Obtiene un archivo subido y maneja posibles errores.
     */
    private function getUploadedFile(string $key): array {
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new UploadException($this->l10n->t('Error en la subida del archivo.'));
        }
        return $file;
    }
}
