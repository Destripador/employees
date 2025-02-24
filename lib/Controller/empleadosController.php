<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ForbiddenException;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IL10N;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\departamentosMapper;
use OCA\Empleados\Db\configuracionesMapper;
use Shuchkin\SimpleXLSXGen;
use Shuchkin\SimpleXLSX;

/**
 * Controlador principal para la gestión de empleados en Nextcloud.
 */
class EmpleadosController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $groupManager;
    protected $empleadosMapper;
    protected $departamentosMapper;
    protected $configuracionesMapper;
    protected $session;
    protected $l10n;

    public function __construct(
        IRequest $request,
        ISession $session,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        departamentosMapper $departamentosMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
        IGroupManager $groupManager
    ) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $configuracionesMapper);

        $this->session = $session;
        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->departamentosMapper = $departamentosMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;
    }

    /**
     * Obtiene la lista de empleados, usuarios y desactivados.
     */
    #[UseSession]
    public function GetUserLists(): array {
        return [
            'Empleados' => $this->empleadosMapper->GetUserLists(),
            'Users' => $this->empleadosMapper->getAllUsers(),
            'Desactivados' => $this->empleadosMapper->GetUserListsDeactive(),
        ];
    }

    /**
     * Obtiene la lista de empleados con validación de acceso.
     */
    #[UseSession]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function GetEmpleadosList(): array {
        return ['Empleados' => $this->empleadosMapper->GetUserLists()];
    }

    /**
     * Obtiene empleados de un área específica.
     */
    #[UseSession]
    public function GetEmpleadosArea(string $id_area): array {
        return ['area' => $this->empleadosMapper->GetEmpleadosArea($id_area)];
    }

    /**
     * Obtiene empleados de un puesto específico.
     */
    #[UseSession]
    public function GetEmpleadosPuesto(string $id_puesto): array {
        return ['puesto' => $this->empleadosMapper->GetEmpleadosPuesto($id_puesto)];
    }

    /**
     * Activa un empleado y crea sus carpetas en Nextcloud.
     */
    #[UseSession]
    public function ActivarEmpleado(string $id_user): string {
        try {
            $user = $this->userManager->get($id_user);
            $gestor = $this->configuracionesMapper->GetGestor()[0]['Data'] ?? null;

            if ($gestor) {
                $userFolder = $this->rootFolder->getUserFolder($gestor);
                $folderPath = "EMPLEADOS/" . $id_user . " - " . strtoupper($user->getDisplayName());

                if (!$userFolder->nodeExists($folderPath)) {
                    foreach (["", "/CAPACITACIONES", "/DOCUMENTOS OFICIALES", "/DOCUMENTOS DE IDENTIFICACION", "/MEMORANDUMS"] as $subFolder) {
                        $userFolder->newFolder($folderPath . $subFolder);
                    }
                }

                $timestamp = date('Y-m-d');
                $empleado = new empleados();
                $empleado->setid_user($id_user);
                $empleado->setestado('1');
                $empleado->setcreated_at($timestamp);
                $empleado->setupdated_at($timestamp);

                $this->empleadosMapper->insert($empleado);
                return "ok";
            } else {
                return "No existe usuario gestor";
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Importa lista de empleados desde un archivo XLSX.
     */
    public function ImportListEmpleados(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
            $rows_info = $xlsx->rows();
            foreach (array_slice($rows_info, 1) as $row) { // Omitir encabezado
                $this->empleadosMapper->updateEmpleado(
                    (string) $row[0], (string) $row[2], $this->convertExcelDate($row[3]),
                    (string) $row[4], (string) $row[5], (string) $row[6], (string) $row[7],
                    (string) $row[8], (string) $row[9], (string) $row[10], (string) $row[11],
                    (string) $row[12], (string) $row[13], $this->convertExcelDate($row[14]),
                    (string) $row[15], (string) $row[16], (string) $row[17], (string) $row[18],
                    (string) $row[19], (string) $row[20], (string) $row[21], (string) $row[22],
                    (string) $row[23], (string) $row[24]
                );
            }
        }
    }

    /**
     * Convierte fechas de Excel a formato `Y-m-d`.
     */
    private function convertExcelDate($excelDate): string {
        if (empty($excelDate) || trim($excelDate) == 'dd/mm/aaaa') return '';

        return is_numeric($excelDate)
            ? date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excelDate))
            : (strtotime($excelDate) !== false ? date('Y-m-d', strtotime($excelDate)) : '');
    }

    /**
     * Obtiene el archivo subido y maneja errores.
     */
    private function getUploadedFile(string $key): array {
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \Exception("Error en la subida del archivo.");
        }
        return $file;
    }
}
