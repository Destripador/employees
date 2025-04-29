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
class Version7Date20250428230520 extends SimpleMigrationStep {

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
  
  		if (!$schema->hasTable('historial_ahorro')) {
  			$table = $schema->createTable('historial_ahorro');
  			$table->addColumn('id_historial', 'integer', [
  				'autoincrement' => true,
  				'notnull' => true,
  			]);
  			$table->addColumn('id_ahorro', 'string', [
  			]);
  			$table->addColumn('cantidad_solicitada', 'string', [
  			]);
			$table->addColumn('cantidad_total', 'string', [
			]);
  			$table->addColumn('fecha_solicitud', 'string', [
  			]);
  			$table->addColumn('estado', 'string', [
  			]);
			  $table->addColumn('nota', 'string', [
			]);
  			$table->setPrimaryKey(['id_historial']);
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
