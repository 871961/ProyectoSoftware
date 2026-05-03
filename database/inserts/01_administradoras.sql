-- =============================================================================
-- ADMINISTRADORAS
-- Contraseña: test123
-- Hash bcrypt válido: $2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i
-- =============================================================================

INSERT INTO administradores (nombre, apellidos, email, contrasena_hash, activo)
VALUES
    ('Claudia', 'Mateo', 'claudia@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', TRUE),
    ('Yousra', 'Jebari', 'yousra@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', TRUE);

COMMIT;
