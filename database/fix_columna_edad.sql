-- Script para renombrar columna edad_diagnóstico a edad_diagnostico
-- Elimina el problema de codificación con la tilde
-- Fecha: Marzo 2026

-- Renombrar la columna para evitar problemas de codificación
ALTER TABLE antecedentes_familiares 
  RENAME COLUMN "edad_diagnóstico" TO edad_diagnostico;

-- Verificar el cambio
\d antecedentes_familiares

\echo ''
\echo 'Columna renombrada correctamente: edad_diagnóstico -> edad_diagnostico'
