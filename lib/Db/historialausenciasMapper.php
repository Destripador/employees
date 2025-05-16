<?php

declare(strict_types=1);

namespace OCA\empleados\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class historialausenciasMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'historial_ausencias', historialausencias::class);
	}

		public function EnviarAusencia(int $id_tipo_ausencia, $id_ausencias, $fecha_de, $fecha_hasta, int $prima_vacacional, string $notas, $id_aniverario): void {
			$insert = $this->db->getQueryBuilder();
			$insert->insert($this->getTableName())
				->values(
					[
						'id_ausencias' => $insert->createNamedParameter($id_ausencias),
						'id_aniversario' => $insert->createNamedParameter($id_aniverario),
						'id_tipo_ausencia' => $insert->createNamedParameter($id_tipo_ausencia),
						'fecha_de' => $insert->createNamedParameter($fecha_de),
						'fecha_hasta' => $insert->createNamedParameter($fecha_hasta),
						'prima_vacacional' => $insert->createNamedParameter($prima_vacacional),
						'notas' => $insert->createNamedParameter($notas),
					]
				);
			$insert->executeStatement();
	}
}
