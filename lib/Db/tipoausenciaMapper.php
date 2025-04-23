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


class tipoausenciaMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tipo_ausencia', tipo_ausencia::class);
	}

	public function getTipo(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());
			
		$result = $qb->execute();
		$tipo_ausencia = $result->fetchAll();
		$result->closeCursor();
	
		return $tipo_ausencia;
	}

	public function updateTipoAusencias(int $id_tipo_ausencia, string $nombre, string $descripcion, bool $solicitar_archivo): void {
		if(empty($id_tipo_ausencia) && $id_tipo_ausencia != 0){ $id_tipo_ausencia = null; }
		if(empty($nombre) && $nombre != 0){ $nombre = null; }
		if(empty($descripcion) && $descripcion != 0){ $descripcion = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('nombre', $query->createNamedParameter($nombre))
			->set('descripcion', $query->createNamedParameter($descripcion))
			->set('solicitar_archivo', $query->createNamedParameter($solicitar_archivo))
			->where($query->expr()->eq('id_tipo_ausencia', $query->createNamedParameter($id_tipo_ausencia)));
	
		$query->execute();
	}

	public function VaciarTipo(): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName());

		$result = $qb->execute();
	}
}