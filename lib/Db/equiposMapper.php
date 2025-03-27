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


class equiposMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'equipos', equipos::class);
	}

	public function GetEquiposList(): array {
		$qb = $this->db->getQueryBuilder();

		/* 
			SELECT d.nombre, COUNT(e.id_empleados) AS cantidad_empleados
			FROM oc_departamentos d
			LEFT JOIN oc_empleados e ON d.Nombre = e.Id_equipo
			GROUP BY d.Id_equipo;
		*/
		$qb->select('d.Id_equipo', 'd.Id_jefe_equipo','d.Nombre')
			->selectAlias($qb->createFunction('COUNT(e.Id_empleados)'), 'cantidad_empleados')
			->from($this->getTableName(), 'd')
			->leftJoin('d', 'empleados', 'e', 'd.Id_equipo = e.Id_equipo')
			->groupBy('d.Id_equipo');
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function CheckExistEquipos($id_departamentos): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($id_departamentos)));
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function deleteByIdEmpleado(int $id_departamentos): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Nombre', $qb->createNamedParameter($Nombre)));

		$result = $qb->execute();
	}

	public function updateEquipos($Id_equipo, $Id_jefe_equipo, $Nombre): void {
		$timestamp = date('Y-m-d');

		if(empty($Id_equipo) && $Id_equipo != 0){ $Id_equipo = null; }
		if(empty($Id_jefe_equipo) && $Id_jefe_equipo != 0){ $Id_jefe_equipo = null; }
		if(empty($Nombre) && $Nombre != 0){ $Nombre = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('Id_jefe_equipo', $query->createNamedParameter($Id_jefe_equipo))
			->set('Nombre', $query->createNamedParameter($Nombre))
			->set('updated_at', $query->createNamedParameter($timestamp))
			->where($query->expr()->eq('Id_equipo', $query->createNamedParameter($Id_equipo)));
	
		$query->execute();
	}

	public function EliminarEquipo(string $Id_equipo): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($Id_equipo)));

		$result = $qb->execute();
	}

}