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

	public function updateAreas(string $Id_departamento, string $Id_padre, string $Nombre ): void {
		$timestamp = date('Y-m-d');

		if(empty($Id_departamento) && $Id_departamento != 0){ $Id_departamento = null; }
		if(empty($Id_padre) && $Id_padre != 0){ $Id_padre = null; }
		if(empty($Nombre) && $Nombre != 0){ $Nombre = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('Id_padre', $query->createNamedParameter($Id_padre))
			->set('Nombre', $query->createNamedParameter($Nombre))
			->set('updated_at', $query->createNamedParameter($timestamp))
			->where($query->expr()->eq('Id_departamento', $query->createNamedParameter($Id_departamento)));
	
		$query->execute();
	}

	public function EliminarArea(string $id_departamento): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Id_departamento', $qb->createNamedParameter($id_departamento)));

		$result = $qb->execute();
	}

}