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
use OCA\Empleados\Db\puestosMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\puestos;
use OCA\Empleados\Db\configuraciones;
use OCA\Empleados\UploadException;
use Shuchkin\SimpleXLSXGen;
use Shuchkin\SimpleXLSX;
use OCP\IGroupManager;

/**
 * Controlador para la gestión de puestos de empleados en Nextcloud.
 */
class PuestosController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $puestosMapper;
    protected $configuracionesMapper;
    protected $l10n;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        puestosMapper $puestosMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
		IGroupManager $groupManager
    ) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $configuracionesMapper);

        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->puestosMapper = $puestosMapper;
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
     * Obtiene la lista de puestos en formato clave-valor.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetPuestosFix(): array {
        return array_map(fn($puesto) => [
            'value' => $puesto['Id_puestos'],
            'label' => $puesto['Nombre'],
        ], $this->puestosMapper->GetPuestosList());
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
     * Obtiene la lista de puestos.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetPuestosList(): array {
        return $this->puestosMapper->GetPuestosList();
    }

    /**
     * Exporta la lista de puestos a un archivo XLSX.
     */
    public function ExportListPuestos(): array {
        $puestos = $this->puestosMapper->GetPuestosList();
        $books = [['Id_puesto', 'Nombre', 'created_at', 'updated_at']];

        foreach ($puestos as $puesto) {
            $books[] = [
                $puesto['Id_puesto'],
                $puesto['Nombre'],
                $puesto['created_at'],
                $puesto['updated_at'],
            ];
        }

        SimpleXLSXGen::fromArray($books)->downloadAs('puestos.xlsx');
        return $books;
    }

    /**
     * Importa la lista de puestos desde un archivo XLSX.
     */
    public function ImportListPuestos(): void {
        $file = $this->getUploadedFile('puestofileXLSX');
        if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->puestosMapper->updatePuestos((string) $row[0], (string) $row[1]);
                } else {
                    $timestamp = date('Y-m-d');
                    $puesto = new puestos();
                    $puesto->setnombre((string) $row[1]);
                    $puesto->setcreated_at($timestamp);
                    $puesto->setupdated_at($timestamp);
                    $this->puestosMapper->insert($puesto);
                }
            }
        }
    }

    /**
     * Elimina un puesto por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function EliminarPuesto(int $id_puesto): string {
        try {
            $this->puestosMapper->EliminarPuesto((string) $id_puesto);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Guarda cambios en los puestos.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GuardarCambioPuestos(int $id_puestos, string $nombre): void {
        $this->puestosMapper->updatePuestos((string) $id_puestos, $nombre);
    }

    /**
     * Crea un nuevo puesto.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearPuesto(string $nombre): void {
        $timestamp = date('Y-m-d');
        $puesto = new puestos();
        $puesto->setnombre($nombre);
        $puesto->setcreated_at($timestamp);
        $puesto->setupdated_at($timestamp);
        $this->puestosMapper->insert($puesto);
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
