<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\userahorroMapper;
use OCA\Empleados\Db\historialahorroMapper;

class ahorrosController extends BaseController {

    protected $empleadosMapper;
	protected $configuracionesMapper;
	protected $userahorroMapper;
	protected $historialahorroMapper;
	protected $session;

	public function __construct(
		IRequest $request, 
		ISession $session, 
		IUserSession $userSession, 
		IUserManager $userManager,
        empleadosMapper $empleadosMapper,
		configuracionesMapper $configuracionesMapper, 
		userahorroMapper $userahorroMapper,
		historialahorroMapper $historialahorroMapper,
		IGroupManager $groupManager
	) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $empleadosMapper, $configuracionesMapper);

		$this->session = $session;
		$this->configuracionesMapper = $configuracionesMapper;
		$this->empleadosMapper = $empleadosMapper;
		$this->userahorroMapper = $userahorroMapper;
		$this->historialahorroMapper = $historialahorroMapper;
	}

    #[UseSession]
    #[NoAdminRequired]
	public function GetInfoAhorro(int $Id_user): array{
		try{
			$user = $this->userahorroMapper->GetInfoAhorro($Id_user);
			
			return $user; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	#[UseSession]
    #[NoAdminRequired]
	public function EnviarSolicitud(int $id_ahorro, float $cantidad_solicitada, string $nota): string {
		try{
			# $user = $this->userahorroMapper->GetInfoByIdAhorro($id_ahorro);
			$user = $this->userSession->getUser();
			$employee = $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());

			$this->historialahorroMapper->EnviarSolicitud($id_ahorro, $cantidad_solicitada, $employee[0]['Fondo_ahorro'], $nota);
			$this->userahorroMapper->updatePermisionUserId($id_ahorro, '2');
			
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	#[UseSession]
    #[NoAdminRequired]
	public function getHistorial(string $id_user) : array {
		try{
			$user = $this->historialahorroMapper->getahorrobyid($id_user);
			
			return $user; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	#[UseSession]
    #[NoAdminRequired]
	public function GetHistorialPanel(string $options_fechas_value, string $options_estado_values) : array {
		try{
			$user = $this->historialahorroMapper->GetHistorialPanel($options_fechas_value, $options_estado_values);
			
			return $user; 
		}
		catch(Exception $e){
			return $e;
		}

	}

	#[UseSession]
    #[NoAdminRequired]
	public function AceptarAhorro(int $id_ahorro, int $id): string {
		try{
			$this->historialahorroMapper->AceptarAhorro($id_ahorro);	
			$this->userahorroMapper->updatePermisionUserId($id, '0');	
			
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	#[UseSession]
    #[NoAdminRequired]
	public function DenegarAhorro(int $id_ahorro, int $id): string {
		try{
			$this->historialahorroMapper->DenegarAhorro($id_ahorro);		
			$this->userahorroMapper->updatePermisionUserId($id, '1');	
			
			return "ok"; 
		}
		catch(Exception $e){
			return $e;
		}
	}

	public function GenerateReport(string $options_fechas_value, string $options_estado_values): string {
		try{
			
			$user = $this->historialahorroMapper->GetHistorialPanel($options_fechas_value, $options_estado_values);
			$books = [["ID", 'NOMBRE', 'CORREO', 'CANTIDAD SOLICITADA', "ESTADO"]];

			foreach($user as $datas){
				if($datas['estado'] == 0){
					$estado = "Pendiente";
				}
				else{
					$estado = "Aprobado";
				}

				array_push($books, 
					[$datas['id'], 
					$datas['displayname'], 
					json_decode($datas['data'], true)['email']['value'],
					'$' . $datas['cantidad_solicitada'] . "", 
					$estado]);
			}

			
		
			$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $books );
			//$xlsx->saveAs('books.xlsx'); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx 
		
			$fileContent = $xlsx->downloadAs('php://memory');

			return $books; 
		}
		catch(Exception $e){
			return $e;
		}
	}

}
