<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Luis Angel Alvarado Hernandez <luis.alvarado@crowe.mx>
// SPDX-License-Identifier: AGPL-3.0-or-later

return [

	'routes' => [
		/********************************** INDEX **********************************************/ 
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#Areas', 'url' => '/Areas', 'verb' => 'GET'],
		['name' => 'page#Puestos', 'url' => '/Puestos', 'verb' => 'GET'],
		
		/******************************** EMPLEADOS ********************************************/ 
		['name' => 'empleados#GetUserLists', 'url' => '/GetUserLists', 'verb' => 'GET'],
		['name' => 'empleados#GetEmpleadosList', 'url' => '/GetEmpleadosList', 'verb' => 'GET'],
		['name' => 'empleados#GetEmpleadosArea', 'url' => '/GetEmpleadosArea/{id_area}', 'verb' => 'GET'],
		['name' => 'empleados#GetEmpleadosPuesto', 'url' => '/GetEmpleadosPuesto/{id_puesto}', 'verb' => 'GET'],
		['name' => 'empleados#GetEmpleadosListFix', 'url' => '/GetEmpleadosListFix', 'verb' => 'GET'],
		['name' => 'empleados#ExportListEmpleados', 'url' => '/ExportListEmpleados', 'verb' => 'GET'],
		
		['name' => 'empleados#GuardarNota', 'url' => '/GuardarNota', 'verb' => 'POST'],
		['name' => 'empleados#CambiosEmpleado', 'url' => '/CambiosEmpleado', 'verb' => 'POST'],
		['name' => 'empleados#CambiosPersonal', 'url' => '/CambiosPersonal', 'verb' => 'POST'],
		['name' => 'empleados#ActivarEmpleado', 'url' => '/ActivarEmpleado', 'verb' => 'POST'],
		['name' => 'empleados#ActivarUsuario', 'url' => '/ActivarUsuario', 'verb' => 'POST'],
		['name' => 'empleados#EliminarEmpleado', 'url' => '/EliminarEmpleado', 'verb' => 'POST'],
		['name' => 'empleados#DesactivarEmpleado', 'url' => '/DesactivarEmpleado', 'verb' => 'POST'],
		['name' => 'empleados#ImportListEmpleados', 'url' => '/ImportListEmpleados', 'verb' => 'POST'],

		/******************************** AREAS ********************************************/ 
		['name' => 'areas#GetAreasFix', 'url' => '/GetAreasFix', 'verb' => 'GET'],
		['name' => 'areas#GetAreasList', 'url' => '/GetAreasList', 'verb' => 'GET'],
		['name' => 'areas#ExportListAreas', 'url' => '/ExportListAreas', 'verb' => 'GET'],

		['name' => 'areas#GuardarCambioArea', 'url' => '/GuardarCambioArea', 'verb' => 'POST'],
		['name' => 'areas#ImportListAreas', 'url' => '/ImportListAreas', 'verb' => 'POST'],
		['name' => 'areas#EliminarArea', 'url' => '/EliminarArea', 'verb' => 'POST'],
		['name' => 'areas#crearArea', 'url' => '/crearArea', 'verb' => 'POST'],

		/****************************** PUESTOS *********************************************/
		['name' => 'puestos#GetPuestosFix', 'url' => '/GetPuestosFix', 'verb' => 'GET'],
		['name' => 'puestos#GetPuestosList', 'url' => '/GetPuestosList', 'verb' => 'GET'],
		['name' => 'puestos#ExportListPuestos', 'url' => '/ExportListPuestos', 'verb' => 'GET'],
		
		['name' => 'puestos#GuardarCambioPuestos', 'url' => '/GuardarCambioPuestos', 'verb' => 'POST'],
		['name' => 'puestos#ImportListPuestos', 'url' => '/ImportListPuestos', 'verb' => 'POST'],
		['name' => 'puestos#EliminarPuesto', 'url' => '/EliminarPuesto', 'verb' => 'POST'],
		['name' => 'puestos#crearPuesto', 'url' => '/crearPuesto', 'verb' => 'POST'],


		/***************************** CONFIGURACIONES ***************************************/
		['name' => 'configuraciones#GetConfigurations', 'url' => '/GetConfigurations', 'verb' => 'GET'],

		['name' => 'configuraciones#ActualizarGestor', 'url' => '/ActualizarGestor', 'verb' => 'POST'],
		['name' => 'configuraciones#ActualizarConfiguracion', 'url' => '/ActualizarConfiguracion', 'verb' => 'POST'],
	],
	
];
