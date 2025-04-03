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

use OCA\Empleados\Db\aniversarioMapper;
use OCA\Empleados\Db\aniversario;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class AniversariosController extends Controller {

    protected $l10n;
    protected $aniversarioMapper;

    public function __construct(
        IRequest $request,
        IL10N $l10n,
        aniversarioMapper $aniversarioMapper,
    ) {
        parent::__construct(Application::APP_ID, $request);
        
        $this->l10n = $l10n;
        $this->aniversarioMapper = $aniversarioMapper;
    }

    /**
     * Obtiene la lista de aniversarios.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function Getaniversarios(): array {
        return $this->aniversarioMapper->Getaniversarios();
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportListAniversarios(): array {
        $aniversarios = $this->aniversarioMapper->Getaniversarios();
        $books = [['id_universario', 'numero_aniversario', 'dias']];

        foreach ($aniversarios as $area) {
            $books[] = [
                $area['id_aniversario'],
                $area['numero_aniversario'],
                $area['dias'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('aniversarios.xlsx');
        return $books;
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function ImportListAniversarios(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->aniversarioMapper->updateAniversarios((int) $row[0], (int) $row[1], (float) $row[2]);
                } else {
                    $area = new aniversario();
                    $area->setnumero_aniversario($row[1]);
                    $area->setdias($row[2]);
                    $this->aniversarioMapper->insert($area);
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
        $this->departamentosMapper->updateAniversarios((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function AgregarNuevoAniversario(int $numero_aniversario, string $fecha_de, string $fecha_hasta, float $dias): void {
        $area = new aniversario();
        $area->setnumero_aniversario($numero_aniversario);
        $area->setfecha_de($fecha_de);
        $area->setfecha_hasta($fecha_hasta);
        $area->setdias($dias);
        $this->aniversarioMapper->insert($area);
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
