- actualizar seccion de detalles de empleado
data es una variable array que contiene todos los datos del empleado
this.$bus.emit('send-data', data)

----------------------------------------------------------------------------------
- actualizar loading state
esto solo cambia el valor de show, cambiar a true o false segun la necesidad
this.$bus.emit('show', false)

----------------------------------------------------------------------------------
- actualizar lista de empleados
this.$bus.emit('getall')

----------------------------------------------------------------------------------

en cada archivo de vue puedes esperar un evento con

		this.$bus.on('send-data', (data) => {
			this.data_empleado = data
		})

cambiando el send-data por el nombre de tu evento
data equivale a la informacion enviada por evento emitido


----------------------------------------------------------------------------------