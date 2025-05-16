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
use OCA\Empleados\UploadException;
use OCP\AppFramework\Http\DataResponse;

use OCA\Empleados\Db\configuracionesMapper;
use OCP\Files\IRootFolder;
use OCP\IUserManager;

use OCP\IUserSession;

use DateTime;

use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\ausenciasMapper;
use OCA\Empleados\Db\tipoausenciaMapper;
use OCA\Empleados\Db\historialausenciasMapper;
use OCA\Empleados\Db\ausencias;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class AusenciasController extends Controller {

    protected $userSession;
    protected $l10n;
    protected $empleadosMapper;
    protected $ausenciasMapper;
    protected $tipoausenciaMapper;
    protected $historialausenciasMapper;
    protected $configuracionesMapper;

    protected $userManager;

    protected IRootFolder $rootFolder;

    public function __construct(
        IRequest $request,
        IL10N $l10n,
        ausenciasMapper $ausenciasMapper,
        historialausenciasMapper $historialausenciasMapper,
        tipoausenciaMapper $tipoausenciaMapper,
        empleadosMapper $empleadosMapper,
        IUserSession $userSession,
        configuracionesMapper $configuracionesMapper,
        IRootFolder $rootFolder,
        IUserManager $userManager,
    ) {
        parent::__construct(Application::APP_ID, $request, $userSession, $configuracionesMapper);
        
        $this->l10n = $l10n;
        $this->empleadosMapper = $empleadosMapper;
        $this->ausenciasMapper = $ausenciasMapper;
        $this->tipoausenciaMapper = $tipoausenciaMapper;
        $this->historialausenciasMapper = $historialausenciasMapper;
        $this->userSession = $userSession;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->rootFolder = $rootFolder;
        $this->userManager = $userManager;
    }

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusencias(): array {
        return $this->ausenciasMapper->Getausencias();
    }

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasByUser($id): array {
        return $this->ausenciasMapper->GetAusenciasByUser($id);
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportListAusencias(): array {
        $ausencias = $this->ausenciasMapper->Getausencias();
        $books = [['id_universario', 'numero_ausencias', 'dias']];

        foreach ($ausencias as $area) {
            $books[] = [
                $area['id_ausencias'],
                $area['numero_ausencias'],
                $area['dias'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('ausencias.xlsx');
        return $books;
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function ImportListAusencias(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->ausenciasMapper->updateAusencias((int) $row[0], (int) $row[1], (float) $row[2]);
                } else {
                    $area = new ausencias();
                    $area->setnumero_ausencias($row[1]);
                    $area->setdias($row[2]);
                    $this->ausenciasMapper->insert($area);
                }
            }
        }
    }
        
    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function VaciarAusencias(): string {
        try {
            $this->ausenciasMapper->VaciarAusencias();
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
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
        $this->departamentosMapper->updateAusencias((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function AgregarNuevoAniversario(int $numero_ausencias, string $fecha_de, string $fecha_hasta, float $dias): void {
        $area = new ausencias();
        $area->setnumero_ausencias($numero_ausencias);
        $area->setfecha_de($fecha_de);
        $area->setfecha_hasta($fecha_hasta);
        $area->setdias($dias);
        $this->ausenciasMapper->insert($area);
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

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAniversarioByDate(string $ingreso): array {
        $fechaInicio = new DateTime($ingreso);
        $hoy = new DateTime();
    
        $diferencia = $hoy->diff($fechaInicio);
    
        return $this->ausenciasMapper->GetAniversarioByDate($diferencia->y);

    }
    /**
    * Generar solicitud de ausencia con sus respectivos archivos adjuntos.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function EnviarAusencia(): DataResponse {
        $files = $_FILES['archivos'] ?? [];
        $fileCount = is_array($files['name']) ? count($files['name']) : 0;
    
        $user = $this->userSession->getUser();
        $gestor = $this->configuracionesMapper->GetGestor()[0]['Data'] ?? null;
    
        if (!$gestor) {
            throw new \Exception('No se encontró la carpeta del gestor de información.');
        }
    
        $userFolder = $this->rootFolder->getUserFolder($gestor);
        $folderPath = "EMPLEADOS/" . $user->getUID() . " - " . strtoupper($user->getDisplayName()) . "/JUSTIFICANTES";
    
        if (!$userFolder->nodeExists($folderPath)) {
            $userFolder->newFolder($folderPath);
        }
    
        $carpetaDestino = $userFolder->get($folderPath);
        $fechaActual = (new \DateTime())->format('Y-m-d');
    
        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
    
            if (is_uploaded_file($tmpName)) {
                $content = file_get_contents($tmpName);
    
                // Separar extensión
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    
                // Construir nuevo nombre
                $newName = $fechaActual . '-' . $baseName . '.' . $extension;
    
                if ($carpetaDestino->nodeExists($newName)) {
                    $carpetaDestino->get($newName)->putContent($content);
                } else {
                    $carpetaDestino->newFile($newName)->putContent($content);
                }
            }
        }
        
        $id_tipo_ausencia = $this->request->getParam('id_tipo_ausencia');
        $dias_solicitados = $this->request->getParam('dias_solicitados');
        $fecha_de = $this->request->getParam('fecha_de');
        $fecha_hasta = $this->request->getParam('fecha_hasta');
        $prima_vacacional = $this->request->getParam('prima_vacacional');
        $notas = $this->request->getParam('notas');

        // aqui se disminuyen los dias de la ausencia
        $tipo_ausencia = $this->tipoausenciaMapper->getTipoById($id_tipo_ausencia);
        $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());
        $empleado_ausencias = $this->ausenciasMapper->GetAusenciasByUser($id_empleado[0]['Id_empleados']);
        if (!empty($tipo_ausencia) && $tipo_ausencia[0]['solicitar_prima_vacacional'] == 1) {
            $dias_disponibles = $empleado_ausencias[0]['dias_disponibles'] - $dias_solicitados;
            $this->ausenciasMapper->updateAusenciasEmpleado($empleado_ausencias[0]['id_ausencias'], $dias_disponibles);
        }
        
        $fecha_de = DateTime::createFromFormat('d/m/Y', $this->request->getParam('fecha_de'))->format('Y-m-d');
        $fecha_hasta = DateTime::createFromFormat('d/m/Y', $this->request->getParam('fecha_hasta'))->format('Y-m-d');

        // Registro en el historial de ausencias 
        $this->historialausenciasMapper->EnviarAusencia((int) $id_tipo_ausencia, $empleado_ausencias[0]['id_ausencias'], $fecha_de, $fecha_hasta, (int) $prima_vacacional, $notas, $empleado_ausencias[0]['id_aniversario']);

        return new DataResponse(['success' => true, 'message' => $tipo_ausencia[0]['solicitar_prima_vacacional']]);
    }    

}
