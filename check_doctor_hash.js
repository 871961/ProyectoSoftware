const { Pool } = require('pg');

const pool = new Pool({
  user: 'postgres',
  password: 'claudia',
  host: 'localhost',
  port: 5432,
  database: 'medhistory',
});

async function checkDoctor() {
  try {
    const result = await pool.query(
      "SELECT email, nombre, apellidos, activo, contrasena_hash FROM medicos WHERE email = $1",
      ['elena.fernandez@clinica.com']
    );

    if (result.rows.length > 0) {
      const medico = result.rows[0];
      console.log('\n=== Información del Médico ===');
      console.log('Email:', medico.email);
      console.log('Nombre:', medico.nombre, medico.apellidos);
      console.log('Activo:', medico.activo);
      console.log('Hash almacenado:', medico.contrasena_hash);
      console.log('\n=== Hashes esperados ===');
      console.log('Hash de "test123":', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO');
      console.log('¿Coinciden?:', medico.contrasena_hash === '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO');
    } else {
      console.log('No se encontró el médico');
    }

    await pool.end();
  } catch (err) {
    console.error('Error:', err.message);
    await pool.end();
    process.exit(1);
  }
}

checkDoctor();
