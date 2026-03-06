# Script para actualizar tabla de antecedentes familiares y poblarla con datos
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Configurando antecedentes familiares" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan

$env:PGPASSWORD = "claudia"

Write-Host "`n1. Actualizando estructura de tabla..." -ForegroundColor Yellow
psql -U postgres -d medhistory -f database\actualizar_antecedentes.sql

if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Estructura actualizada correctamente" -ForegroundColor Green
    
    Write-Host "`n2. Poblando antecedentes para pacientes existentes..." -ForegroundColor Yellow
    psql -U postgres -d medhistory -f database\poblar_antecedentes.sql
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Antecedentes creados correctamente" -ForegroundColor Green
    }
    else {
        Write-Host "   ✗ Error al poblar antecedentes" -ForegroundColor Red
    }
}
else {
    Write-Host "   ✗ Error al actualizar estructura" -ForegroundColor Red
}

Write-Host "`n===============================================" -ForegroundColor Cyan
Write-Host "Proceso completado!" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "`nPresiona cualquier tecla para continuar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
