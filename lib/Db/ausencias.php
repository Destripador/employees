<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;


class ausencias extends Entity {
    
    protected ?int $id_ausencias = null;
    protected ?int $id_aniversario = null;
    protected ?int $id_empleado = null;
    protected ?float $dias_disponibles = null;
    protected ?int $prima_vacacional = null;
	protected ?\DateTime $timestamp = null;

	public function __construct() {
        $this->addType('id_ausencias', 'int');
        $this->addType('id_aniversario', 'int');
        $this->addType('id_empleado', 'int');
		$this->addType('dias_disponibles', 'float');
		$this->addType('prima_vacacional', 'int');
		$this->addType('timestamp', 'DateTime');
	}

	public function read(): array {
		return [
			'id_aniversario' => $this->id_aniversario,
			'id_empleado' => $this->id_empleado,
			'dias_disponibles' => $this->dias_disponibles,
			'prima_vacacional' => $this->prima_vacacional,
			'timestamp' => $this->timestamp,
		];
	}
}