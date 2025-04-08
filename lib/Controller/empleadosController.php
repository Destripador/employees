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
use OCA\Empleados\Db\ausenciasMapper;
use OCA\Empleados\Db\ausencias;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\departamentos;
use OCA\Empleados\Db\configuraciones;

use OCP\IDBConnection;

use OCP\Files\IRootFolder;

use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;


require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador principal para la gestión de empleados en Nextcloud.
 */
class EmpleadosController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $groupManager;
    protected $empleadosMapper;
    protected $ausenciasMapper;
    protected $departamentosMapper;
    protected $configuracionesMapper;
    protected $session;
    protected $l10n;

    protected IRootFolder $rootFolder;

    public function __construct(
        IRequest $request,
        ISession $session,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        ausenciasMapper $ausenciasMapper,
        departamentosMapper $departamentosMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
        IGroupManager $groupManager,
        IRootFolder $rootFolder
    ) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $configuracionesMapper);

        $this->session = $session;
        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->ausenciasMapper = $ausenciasMapper;
        $this->departamentosMapper = $departamentosMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;

        $this->rootFolder = $rootFolder;
        $this->AdminCheckAccess(); // 🔥 Validar accesos automáticamente
    }

    /**
     * Obtiene la lista de empleados, usuarios y desactivados.
     */
    #[UseSession]
    #[NoAdminRequired]
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
    #[NoAdminRequired]
    public function GetEmpleadosList(): array {
        return ['Empleados' => $this->empleadosMapper->GetUserLists()];
    }

    /**
     * Obtiene empleados de un área específica.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosArea(string $id_area): array {
        return ['area' => $this->empleadosMapper->GetEmpleadosArea($id_area)];
    }

    /**
     * Obtiene empleados de un puesto específico.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosPuesto(string $id_puesto): array {
        return ['puesto' => $this->empleadosMapper->GetEmpleadosPuesto($id_puesto)];
    }


    
    /**
     * Obtiene empleados de un equipo específico.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosEquipo(string $id_equipo): array {
        return ['equipo' => $this->empleadosMapper->GetEmpleadosEquipo($id_equipo)];
    }

    /**
     * Activa un empleado y crea sus carpetas en Nextcloud.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function ActivarEmpleado(string $id_user): string {
        try {
            // Verificar si el grupo "empleados" existe
            $group = $this->groupManager->get("empleados");
            if (!$group) {
                $this->groupManager->createGroup("empleados");
                $group = $this->groupManager->get("empleados");
            }

            // verificar que el usuario exista en nextcloud
            $user = $this->userManager->get($id_user);
        
            // Verificar si el usuario ya pertenece al grupo
            if (!$group->inGroup($user)) {
                $group->addUser($user);
            }
            
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
                                
                // Obtén la conexión a través del contenedor de Nextcloud
                $connection = \OC::$server->get(IDBConnection::class);
                $idEmpleado = $connection->lastInsertId('empleados');

                $ausencias = new ausencias();
                $ausencias->setid_empleado((int)$idEmpleado);
                $this->ausenciasMapper->insert($ausencias);

                return "ok";
            } else {
                return "No existe usuario gestor";
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    #[UseSession]
    #[NoAdminRequired]
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
    #[NoAdminRequired]
	public function ActivarUsuario(int $id_empleados): string {
		try{

			$this->empleadosMapper->ActivarByIdEmpleado($id_empleados);
            
    
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

    #[UseSession]
    #[NoAdminRequired]
	public function EliminarEmpleado(int $id_empleados, string $id_user): string {
		try{

            // verificar que el usuario exista en nextcloud
            $user = $this->userManager->get($id_user);
            // Verificar si el grupo "empleados" existe
            $group = $this->groupManager->get("empleados");

            // Verificar si el usuario ya pertenece al grupo
            if ($group->inGroup($user)) {
                $group->removeUser($user);
            }
            
			$this->empleadosMapper->deleteByIdEmpleado($id_empleados);
            $this->ausenciasMapper->deleteByIdEmpleado($id_empleados);

			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

    /**
     * Importa lista de empleados desde un archivo XLSX.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function ImportListEmpleados(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
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
                
                $this->ausenciasMapper->updateAusenciasById(
                    (int)$row[0], 
                    (int)$row[25], 
                    (float)$row[26],
                );
            }
        }
    }

    #[UseSession]
    #[NoAdminRequired]
    public function GuardarNota(int $id_empleados, string $nota): void {
		$this->empleadosMapper->GuardarNota(strval($id_empleados), $nota);
	}

    #[UseSession]
    #[NoAdminRequired]
	public function CambiosEmpleado(
        $id_empleados, 
        $numeroempleado, 
        $ingreso, 
        $area, 
        $puesto, 
        $socio, 
        $gerente, 
        $fondoclave, 
        $fondoahorro, 
        $numerocuenta, 
        $equipoasignado,
        $equipo, 
        $sueldo,
        $id_aniversario,
        $dias_disponibles): void {
		$this->empleadosMapper->CambiosEmpleado(
            $id_empleados, 
            $numeroempleado, 
            $ingreso, 
            $area, 
            $puesto, 
            $socio, 
            $gerente, 
            $fondoclave, 
            $fondoahorro, 
            $numerocuenta, 
            $equipoasignado, 
            $equipo, 
            $sueldo);

        $this->ausenciasMapper->updateAusenciasById(
            (int)$id_empleados, 
            (int)$id_aniversario, 
            (float)$dias_disponibles
        );
	}

    #[UseSession]
    #[NoAdminRequired]
	public function CambiosPersonal($Id_empleados, $Direccion, $Estado_civil, $Telefono_contacto, $Rfc, $Imss, $Contacto_emergencia, $Numero_emergencia, $Curp, $Fecha_nacimiento, $Correo_contacto, $Genero): void {
		$this->empleadosMapper->CambiosPersonal($Id_empleados, $Direccion, $Estado_civil, $Telefono_contacto, $Rfc, $Imss, $Contacto_emergencia, $Numero_emergencia, $Curp, $Fecha_nacimiento, $Correo_contacto, $Genero);
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
        'id_aniversario',
        'dias_disponibles',
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
                    $datas['id_aniversario'],
                    $datas['dias_disponibles'],
					$datas['created_at'], 
					$datas['updated_at'], 
				]);
		}

		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $books );
		//$xlsx->saveAs('books.xlsx'); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx 
	
		$fileContent = $xlsx->downloadAs('php://memory');

		return $books; 
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

    
	#[NoCSRFRequired]
	#[NoAdminRequired]    
	public function GetUsers(): array {

        $users = $this->userManager->search('');

        $userList = [];
        foreach ($users as $user) {
            $userList[] = [
                'id' => $user->getUID(),
                'displayName' => $user->getDisplayName(),
                'icon' => $user->getUID(),
                'user' => $user->getUID(),
                'showUserStatus' => false,
            ];
        }

        
		$data = array(
			'Users' => $userList,
        );


        return $data;
	}

}
