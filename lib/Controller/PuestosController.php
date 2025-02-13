<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\ISession;
use OCP\IL10N;
use OCP\IUserSession;
use OCP\IUserManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\puestosMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\UploadException;
use Shuchkin\SimpleXLSXGen;
use Shuchkin\SimpleXLSX;

/**
 * @psalm-suppress UnusedClass
 */
class PuestosController extends Controller {

	private $userSession;
	private $userManager;
	private $empleadosMapper;
	private $puestosMapper;
	private $configuracionesMapper;
	protected IRootFolder $rootFolder;
	private $session;
	private IL10N $l10n;

	public function __construct(IRequest $request, ISession $session, IUserSession $userSession, IUserManager $userManager, empleadosMapper $empleadosMapper, puestosMapper $puestosMapper, IL10N $l10n, IRootFolder $rootFolder, configuracionesMapper $configuracionesMapper) {
		parent::__construct(Application::APP_ID, $request);

		$this->userSession = $userSession;
		$this->userManager = $userManager;
		$this->empleadosMapper = $empleadosMapper;
		$this->puestosMapper = $puestosMapper;
		$this->configuracionesMapper = $configuracionesMapper;
		$this->rootFolder = $rootFolder;
		$this->session = $session;
		$this->l10n = $l10n;
	}

	#[UseSession]
	public function GetUserLists(): array {
		$empleados = $this->empleadosMapper->GetUserLists();
		$users = $this->empleadosMapper->getAllUsers();
		
		return [
			'Empleados' => $empleados,
			'Users' => $users,
		];
	}

	#[UseSession]
	public function GetPuestosFix(): array {
		$Puestos = $this->puestosMapper->GetPuestosList();
		$data = [];
		foreach ($Puestos as $puesto) {
			$data[] = [
				'value' => $puesto['Id_puestos'],
				'label' => $puesto['Nombre'],
			];
		}
		return $data;
	}

	#[UseSession]
	public function GetEmpleadosList(): array {
		$empleados = $this->empleadosMapper->GetUserLists();
		return ['Empleados' => $empleados];
	}

	#[UseSession]
	public function GetEmpleadosListFix(): array {
		$empleados = $this->empleadosMapper->GetUserLists();
		$data = [];
		foreach ($empleados as $empleado) {
			if (empty($empleado['displayname'])) {
				$empleado['displayname'] = $empleado['uid'];
			}
			$data[] = [
				'id' => $empleado['uid'],
				'displayName' => $empleado['displayname'],
				'user' => $empleado['uid'],
				'showUserStatus' => false,
			];
		}
		return $data;
	}

	#[UseSession]
	public function ActivarEmpleado(string $id_user): string {
		try {
			$timestamp = date('Y-m-d');
			$user = new empleados();
			$user->setid_user($id_user);
			$user->setcreated_at($timestamp);
			$user->setupdated_at($timestamp);
			$this->empleadosMapper->insert($user);
			return "ok"; 
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	#[UseSession]
	public function EliminarEmpleado(int $id_empleados): string {
		try {
			$this->empleadosMapper->deleteByIdEmpleado($id_empleados);
			return "ok"; 
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	#[UseSession]
	#[NoCSRFRequired]
	public function GetPuestosList(): array {
		return $this->puestosMapper->GetPuestosList();
	}

	public function ExportListPuestos(): array {
		$empleados = $this->puestosMapper->GetPuestosList();
		$books = [['Id_puesto', 'Nombre', 'created_at', 'updated_at']];
		foreach ($empleados as $datas) {
			$books[] = [
				$datas['Id_puesto'],
				$datas['Nombre'], 
				$datas['created_at'], 
				$datas['updated_at'],
			];
		}
		$xlsx = SimpleXLSXGen::fromArray($books);
		$xlsx->downloadAs('php://memory');
		return $books; 
	}

	public function ImportListPuestos(): void {
		$file = $this->getUploadedFile('puestofileXLSX');
		if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
			$rows_info = $xlsx->rows();
			foreach ($rows_info as $row) {
				if (strval($row[0])) {
					$this->puestosMapper->updatePuestos(strval($row[0]), strval($row[1]));
				} else {
					$timestamp = date('Y-m-d');
					$puesto = new puestos();
					$puesto->setnombre(strval($row[1]));
					$puesto->setcreated_at($timestamp);
					$puesto->setupdated_at($timestamp);
					$this->puestosMapper->insert($puesto);
				}
			}
		}
	}

	private function getUploadedFile(string $key): array {
		$file = $this->request->getUploadedFile($key);
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

	public function getAdminUser() {
		$adminGroup = 'admin';
		$adminUsers = $this->userManager->search('admin', $adminGroup, 10, 0);
		return !empty($adminUsers) ? $adminUsers : null;
	}

	#[UseSession]
	public function EliminarPuesto(int $id_puesto): string {
		try {
			$this->puestosMapper->EliminarPuesto(strval($id_puesto));
			return "ok"; 
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function GuardarCambioPuestos(int $id_puestos, string $nombre): void {
		$this->puestosMapper->updatePuestos(strval($id_puestos), $nombre);
	}

	public function crearPuesto(string $nombre): void {
		$timestamp = date('Y-m-d');
		$puesto = new puestos();
		$puesto->setnombre($nombre);
		$puesto->setcreated_at($timestamp);
		$puesto->setupdated_at($timestamp);
		$this->puestosMapper->insert($puesto);
	}
}
