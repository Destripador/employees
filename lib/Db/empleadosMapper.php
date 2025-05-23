<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use DateTime;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;


use OCP\AppFramework\Db\DoesNotExistException;


class empleadosMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'empleados', empleados::class);
	}

    public function GetMyEmployeeInfo($id): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*') // Solo traemos empleados sin duplicar
			->from('empleados', 'e')
			->where($qb->expr()->eq('e.Id_user', $qb->createNamedParameter($id)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

    public function GetUserLists(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('e.*', 'u.displayname', 'u.uid', 'a.*', 'i.*') // Solo traemos empleados sin duplicar
			->from('empleados', 'e')
			->innerJoin('e', 'users', 'u', $qb->expr()->eq('u.uid', 'e.Id_user'))
			->innerJoin('e', 'ausencias', 'a', $qb->expr()->eq('a.id_empleado', 'e.Id_empleados'))
			->innerJoin('e', 'user_ahorro', 'i', $qb->expr()->eq('i.id_user', 'e.Id_empleados'))
			->where($qb->expr()->eq('e.estado', $qb->createNamedParameter(1)));


		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function getAllUsers(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('users', 'o')
			->innerJoin('o', 'accounts', 'c', $qb->expr()->eq('o.uid', 'c.uid'));
			

		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function GetUserListsDeactive(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'o')
			->innerJoin('o', 'users', 'c', $qb->expr()->eq('uid', 'id_user'))
			->where($qb->expr()->eq('Estado', $qb->createNamedParameter(0)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function deleteByIdEmpleado(int $id_empleados): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Id_empleados', $qb->createNamedParameter($id_empleados)));
			

		$result = $qb->execute();
	}

	public function DesactivarByIdEmpleado(int $id_empleados): void {
		$timestamp = date('Y-m-d');
		$qb = $this->db->getQueryBuilder();

		$qb->update($this->getTableName())
		->set('Estado', $qb->createNamedParameter(0))
		->set('updated_at', $qb->createNamedParameter($timestamp))
		->where($qb->expr()->eq('Id_empleados', $qb->createNamedParameter($id_empleados)));
			
		$result = $qb->execute();
	}

	public function ActivarByIdEmpleado(int $id_empleados): void {
		$timestamp = date('Y-m-d');
		$qb = $this->db->getQueryBuilder();

		$qb->update($this->getTableName())
		->set('Estado', $qb->createNamedParameter(1))
		->set('updated_at', $qb->createNamedParameter($timestamp))
		->where($qb->expr()->eq('Id_empleados', $qb->createNamedParameter($id_empleados)));
			
		$result = $qb->execute();
	}

	public function updateEmpleado(
		string $Id_empleados, 
		string $Numero_empleado, 
		string $Ingreso, 
		string $Correo_contacto, 
		string $Id_departamento, 
		string $Id_puesto, 
		string $Id_gerente, 
		string $Id_socio, 
		string $Fondo_clave, 
		string $Fondo_ahorro, 
		string $Numero_cuenta, 
		string $Equipo_asignado, 
		string $Sueldo, 
		string $Fecha_nacimiento, 
		string $Estado, 
		string $Direccion, 
		string $Estado_civil, 
		string $Telefono_contacto, 
		string $Curp, 
		string $Rfc, 
		string $Imss, 
		string $Genero, 
		string $Contacto_emergencia, 
		string $Numero_emergencia,
	): void {
		try{
			$timestamp = date('Y-m-d');

			if(empty($Numero_empleado) && $Numero_empleado != 0){ $Numero_empleado = null; }
			if(empty($Ingreso) && $Ingreso != 0){ $Ingreso = null; }
			if(empty($Correo_contacto) && $Correo_contacto != 0){ $Correo_contacto = null; }
			if(empty($Id_departamento) && $Id_departamento != 0){ $Id_departamento = null; }
			if(empty($Id_puesto) && $Id_puesto != 0){ $Id_puesto = null; }
			if(empty($Id_gerente) && $Id_gerente != 0){ $Id_gerente = null; }
			if(empty($Id_socio) && $Id_socio != 0){ $Id_socio = null; }
			if(empty($Fondo_clave) && $Fondo_clave != 0){ $Fondo_clave = null; }
			if(empty($Fondo_ahorro) && $Fondo_ahorro != 0){ $Fondo_ahorro = null; }
			if(empty($Numero_cuenta) && $Numero_cuenta != 0){ $Numero_cuenta = null; }
			if(empty($Equipo_asignado) && $Equipo_asignado != 0){ $Equipo_asignado = null; }
			if(empty($Sueldo) && $Sueldo != 0){ $Sueldo = null; }
			if(empty($Fecha_nacimiento) && $Fecha_nacimiento != 0){ $Fecha_nacimiento = null; }
			if(empty($Estado) && $Estado != 0){ $Estado = null; }
			if(empty($Direccion) && $Direccion != 0){$Direccion = null; }
			if(empty($Estado_civil) && $Estado_civil != 0){$Estado_civil = null; }
			if(empty($Telefono_contacto) && $Telefono_contacto != 0){$Telefono_contacto = null; }
			if(empty($Curp) && $Curp != 0){$Curp = null; }
			if(empty($Rfc) && $Rfc != 0){$Rfc = null; }
			if(empty($Imss) && $Imss != 0){$Imss = null; }
			if(empty($Genero) && $Genero != 0){$Genero = null; }
			if(empty($Contacto_emergencia) && $Contacto_emergencia != 0){$Contacto_emergencia = null; }
			if(empty($Numero_emergencia) && $Numero_emergencia != 0){$Numero_emergencia = null; }
	
			$query = $this->db->getQueryBuilder();
			$query->update($this->getTableName())
				->set('Numero_empleado', $query->createNamedParameter($Numero_empleado))
				->set('Ingreso', $query->createNamedParameter($Ingreso))
				->set('Correo_contacto', $query->createNamedParameter($Correo_contacto))
				->set('Id_departamento', $query->createNamedParameter($Id_departamento))
				->set('Id_puesto', $query->createNamedParameter($Id_puesto))
				->set('Id_gerente', $query->createNamedParameter($Id_gerente))
				->set('Id_socio', $query->createNamedParameter($Id_socio))
				->set('Fondo_clave', $query->createNamedParameter($Fondo_clave))
				->set('Fondo_ahorro', $query->createNamedParameter($Fondo_ahorro))
				->set('Numero_cuenta', $query->createNamedParameter($Numero_cuenta))
				->set('Equipo_asignado', $query->createNamedParameter($Equipo_asignado))
				->set('Sueldo', $query->createNamedParameter($Sueldo))
				->set('Fecha_nacimiento', $query->createNamedParameter($Fecha_nacimiento))
				->set('Estado', $query->createNamedParameter($Estado))
				->set('Direccion', $query->createNamedParameter($Direccion))
				->set('Estado_civil', $query->createNamedParameter($Estado_civil))
				->set('Telefono_contacto', $query->createNamedParameter($Telefono_contacto))
				->set('Curp', $query->createNamedParameter($Curp))
				->set('Rfc', $query->createNamedParameter($Rfc))
				->set('Imss', $query->createNamedParameter($Imss))
				->set('Genero', $query->createNamedParameter($Genero))
				->set('Contacto_emergencia', $query->createNamedParameter($Contacto_emergencia))
				->set('Numero_emergencia', $query->createNamedParameter($Numero_emergencia))
				->set('updated_at', $query->createNamedParameter($timestamp))
				->where($query->expr()->eq('Id_empleados', $query->createNamedParameter($Id_empleados)));
	
			$query->execute();
		}
		catch(Exception $e){
			console.log($e);
		}
	}

	public function GuardarNota(string $Id_empleados, string $Nota,
	): void {
		try{
			$timestamp = date('Y-m-d');

			if(empty($Nota)){ $Nota = null; }
			
			$query = $this->db->getQueryBuilder();
			$query->update($this->getTableName())
				->set('Notas', $query->createNamedParameter($Nota))
				->set('updated_at', $query->createNamedParameter($timestamp))
				->where($query->expr()->eq('Id_empleados', $query->createNamedParameter($Id_empleados)));
	
			$query->execute();
		}
		catch(Exception $e){
			console.log($e);
		}
	}

    public function GetEmpleadosArea(string $id_area): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'e')
			->innerJoin('e', 'users', 'u', $qb->expr()->eq('u.uid', 'e.Id_user'))
			->where($qb->expr()->eq('Id_departamento', $qb->createNamedParameter($id_area)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function GetEmpleadosPuesto(string $id_puesto): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'e')
			->innerJoin('e', 'users', 'u', $qb->expr()->eq('u.uid', 'e.Id_user'))
			->where($qb->expr()->eq('Id_puesto', $qb->createNamedParameter($id_puesto)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function GetEmpleadosEquipo(string $id_equipo): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'e')
			->innerJoin('e', 'users', 'u', $qb->expr()->eq('u.uid', 'e.Id_user'))
			->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($id_equipo)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function CambiosEmpleado(
		$Id_empleados, 
		$Numero_empleado, 
		$Ingreso, 
		$Id_departamento, 
		$Id_puesto, 
		$Id_socio, 
		$Id_gerente, 
		$Fondo_clave, 
		$Fondo_ahorro, 
		$Numero_cuenta, 
		$Equipo_asignado,
		$Id_equipo,
		$Sueldo): void {

		try{
			$timestamp = date('Y-m-d');

			if(empty($Numero_empleado) && $Numero_empleado != 0){ $Numero_empleado = null; }
			if(empty($Ingreso) && $Ingreso != 0){ $Ingreso = null; }
			if(empty($Id_departamento) && $Id_departamento != 0){ $Id_departamento = null; }
			if(empty($Id_puesto) && $Id_puesto != 0){ $Id_puesto = null; }
			if(empty($Id_gerente) && $Id_gerente != 0){ $Id_gerente = null; }
			if(empty($Id_socio) && $Id_socio != 0){ $Id_socio = null; }
			if(empty($Fondo_clave) && $Fondo_clave != 0){ $Fondo_clave = null; }
			if(empty($Fondo_ahorro) && $Fondo_ahorro != 0){ $Fondo_ahorro = null; }
			if(empty($Numero_cuenta) && $Numero_cuenta != 0){ $Numero_cuenta = null; }
			if(empty($Equipo_asignado) && $Equipo_asignado != 0){ $Equipo_asignado = null; }
			if(empty($Id_equipo) && $Id_equipo != 0){ $Id_equipo = null; }
			if(empty($Sueldo) && $Sueldo != 0){ $Sueldo = null; }
	
			$query = $this->db->getQueryBuilder();
			$query->update($this->getTableName())
				->set('Numero_empleado', $query->createNamedParameter($Numero_empleado))
				->set('Ingreso', $query->createNamedParameter($Ingreso))
				->set('Id_departamento', $query->createNamedParameter($Id_departamento))
				->set('Id_puesto', $query->createNamedParameter($Id_puesto))
				->set('Id_gerente', $query->createNamedParameter($Id_gerente))
				->set('Id_socio', $query->createNamedParameter($Id_socio))
				->set('Fondo_clave', $query->createNamedParameter($Fondo_clave))
				->set('Fondo_ahorro', $query->createNamedParameter($Fondo_ahorro))
				->set('Numero_cuenta', $query->createNamedParameter($Numero_cuenta))
				->set('Equipo_asignado', $query->createNamedParameter($Equipo_asignado))
				->set('Id_equipo', $query->createNamedParameter($Id_equipo))
				->set('Sueldo', $query->createNamedParameter($Sueldo))
				->set('updated_at', $query->createNamedParameter($timestamp))
				->where($query->expr()->eq('Id_empleados', $query->createNamedParameter($Id_empleados)));
	
			$query->execute();
			
		}
		catch(Exception $e){
			console.log($e);
		}
	}

	public function CambiosPersonal($Id_empleados, $Direccion, $Estado_civil, $Telefono_contacto, $Rfc, $Imss, $Contacto_emergencia, $Numero_emergencia, $Curp, $Fecha_nacimiento, $Correo_contacto, $Genero): void {
		try{
			$timestamp = date('Y-m-d');

			if(empty($Direccion) && $Direccion != 0){ $Direccion = null; }
			if(empty($Estado_civil) && $Estado_civil != 0){ $Estado_civil = null; }
			if(empty($Telefono_contacto) && $Telefono_contacto != 0){ $Telefono_contacto = null; }
			if(empty($Curp) && $Curp != 0){ $Curp = null; }
			if(empty($Rfc) && $Rfc != 0){ $Rfc = null; }
			if(empty($Imss) && $Imss != 0){ $Imss = null; }
			if(empty($Genero) && $Genero != 0){ $Genero = null; }
			if(empty($Correo_contacto) && $Correo_contacto != 0){ $Correo_contacto = null; }
			if(empty($Contacto_emergencia) && $Contacto_emergencia != 0){ $Contacto_emergencia = null; }
			if(empty($Fecha_nacimiento) && $Fecha_nacimiento != 0){ $Fecha_nacimiento = null; }
			if(empty($Numero_emergencia) && $Numero_emergencia != 0){ $Numero_emergencia = null; }
			
	
			$query = $this->db->getQueryBuilder();
			$query->update($this->getTableName())
				->set('Direccion', $query->createNamedParameter($Direccion))
				->set('Estado_civil', $query->createNamedParameter($Estado_civil))
				->set('Telefono_contacto', $query->createNamedParameter($Telefono_contacto))
				->set('Curp', $query->createNamedParameter($Curp))
				->set('Rfc', $query->createNamedParameter($Rfc))
				->set('Imss', $query->createNamedParameter($Imss))
				->set('Genero', $query->createNamedParameter($Genero))			
				->set('Correo_contacto', $query->createNamedParameter($Correo_contacto))
				->set('Contacto_emergencia', $query->createNamedParameter($Contacto_emergencia))
				->set('Numero_emergencia', $query->createNamedParameter($Numero_emergencia))
				->set('Fecha_nacimiento', $query->createNamedParameter($Fecha_nacimiento))
				->set('updated_at', $query->createNamedParameter($timestamp))
				->where($query->expr()->eq('Id_empleados', $query->createNamedParameter($Id_empleados)));
	
			$query->execute();
		}
		catch(Exception $e){
			echo $e;
		}
	}
}