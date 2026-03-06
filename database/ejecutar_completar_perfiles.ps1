# Script para ejecutar completar_perfiles_salud.sql
# Fecha: Marzo 2026

Write-Host "Ejecutando completar_perfiles_salud.sql..." -ForegroundColor Cyan

# Configurar la contraseña como variable de entorno
$env:PGPASSWORD = "claudia"

# Ejecutar el script SQL
psql -U postgres -d medhistory -f database\completar_perfiles_salud.sql

# Verificar el código de salida
if ($LASTEXITCODE -eq 0) {
    Write-Host "Script ejecutado correctamente." -ForegroundColor Green
} else {
    Write-Host "Error al ejecutar el script. Código de salida: $LASTEXITCODE" -ForegroundColor Red
}

# Limpiar la variable de entorno
Remove-Item Env:\PGPASSWORD
