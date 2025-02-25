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
use OCP\IGroupManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\departamentosMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\departamentos;
use OCA\Empleados\Db\configuraciones;
use OCA\Empleados\UploadException;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class AreasController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $departamentosMapper;
    protected $configuracionesMapper;
    protected $l10n;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        departamentosMapper $departamentosMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
        IGroupManager $groupManager
    ) {
        parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $configuracionesMapper);

        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->departamentosMapper = $departamentosMapper;
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
     * Obtiene la lista de áreas en formato clave-valor.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAreasFix(): array {
        return array_map(fn($area) => [
            'value' => $area['Id_departamento'],
            'label' => $area['Nombre'],
        ], $this->departamentosMapper->GetAreasList());
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
     * Obtiene la lista de áreas.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAreasList(): array {
        return $this->departamentosMapper->GetAreasList();
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportListAreas(): array {
        $areas = $this->departamentosMapper->GetAreasList();
        $books = [['Id_departamento', 'Id_padre', 'Nombre', 'created_at', 'updated_at']];

        foreach ($areas as $area) {
            $books[] = [
                $area['Id_departamento'],
                $area['Id_padre'],
                $area['Nombre'],
                $area['created_at'],
                $area['updated_at'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('areas.xlsx');
        return $books;
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function ImportListAreas(): void {
        $file = $this->getUploadedFile('AreafileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->departamentosMapper->updateAreas((string) $row[0], (string) $row[1], (string) $row[2]);
                } else {
                    $timestamp = date('Y-m-d');
                    $area = new departamentos();
                    $area->setid_padre((string) $row[1]);
                    $area->setnombre((string) $row[2]);
                    $area->setcreated_at($timestamp);
                    $area->setupdated_at($timestamp);
                    $this->departamentosMapper->insert($area);
                }
            }
        }
    }

    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function EliminarArea(int $id_departamento): string {
        try {
            $this->departamentosMapper->EliminarArea((string) $id_departamento);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Guarda cambios en las áreas.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GuardarCambioArea(int $id_departamento, string $padre, string $nombre): void {
        $this->departamentosMapper->updateAreas((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearArea(string $nombre, string $padre): void {
        $timestamp = date('Y-m-d');
        $area = new departamentos();
        $area->setid_padre($padre);
        $area->setnombre($nombre);
        $area->setcreated_at($timestamp);
        $area->setupdated_at($timestamp);
        $this->departamentosMapper->insert($area);
    }

    /**
     * Obtiene un archivo subido y maneja posibles errores.
     */
    #[UseSession]
    #[NoAdminRequired]
    private function getUploadedFile(string $key): array {
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new UploadException($this->l10n->t('Error en la subida del archivo.'));
        }
        return $file;
    }
}
