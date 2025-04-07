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


class ausenciasMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'ausencias', ausencias::class);
	}

	public function GetAusencias(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());
			
		$result = $qb->execute();
		$ausencias = $result->fetchAll();
		$result->closeCursor();
	
		return $ausencias;
	}

	public function CheckExistAreas($id_ausencias): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('Id_departamento', $qb->createNamedParameter($id_ausencias)));
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function updateAusencias(int $id_ausencias, int $numero_ausencias, float $dias ): void {
		if(empty($id_ausencias) && $id_ausencias != 0){ $id_ausencias = null; }
		if(empty($numero_ausencias) && $numero_ausencias != 0){ $numero_ausencias = null; }
		if(empty($dias) && $dias != 0){ $dias = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('numero_ausencias', $query->createNamedParameter($numero_ausencias))
			->set('dias', $query->createNamedParameter($dias))
			->where($query->expr()->eq('id_ausencias', $query->createNamedParameter($id_ausencias)));
	
		$query->execute();
	}

	public function VaciarAusencias(): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName());

		$result = $qb->execute();
	}

	public function EliminarArea(string $id_departamento): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Id_departamento', $qb->createNamedParameter($id_departamento)));

		$result = $qb->execute();
	}

	public function deleteByIdEmpleado(int $id_empleados): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('id_empleado', $qb->createNamedParameter($id_empleados)));
			

		$qb->executeStatement();
	}

}