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


class historialahorroMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'historial_ahorro', historialahorro::class);
	}

    public function getahorrobyid(string $id_user): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())            
			->where($qb->expr()->eq('id_ahorro', $qb->createNamedParameter($id_user)));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function GetHistorialPanel(string $options_fechas_value, string $options_estado_values): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName(), 'o')
			->innerJoin('o', 'user_ahorro', 'x', $qb->expr()->eq('x.id_ahorro', 'o.id_ahorro'))
			->innerJoin('x', 'empleados', 'c', $qb->expr()->eq('c.Id_empleados', 'x.id_user'))
			->innerJoin('c', 'users', 'u', $qb->expr()->eq('u.uid', 'c.Id_user'))
			->where($qb->expr()->eq('o.estado', $qb->createNamedParameter($options_estado_values)))
			->andWhere(($qb->expr()->like('o.fecha_solicitud', $qb->createNamedParameter('%' . $options_fechas_value))));
		
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function EnviarSolicitud(int $id_ahorro, float $cantidad_solicitada, string $cantidad_total, string $nota): void {
		$nowTimestamp = date("d-m-Y");

		$insert = $this->db->getQueryBuilder();
		$insert->insert($this->getTableName())
			->values(
				[
					'id_ahorro' => $insert->createNamedParameter($id_ahorro),
					'cantidad_solicitada' => $insert->createNamedParameter($cantidad_solicitada),
					'cantidad_total' => $insert->createNamedParameter($cantidad_total),
					'fecha_solicitud' => $insert->createNamedParameter($nowTimestamp),
					'estado' => $insert->createNamedParameter('0'),
					'nota' => $insert->createNamedParameter($nota),
				]
			);
		$insert->executeStatement();
	}
	
	public function getSolicitudId($id): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
		
		$result = $qb->execute();
		$data = $result->fetchAll();
		$result->closeCursor();
	
		return $data;
	}


	public function AceptarAhorro(int $id): void {
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('estado', $query->createNamedParameter("1"))
			->where($query->expr()->eq('id_historial', $query->createNamedParameter($id)));

		$query->execute();
	}

	public function DenegarAhorro(int $id): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('id_historial', $qb->createNamedParameter($id)));

		$qb->execute();
	}
}