- actualizar seccion de detalles de empleado
data es una variable array que contiene todos los datos del empleado
this.$bus.emit('send-data', data)

- actualizar loading state
esto solo cambia el valor de show, cambiar a true o false segun la necesidad
this.$bus.emit('show', false)

- actualizar lista de empleados
this.$bus.emit('getall')