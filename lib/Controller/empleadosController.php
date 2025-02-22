<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\StorageInvalidException;
use OCP\Files\StorageNotAvailableException;

use OCP\IRequest;
use OCP\ISession;
use OCP\Util;
use OCP\AppFramework\Http\Response;
use DateTime;
use DateTimeZone;

use OCP\IL10N;
use OCA\Empleados\UploadException;


#dependencias agregadas
use OCP\IUserSession;
use OCP\IUserManager;
use OCP\IGroupManager;

use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\empleados;

use OCA\Empleados\Db\departamentosMapper;
use OCA\Empleados\Db\departamentos;

use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\configuraciones;
/*
use OCA\Empleados\Db\puestosMapper;
use OCA\Empleados\Db\puestos;
*/

require_once 'SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

require_once 'SimpleXLSX.php';
use Shuchkin\SimpleXLSX;


use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ForbiddenException;

/**
 * @psalm-suppress UnusedClass
 */
class empleadosController extends Controller {

	private $userSession;
	private $userManager;
	private $empleadosMapper;
	private $departamentosMapper;
	private $configuracionesMapper;

	protected IRootFolder $rootFolder;

	private $session;
	private IL10N $l10n;

	public function __construct(IRequest $request, ISession $session, IUserSession $userSession, IUserManager $userManager, empleadosMapper $empleadosMapper, departamentosMapper $departamentosMapper, IL10N $l10n, IRootFolder $rootFolder, configuracionesMapper $configuracionesMapper, IGroupManager $groupManager) {
		parent::__construct(Application::APP_ID, $request);

		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->userManager = $userManager;
		$this->empleadosMapper = $empleadosMapper;
		$this->departamentosMapper = $departamentosMapper;
		$this->configuracionesMapper = $configuracionesMapper;
		
		$this->rootFolder = $rootFolder;

		$this->ISession = $session;
		
		$this->l10n = $l10n;
		
	}


	#[UseSession]
	public function GetUserLists(): array{
		$empleados = $this->empleadosMapper->GetUserLists();
		$users = $this->empleadosMapper->getAllUsers();
		$deactivated = $this->empleadosMapper->GetUserListsDeactive();
		
		$data = array(
			'Empleados' => $empleados,
			'Users' => $users,
			'Desactivados' => $deactivated,
        );

		return $data;
	}

	#[UseSession]
	#[NoCSRFRequired]
	#[NoAdminRequired] // Asegura que no requiera ser admin
	public function GetEmpleadosList(): array{
		if (!$this->userHasAccess()) {
			throw new \OCP\AppFramework\Http\ForbiddenException("No tienes permiso para acceder a este módulo.");
		}
		$empleados = $this->empleadosMapper->GetUserLists();
		
		$data = array(
			'Empleados' => $empleados,
        );

		return $data;
	}

	#[UseSession]
	public function GetEmpleadosArea(string $id_area): array{
		$area = $this->empleadosMapper->GetEmpleadosArea($id_area);
		
		$data = array(
			'area' => $area,
        );

		return $data;
	}

	#[UseSession]
	public function GetEmpleadosPuesto(string $id_puesto): array{
		$puesto = $this->empleadosMapper->GetEmpleadosPuesto($id_puesto);
		
		$data = array(
			'puesto' => $puesto,
        );

		return $data;
	}

	#[UseSession]
	public function GetEmpleadosListFix(): array{
		$empleados = $this->empleadosMapper->GetUserLists();
		$data = [];
		foreach($empleados as $empleado){
			if($empleado['displayname'] == null || $empleado['displayname'] == ""){
				$empleado['displayname'] = $empleado['uid'];
			}
			$datas = array(
				'id' => $empleado['uid'],
				'displayName' => $empleado['displayname'],
				'user' => $empleado['uid'],
                'showUserStatus' => false,
			);
			$data[] = $datas;
		}

		return $data;
	}

	#[UseSession]
	public function ActivarEmpleado(string $id_user): string {
		try{
			$user = $this->userManager->get($id_user);
			$gestor = $this->configuracionesMapper->GetGestor();
			$currenGestor = $gestor[0]['Data'] ?? null; // Usa null coalescing operator para evitar errores si 'Data' no existe

			if (!empty($currenGestor)) { // Verifica que el usuario existe
				try {
					$userFolder = $this->rootFolder->getUserFolder($currenGestor);

					// Ruta EMPLEADOS/usuario/
					$folderPath = "EMPLEADOS/". $id_user . " - " . strtoupper($user->getDisplayName());

					if (!$userFolder->nodeExists($folderPath)) {
						$userFolder->newFolder($folderPath);
						$userFolder->newFolder($folderPath . "/CAPACITACIONES");
						$userFolder->newFolder($folderPath . "/DOCUMENTOS OFICIALES");
						$userFolder->newFolder($folderPath . "/DOCUMENTOS DE IDENTIFICACION");
						$userFolder->newFolder($folderPath . "/MEMORANDUMS");
					}
					
					$timestamp = date('Y-m-d');
					$user = new empleados();
					$user->setid_user($id_user);
					$user->setestado('1');
					$user->setcreated_at($timestamp);
					$user->setupdated_at($timestamp);

					$insertedUser = $this->empleadosMapper->insert($user);
					$userId = $insertedUser->getid_user();

				} catch (Exception $e) {
					return $e->getMessage();
				}
			} else {
				return "No existe usuario gestor";
			}
			return "ok";
		}
		catch(Exception $e){
			return $e;
		}
	}

	
	#[UseSession]
	public function DesactivarEmpleado(int $id_empleados): string {
		try{

			$this->empleadosMapper->DesactivarByIdEmpleado($id_empleados);

			
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	
	#[UseSession]
	public function ActivarUsuario(int $id_empleados): string {
		try{

			$this->empleadosMapper->ActivarByIdEmpleado($id_empleados);

			
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}
	public function ImportListEmpleados(): void {
		$file = $this->getUploadedFile('fileXLSX');
		if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
	
			$rows_info = $xlsx->rows();
			$firstRow = true; // Para omitir el encabezado si es necesario
	
			foreach ($rows_info as $row) {
				// Omitir la primera fila si contiene encabezados
				if ($firstRow) {
					$firstRow = false;
					continue;
				}

				$this->empleadosMapper->updateEmpleado(
					(string) $row[0], 
					(string) $row[2], 
					$this->convertExcelDate($row[3]), // Fecha validada y convertida
					(string) $row[4], (string) $row[5], (string) $row[6], (string) $row[7], 
					(string) $row[8], (string) $row[9], (string) $row[10], (string) $row[11], 
					(string) $row[12], (string) $row[13],
					$this->convertExcelDate($row[14]),// Fecha validada y convertida
					(string) $row[15], (string) $row[16], (string) $row[17], (string) $row[18], (string) $row[19], 
					(string) $row[20], (string) $row[21], (string) $row[22], (string) $row[23], 
					(string) $row[24]
				);
			}
		}
	}
	
	private function convertExcelDate($excelDate): string {
		if (empty($excelDate) || trim($excelDate) == 'dd/mm/aaaa') {
			return ''; // Devolver vacío si la fecha no es válida
		}
	
		if (is_numeric($excelDate)) {
			$timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excelDate);
			return date('Y-m-d', $timestamp); // Formato correcto para input type="date"
		}
	
		if (strtotime($excelDate) !== false) {
			return date('Y-m-d', strtotime($excelDate));
		}
	
		return ''; // Si no es una fecha válida, devolver vacío
	}
	
	
	

	private function getUploadedFile(string $key): array {
		$file = $this->request->getUploadedFile($key);
		$error = null;
		$phpFileUploadErrors = [
			UPLOAD_ERR_OK => $this->l10n->t('The file was uploaded'),
			UPLOAD_ERR_INI_SIZE => $this->l10n->t('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
			UPLOAD_ERR_FORM_SIZE => $this->l10n->t('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
			UPLOAD_ERR_PARTIAL => $this->l10n->t('The file was only partially uploaded'),
			UPLOAD_ERR_NO_FILE => $this->l10n->t('No file was uploaded'),
			UPLOAD_ERR_NO_TMP_DIR => $this->l10n->t('Missing a temporary folder'),
			UPLOAD_ERR_CANT_WRITE => $this->l10n->t('Could not write file to disk'),
			UPLOAD_ERR_EXTENSION => $this->l10n->t('A PHP extension stopped the file upload'),
		];

		if (empty($file)) {
			throw new UploadException($this->l10n->t('No file uploaded or file size exceeds maximum of %s', [Util::humanFileSize(Util::uploadLimit())]));
		}
		if (array_key_exists('error', $file) && $file['error'] !== UPLOAD_ERR_OK) {
			throw new UploadException($phpFileUploadErrors[$file['error']]);
		}
		return $file;
	}

	#[UseSession]
	public function EliminarEmpleado(int $id_empleados): string {
		try{

			$this->empleadosMapper->deleteByIdEmpleado($id_empleados);

			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	#[UseSession]
	#[NoCSRFRequired]
	public function GetAreasList(): array{
		$Areas = $this->departamentosMapper->GetAreasList();
	
		return $Areas;
	}

	public function ExportListEmpleados(): array{
		$empleados = $this->empleadosMapper->GetUserLists();
		
		$books = [[
		'Id_empleados', 
		'Id_user', 
		'Numero_empleado', 
		'Ingreso', 
		'Correo_contacto', 
		'Id_departamento', 
		'Id_puesto', 
		'Id_gerente', 
		'Id_socio', 
		'Fondo_clave',
		'Fondo_ahorro',
		'Numero_cuenta', 
		'Equipo_asignado', 
		'Sueldo', 
		'Fecha_nacimiento', 
		'Estado',
		'Direccion',
		'Estado_civil',
		'Telefono_contacto',
		'Curp',
		'Rfc',
		'Imss',
		'Genero',
		'Contacto_emergencia',
		'Numero_emergencia',
		'created_at', 
		'updated_at', 
		]];

		foreach($empleados as $datas){
			array_push(
				$books, 
				[
					$datas['Id_empleados'], 
					$datas['Id_user'], 
					$datas['Numero_empleado'], 
					$datas['Ingreso'], 
					$datas['Correo_contacto'], 
					$datas['Id_departamento'], 
					$datas['Id_puesto'], 
					$datas['Id_gerente'], 
					$datas['Id_socio'], 
					$datas['Fondo_clave'], 
					$datas['Fondo_ahorro'], 
					$datas['Numero_cuenta'], 
					$datas['Equipo_asignado'], 
					$datas['Sueldo'], 
					$datas['Fecha_nacimiento'], 
					$datas['Estado'],
					$datas['Direccion'],
					$datas['Estado_civil'],
					$datas['Telefono_contacto'],
					$datas['Curp'],
					$datas['Rfc'],
					$datas['Imss'],
					$datas['Genero'],
					$datas['Contacto_emergencia'],
					$datas['Numero_emergencia'],
					$datas['created_at'], 
					$datas['updated_at'], 
				]);
		}

		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $books );
		//$xlsx->saveAs('books.xlsx'); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx 
	
		$fileContent = $xlsx->downloadAs('php://memory');

		return $books; 
	}

	public function GuardarNota(int $id_empleados, string $nota): void {
		$this->empleadosMapper->GuardarNota(strval($id_empleados), $nota);
	}

	public function CambiosEmpleado($id_empleados, $numeroempleado, $ingreso, $area, $puesto, $socio, $gerente, $fondoclave, $fondoahorro, $numerocuenta, $equipoasignado, $sueldo): void {
		$this->empleadosMapper->CambiosEmpleado($id_empleados, $numeroempleado, $ingreso, $area, $puesto, $socio, $gerente, $fondoclave, $fondoahorro, $numerocuenta, $equipoasignado, $sueldo);
	}

	public function CambiosPersonal($Id_empleados, $Direccion, $Estado_civil, $Telefono_contacto, $Rfc, $Imss, $Contacto_emergencia, $Numero_emergencia, $Curp, $Fecha_nacimiento, $Correo_contacto, $Genero): void {
		$this->empleadosMapper->CambiosPersonal($Id_empleados, $Direccion, $Estado_civil, $Telefono_contacto, $Rfc, $Imss, $Contacto_emergencia, $Numero_emergencia, $Curp, $Fecha_nacimiento, $Correo_contacto, $Genero);
	}
	private function userHasAccess(): bool {
		$allowedGroups = ['admin', 'empleados', 'recursos_humanos']; // Define los grupos permitidos
		$user = $this->userSession->getUser();
	
		if (!$user) {
			return false; // Usuario no autenticado
		}
	
		// Obtener los grupos a los que pertenece el usuario
		$userGroups = $this->groupManager->getUserGroups($user);
	
		foreach ($userGroups as $group) {
			if (in_array($group->getGID(), $allowedGroups)) {
				return true; // El usuario pertenece a un grupo permitido
			}
		}
	
		return false;
	}
	
}
