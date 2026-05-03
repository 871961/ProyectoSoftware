# Test de login - Médico
$body = @{
    email = "elena.fernandez@clinica.com"
    password = "test123"
    role = "medico"
} | ConvertTo-Json

Write-Host "=== TEST LOGIN MÉDICO ===" -ForegroundColor Cyan
Write-Host "Enviando solicitud POST a: http://medhistory.local/backend/src/controllers/AuthController.php"
Write-Host "Body: $body`n"

try {
    $response = Invoke-WebRequest -Uri "http://medhistory.local/backend/src/controllers/AuthController.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body `
        -ErrorAction SilentlyContinue

    Write-Host "Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Green
    $response.Content | ConvertFrom-Json | ConvertTo-Json | Write-Host
} catch {
    Write-Host "Error:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}

Write-Host "`n=== TEST LOGIN PACIENTE ===" -ForegroundColor Cyan

$body2 = @{
    email = "test@test.com"
    password = "test123"
    role = "paciente"
} | ConvertTo-Json

Write-Host "Enviando solicitud POST a: http://medhistory.local/backend/src/controllers/AuthController.php"
Write-Host "Body: $body2`n"

try {
    $response = Invoke-WebRequest -Uri "http://medhistory.local/backend/src/controllers/AuthController.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body2 `
        -ErrorAction SilentlyContinue

    Write-Host "Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Green
    $response.Content | ConvertFrom-Json | ConvertTo-Json | Write-Host
} catch {
    Write-Host "Error:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}
