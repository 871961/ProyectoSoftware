// Test de login API - simula lo que el frontend envía

const testCases = [
  {
    name: 'Elena (Médica General)',
    email: 'elena.fernandez@clinica.com',
    password: 'test123',
    role: 'medico'
  },
  {
    name: 'Ana (Médica General)',
    email: 'ana.martinez@clinica.com',
    password: 'test123',
    role: 'medico'
  }
];

async function testLogin(testCase) {
  console.log(`\n=== Test: ${testCase.name} ===`);
  console.log(`Email: ${testCase.email}`);
  console.log(`Password: ${testCase.password}`);
  console.log(`Role: ${testCase.role}`);

  try {
    const response = await fetch('http://medhistory.local/backend/src/controllers/AuthController.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(testCase)
    });

    console.log(`Status: ${response.status}`);
    const data = await response.json();

    if (response.ok) {
      console.log('✓ Login exitoso');
      console.log('Usuario:', data.usuario.nombre);
      console.log('Redirect:', data.redirect);
    } else {
      console.log('✗ Login fallido');
      console.log('Mensaje:', data.mensaje);
    }
  } catch (error) {
    console.log('✗ Error de conexión:', error.message);
  }
}

async function runTests() {
  console.log('=== TEST LOGIN API ===');
  console.log('URL Base: http://medhistory.local');

  for (const testCase of testCases) {
    await testLogin(testCase);
  }
}

runTests();
