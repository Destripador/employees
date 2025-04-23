<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;


class tipoausencia extends Entity {
    
    protected ?int $id_tipo_ausencia = null;
    protected ?string $nombre = null;
    protected string $descripcion = '';
    protected ?bool $solicitar_archivo = null;

	public function __construct() {
        $this->addType('id_tipo_ausencia', 'int');
        $this->addType('nombre', 'string');
		$this->addType('descripcion', 'string');
		$this->addType('solicitar_archivo', 'bool');
	}

	public function read(): array {
		return [
			'id_tipo_ausencia' => $this->id_tipo_ausencia,
			'nombre' => $this->nombre,
			'descripcion' => $this->descripcion,
			'solicitar_archivo' => $this->solicitar_archivo,
		];
	}
}