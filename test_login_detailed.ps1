# Test de login con captura de error detallada
$body = @{
    email = "elena.fernandez@clinica.com"
    password = "test123"
    role = "medico"
} | ConvertTo-Json

Write-Host "=== TEST LOGIN MÉDICO - CON DETALLES DE ERROR ===" -ForegroundColor Cyan

try {
    $response = Invoke-WebRequest -Uri "http://medhistory.local/backend/src/controllers/AuthController.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body
} catch {
    Write-Host "Status Code: $($_.Exception.Response.StatusCode.Value)" -ForegroundColor Yellow
    Write-Host "Status Description: $($_.Exception.Response.StatusDescription)" -ForegroundColor Yellow

    # Intentar leer el contenido del error
    $errorResponse = $_.Exception.Response.GetResponseStream()
    $reader = [System.IO.StreamReader]::new($errorResponse)
    $errorContent = $reader.ReadToEnd()

    Write-Host "Response Content:" -ForegroundColor Yellow
    Write-Host $errorContent
}

# También probar el DebugRaw para ver qué recibe el servidor
Write-Host "`n=== TEST DebugRaw ===" -ForegroundColor Cyan

try {
    $response = Invoke-WebRequest -Uri "http://medhistory.local/backend/src/controllers/DebugRaw.php" `
        -Method POST `
        -ContentType "application/json" `
        -Body $body

    Write-Host "Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Green
    Write-Host $response.Content
} catch {
    Write-Host "Error:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}
