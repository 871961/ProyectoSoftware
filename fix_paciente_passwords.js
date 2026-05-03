const { Pool } = require('pg');

const pool = new Pool({
  user: 'postgres',
  password: 'claudia',
  host: 'localhost',
  port: 5432,
  database: 'medhistory',
});

async function updatePatientPasswords() {
  try {
    // Hash bcrypt VÁLIDO para 'test123'
    const passwordHash = '$2y$10$s16NUatThd/CxYns8aTSt.1DV3tTqpAHoWLIW2j9HxaYQ0tUfjmZO';

    // Actualizar contraseñas de TODOS los pacientes
    const result = await pool.query(
      "UPDATE pacientes SET contrasena_hash = $1 WHERE activo = TRUE",
      [passwordHash]
    );

    console.log(`✓ Contraseñas de pacientes actualizadas: ${result.rowCount} pacientes\n`);
    console.log('Pacientes disponibles:');
    console.log('='.repeat(50));

    // Listar pacientes
    const patients = await pool.query(
      "SELECT email, nombre, apellidos FROM pacientes WHERE activo = TRUE ORDER BY nombre LIMIT 5"
    );

    patients.rows.forEach(row => {
      console.log(`\nEmail: ${row.email}`);
      console.log(`Nombre: ${row.nombre} ${row.apellidos}`);
    });

    console.log('\n' + '='.repeat(50));
    console.log('Contraseña: test123');
    console.log('Rol: Paciente\n');

    await pool.end();
  } catch (err) {
    console.error('Error:', err);
    await pool.end();
    process.exit(1);
  }
}

updatePatientPasswords();
