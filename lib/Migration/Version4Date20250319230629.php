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
class Version4Date20250319230629 extends SimpleMigrationStep {

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
		
		if (!$schema->hasTable('aniversarios')) {
			$table = $schema->createTable('aniversarios');
			$table->addColumn('id_aniversario', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('numero_aniversario', 'integer', ['notnull' => true]);
			$table->addColumn('fecha_de', 'datetime', ['notnull' => false]);
			$table->addColumn('fecha_hasta', 'datetime', ['notnull' => false]);
			$table->addColumn('dias', 'decimal', ['precision' => 5, 'scale' => 2, 'notnull' => true]);
			$table->addColumn('timestamp', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
			$table->setPrimaryKey(['id_aniversario']);
			$table->addIndex(['numero_aniversario']);
		}
		
		if (!$schema->hasTable('tipo_ausencia')) {
			$table = $schema->createTable('tipo_ausencia');
			$table->addColumn('id_tipo_ausencia', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('nombre', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('descripcion', 'text', ['notnull' => false]);
			$table->addColumn('solicitar_archivo', 'integer', ['notnull' => true]);
			$table->setPrimaryKey(['id_tipo_ausencia']);
			$table->addIndex(['nombre']);
		}
		
		if (!$schema->hasTable('ausencias')) {
			$table = $schema->createTable('ausencias');
			$table->addColumn('id_ausencias', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('id_empleado', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('id_aniversario', 'integer', ['unsigned' => true, 'notnull' => false]);
			$table->addColumn('dias_disponibles', 'decimal', ['precision' => 5, 'scale' => 2, 'notnull' => false, 'default' => 0.00]); // PERMITE MEDIO DÍA
			$table->addColumn('prima_vacacional', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('timestamp', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
			$table->setPrimaryKey(['id_ausencias']);
			$table->addUniqueIndex(['id_empleado']);
		}
		
		if (!$schema->hasTable('historial_ausencias')) {
			$table = $schema->createTable('historial_ausencias');
			$table->addColumn('id_historial_ausencias', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('id_ausencias', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('id_aniversario', 'integer', ['unsigned' => true, 'notnull' => false]);
			$table->addColumn('id_tipo_ausencia', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('fecha_de', 'datetime', ['notnull' => true]);
			$table->addColumn('fecha_hasta', 'datetime', ['notnull' => true]);
			$table->addColumn('prima_vacacional', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('archivo', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('timestamp', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
			$table->addColumn('a_socio', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('a_gerente', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('a_capital_humano', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->setPrimaryKey(['id_historial_ausencias']);
			$table->addIndex(['id_ausencias']);
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
