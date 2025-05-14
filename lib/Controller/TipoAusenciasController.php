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

use DateTime;

use OCA\Empleados\Db\tipoausenciaMapper;
use OCA\Empleados\Db\tipoausencia;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class TipoAusenciasController extends Controller {

    protected $l10n;
    protected $tipoausenciaMapper;

    public function __construct(
        IRequest $request,
        IL10N $l10n,
        tipoausenciaMapper $tipoausenciaMapper,
    ) {
        parent::__construct(Application::APP_ID, $request);
        
        $this->l10n = $l10n;
        $this->tipoausenciaMapper = $tipoausenciaMapper;
    }

    /**
     * Obtiene la lista de tipoausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function getTipo(): array {
        return $this->tipoausenciaMapper->getTipo();
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportarTipo(): array {
        $tipoausencias = $this->tipoausenciaMapper->getTipo();
        $books = [['id_tipo_ausencia', 'nombre', 'descripcion', 'solicitar_archivo', 'solicitar_prima_vacacional']];

        foreach ($tipoausencias as $tipo) {
            $books[] = [
                $tipo['id_tipo_ausencia'],
                $tipo['nombre'],
                $tipo['descripcion'],
                $tipo['solicitar_archivo'],
                $tipo['solicitar_prima_vacacional'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('tipoausencias.xlsx');
        return $books;
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function importarTipo(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->tipoausenciaMapper->updateTipoAusencias((int) $row[0], (string) $row[1], (string) $row[2], (bool) $row[3], (bool) $row[34]);
                } else {
                    $tipo = new tipoausencia();
                    $tipo->setnombre($row[1]);
                    $tipo->setdescripcion($row[2]);
                    $tipo->setsolicitar_archivo($row[3]);
                    $tipo->setsolicitar_prima_vacacional($row[4]);
                    $this->tipoausenciaMapper->insert($tipo);
                }
            }
        }
    }
        
    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function VaciarTipo(): string {
        try {
            $this->tipoausenciaMapper->VaciarTipo();
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
        $this->departamentosMapper->updateTipoAusencias((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function AgregarNuevoTipo(string $nombre, string $descripcion, int $solicitar_archivo): void {
        $tipo = new tipoausencia();
        $tipo->setnombre($nombre);
        $tipo->setdescripcion($descripcion);
        $tipo->setsolicitar_archivo($solicitar_archivo);
        $this->tipoausenciaMapper->insert($tipo);
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
     * Obtiene la lista de tipoausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAniversarioByDate(string $ingreso): array {
        $fechaInicio = new DateTime($ingreso);
        $hoy = new DateTime();
    
        $diferencia = $hoy->diff($fechaInicio);
    
        return $this->tipoausenciaMapper->GetAniversarioByDate($diferencia->y);

    }
}
