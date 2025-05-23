<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;


class equipos extends Entity {
    
    protected string $id_equipos = '';
	protected string $id_jefe_equipo = '';
    protected string $nombre = '';
    protected string $created_at = '';
    protected string $updated_at = '';

	public function __construct() {
        $this->addType('Id_equipos', 'string');
        $this->addType('Id_jefe_equipo', 'string');
		$this->addType('Nombre', 'string');
		$this->addType('created_at', 'string');
		$this->addType('updated_at', 'string');
	}

	public function read(): array {
		return [
			'Id_equipos' => $this->idequipos,
			'Id_jefe_equipo' => $this->idjefeequipo,
			'Nombre' => $this->nombre,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}