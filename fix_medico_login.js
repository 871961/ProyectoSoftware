const { Pool } = require('pg');

const pool = new Pool({
  user: 'postgres',
  password: 'claudia',
  host: 'localhost',
  port: 5432,
  database: 'medhistory',
});

async function updateDoctorPasswords() {
  try {
    // Hash bcrypt VÁLIDO para 'medico123'
    const passwordHash = '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i';

    // Actualizar contraseñas
    const result = await pool.query(
      "UPDATE medicos SET contrasena_hash = $1 WHERE email LIKE '%clinica.com'",
      [passwordHash]
    );

    console.log(`✓ Contraseñas actualizadas: ${result.rowCount} médicos\n`);
    console.log('Médicos disponibles:');
    console.log('='.repeat(50));

    // Listar médicos
    const doctors = await pool.query(
      "SELECT nombre, apellidos, email, tipo_medico FROM medicos WHERE email LIKE '%clinica.com' AND activo = TRUE ORDER BY nombre"
    );

    doctors.rows.forEach(row => {
      console.log(`\nEmail: ${row.email}`);
      console.log(`Nombre: ${row.nombre} ${row.apellidos}`);
      console.log(`Tipo: ${row.tipo_medico === 'general' ? 'Médico General' : 'Especialista'}`);
    });

    console.log('\n' + '='.repeat(50));
    console.log('Contraseña: test123');
    console.log('Rol: Médico\n');

    await pool.end();
  } catch (err) {
    console.error('Error:', err);
    await pool.end();
    process.exit(1);
  }
}

updateDoctorPasswords();
