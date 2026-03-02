# Script de ayuda para ejecutar archivos SQL en PostgreSQL
# Uso: .\ejecutar_sql.ps1 [schema|datos|ambos]

param(
    [string]$Opcion = "ambos"
)

# Buscar la instalación de PostgreSQL
$PostgresPaths = @(
    "C:\Program Files\PostgreSQL\18\bin\psql.exe",
    "C:\Program Files\PostgreSQL\17\bin\psql.exe",
    "C:\Program Files\PostgreSQL\16\bin\psql.exe",
    "C:\Program Files\PostgreSQL\15\bin\psql.exe",
    "C:\xampp\postgresql\bin\psql.exe"
)

$psqlPath = $null
foreach ($path in $PostgresPaths) {
    if (Test-Path $path) {
        $psqlPath = $path
        break
    }
}

if (-not $psqlPath) {
    Write-Host "ERROR: No se encontro psql.exe en las rutas comunes." -ForegroundColor Red
    Write-Host "Busque manualmente la ubicacion de psql.exe y ejecute:" -ForegroundColor Yellow
    Write-Host '  & "C:\ruta\a\psql.exe" -U postgres -d medhistory -f .\database\schema.sql' -ForegroundColor Cyan
    exit 1
}

Write-Host "PostgreSQL encontrado en: $psqlPath" -ForegroundColor Green

# Credenciales (ajustar si son diferentes)
$usuario = "postgres"
$basedatos = "medhistory"

switch ($Opcion.ToLower()) {
    "schema" {
        Write-Host "`nEjecutando schema.sql..." -ForegroundColor Cyan
        & $psqlPath -U $usuario -d $basedatos -f ".\database\schema.sql"
    }
    "datos" {
        Write-Host "`nEjecutando datos_prueba.sql..." -ForegroundColor Cyan
        & $psqlPath -U $usuario -d $basedatos -f ".\database\datos_prueba.sql"
    }
    "ambos" {
        Write-Host "`nEjecutando schema.sql..." -ForegroundColor Cyan
        & $psqlPath -U $usuario -d $basedatos -f ".\database\schema.sql"
        Write-Host "`nEjecutando datos_prueba.sql..." -ForegroundColor Cyan
        & $psqlPath -U $usuario -d $basedatos -f ".\database\datos_prueba.sql"
    }
    default {
        Write-Host "Opcion no valida. Use: schema, datos o ambos" -ForegroundColor Red
        exit 1
    }
}

Write-Host "`nEjecucion completada." -ForegroundColor Green
Write-Host "Ahora puede probar el sistema accediendo a: http://medHistory.local/test_db.php" -ForegroundColor Yellow
