# Script para completar antecedentes familiares de pacientes
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Completando antecedentes familiares" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan

$env:PGPASSWORD = "claudia"

Write-Host ""
Write-Host "Ejecutando script de completado..." -ForegroundColor Yellow
psql -U postgres -d medhistory -f database\completar_antecedentes.sql

if ($LASTEXITCODE -eq 0) {
    Write-Host "   Script ejecutado correctamente" -ForegroundColor Green
}
else {
    Write-Host "   Error al ejecutar el script" -ForegroundColor Red
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Proceso completado!" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
