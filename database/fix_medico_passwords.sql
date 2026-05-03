-- Fix: Actualizar contraseñas de médicos para que sean iguales a la contraseña de prueba de pacientes
-- Contraseña: test123
-- Hash: $2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO

UPDATE medicos
SET contrasena_hash = '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO'
WHERE email LIKE '%clinica.com';

SELECT email, nombre, apellidos FROM medicos WHERE email LIKE '%clinica.com';
