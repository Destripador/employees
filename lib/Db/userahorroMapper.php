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


class userahorroMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'user_ahorro', userahorro::class);
	}

	public function getUserAhorro(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());
		
		return $this->findEntities($qb);
	}

	// obtener listado de usuarios con ahorro activo
	public function getUsersWithAhorro(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'o')
			->innerJoin('o', 'users', 'c', $qb->expr()->eq('uid', 'id_user'));

		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function getAllUsers(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('users');

		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function updateAhorroById(int $id_ahorro, string $id_permision, string $state, string $cantidad): void {
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('id_permision', $query->createNamedParameter($id_permision))
			->set('state', $query->createNamedParameter($state))
			->set('cantidad', $query->createNamedParameter($cantidad))
			->where($query->expr()->eq('id', $query->createNamedParameter($id)));

		$query->execute();
	}

	//TODO
	public function updatePermisionUserId(int $id, string $state): void {
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('state', $query->createNamedParameter($state))
			->where($query->expr()->eq('id_ahorro', $query->createNamedParameter($id)));

		$query->execute();
	}

	public function GetInfoAhorro(int $id_user): array{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id_user', $qb->createNamedParameter($id_user)));

		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;

	}
	public function GetInfoByIdAhorro(int $id_user): array{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id_ahorro', $qb->createNamedParameter($id_user)));

		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;

	}
}