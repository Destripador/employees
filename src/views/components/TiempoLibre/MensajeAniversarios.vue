<template id="content">
	<div>
		<div class="info-vacaciones">
			<h2>🏖️ Tabla de Vacaciones</h2>
			<p>
				Esta tabla te muestra <strong>cuántos días de vacaciones te corresponden</strong> según los años que lleves trabajando en la empresa. Es una guía basada en la <strong>Ley Federal del Trabajo</strong>, reformada en 2023.
			</p>

			<h3>🤔 Preguntas frecuentes</h3>

			<!-- Acordeón -->
			<div v-for="(pregunta, index) in preguntas" :key="index" class="acordeon-item">
				<button class="acordeon-titulo" @click="toggle(index)">
					{{ pregunta.titulo }}
					<span>{{ pregunta.abierto ? '➖' : '➕' }}</span>
				</button>
				<div v-show="pregunta.abierto" class="acordeon-contenido">
					<div>{{ pregunta.contenido }}</div>
				</div>
			</div>

			<h3>✅ Recomendación</h3>
			<p>
				Consulta esta tabla cada vez que cumplas un aniversario laboral. Así podrás <strong>planear tus descansos con anticipación</strong> y disfrutar al máximo tus días libres.
			</p>
		</div>
	</div>
</template>

<script>
export default {
	name: 'MensajeAniversarios',

	props: {
		info: {
			type: Object,
			required: true,
		},
		acumular: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			preguntas: [
				{
					titulo: '¿Desde cuándo tengo derecho a vacaciones?',
					contenido: 'Desde tu primer año trabajado completo ya puedes disfrutar de vacaciones. El mínimo son <strong>12 días</strong>, y va aumentando cada año.',
					abierto: false,
				},
				{
					titulo: '¿Cómo se cuentan los días?',
					contenido: 'Los días que aparecen en la tabla son <strong>días hábiles</strong>. No cuentan sábados, domingos ni feriados.',
					abierto: false,
				},
				{
					titulo: '¿Puedo dividir mis vacaciones?',
					contenido: 'Sí. Por ley, al menos la mitad debe tomarse de corrido, pero puedes hablar con Recursos Humanos para distribuir los días según tus necesidades y las de tu equipo.',
					abierto: false,
				},
				{
					titulo: '¿Qué pasa si no tomo mis vacaciones?',
					contenido: this.acumular === 'true'
						? 'Las vacaciones no tomadas <strong>no se pierden</strong>, pero es importante usarlas. Descansar es un derecho y también ayuda a tu salud y desempeño.'
						: 'Si no tomas tus vacaciones, <strong>se pierden</strong>. Es importante que las uses para cuidar tu salud y bienestar.',
					abierto: false,
				},
				{
					titulo: '¿Puedo pedir más días de vacaciones?',
					contenido: 'Sí, aunque lo establecido por ley es el mínimo, la empresa puede ofrecer más días como <strong>prestación adicional</strong>. Revisa tu contrato o habla con RH.',
					abierto: false,
				},
			],
		}
	},

	methods: {
		toggle(index) {
			this.preguntas[index].abierto = !this.preguntas[index].abierto
		},
	},
}
</script>

<style scoped>
.acordeon-item {
	margin-bottom: 10px;
	border: 1px solid #ccc;
	border-radius: 5px;
	overflow: hidden;
}

.acordeon-titulo {
	width: 100%;
	text-align: left;
	background-color: #f0f0f0;
	border: none;
	padding: 10px;
	font-weight: bold;
	cursor: pointer;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 16px;
}

.acordeon-contenido {
	padding: 10px;
	background-color: #fafafa;
	border-top: 1px solid #ddd;
	transition: all 0.3s ease-in-out;
}
</style>
