<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;


class aniversario extends Entity {
    
    protected int $id_aniversario = '';
    protected int $numero_aniversario = '';
    protected string $fecha_de = '';
    protected string $fecha_hasta = '';
    protected float $dias = '';

	public function __construct() {
        $this->addType('Id_aniversario', 'int');
        $this->addType('numero_aniversario', 'int');
		$this->addType('fecha_de', 'string');
		$this->addType('fecha_hasta', 'string');
		$this->addType('dias', 'float');
	}

	public function read(): array {
		return [
			'Id_aniversario' => $this->id_aniversario,
			'numero_aniversario' => $this->numero_aniversario,
			'fecha_de' => $this->fecha_de,
			'fecha_hasta' => $this->fecha_hasta,
			'dias' => $this->dias,
		];
	}
}