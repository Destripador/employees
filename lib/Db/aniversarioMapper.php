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


class aniversarioMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'aniversarios', aniversarios::class);
	}

	public function GetAniversarios(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());
			
		$result = $qb->execute();
		$aniversarios = $result->fetchAll();
		$result->closeCursor();
	
		return $aniversarios;
	}

	public function CheckExistAreas($id_aniversarios): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('Id_departamento', $qb->createNamedParameter($id_aniversarios)));
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function deleteByIdEmpleado(int $id_aniversarios): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Nombre', $qb->createNamedParameter($Nombre)));

		$result = $qb->execute();
	}

	public function updateAniversarios(int $id_aniversario, int $numero_aniversario, float $dias ): void {
		if(empty($id_aniversario) && $id_aniversario != 0){ $id_aniversario = null; }
		if(empty($numero_aniversario) && $numero_aniversario != 0){ $numero_aniversario = null; }
		if(empty($dias) && $dias != 0){ $dias = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('numero_aniversario', $query->createNamedParameter($numero_aniversario))
			->set('dias', $query->createNamedParameter($dias))
			->where($query->expr()->eq('id_aniversario', $query->createNamedParameter($id_aniversario)));
	
		$query->execute();
	}

	public function VaciarAniversarios(): void {
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

}