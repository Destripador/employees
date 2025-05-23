<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;

class Version2Date20240724190637 extends SimpleMigrationStep {

	/** @var IDBConnection */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// No pre-schema changes needed
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('empleados_conf')) {
			$table = $schema->createTable('empleados_conf');
			$table->addColumn('Id_conf', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			
			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
			]);
	
			$table->addColumn('Data', 'string', [
				'notnull' => false,
			]);
			
			$table->setPrimaryKey(['Id_conf']);
			$table->addIndex(['Id_conf'], 'Id_conf');
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$qb = $this->db->getQueryBuilder();
		$nombres = ['usuario_almacenamiento', 'automatic_save_note', 'acumular_vacaciones', 'modulo_ahorro'];
		  
		foreach ($nombres as $nombre) {
			$qb->insert('empleados_conf')
				->values([
					'Nombre' => $qb->createNamedParameter($nombre)
				])
				->execute();
		}
	}
}
