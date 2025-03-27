<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version5Date20250325183650 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
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
					// Tabla de equipo
					if (!$schema->hasTable('equipos')) {
						$table = $schema->createTable('equipos');
						$table->addColumn('Id_equipo', 'integer', [
							'autoincrement' => true,
							'notnull' => true,
						]);
		
						$table->addColumn('Id_jefe_equipo', 'string', [
							'notnull' => false,
						]);
		
						$table->addColumn('Nombre', 'string', [
							'notnull' => false,
						]);
			
						$table->addColumn('created_at', 'string', [
						'notnull' => true,
						]);
				
						$table->addColumn('updated_at', 'string', [
						'notnull' => true,
						]);
						
						$table->setPrimaryKey(['Id_equipo']);
						$table->addIndex(['Id_equipo'], 'Id_equipo');
					}

					return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
