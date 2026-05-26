param(
    [string]$Filter = "WorkflowSmoke"
)

Write-Host "=== CPS Feedback Loop ===" -ForegroundColor Cyan
Write-Host "Filter: $Filter" -ForegroundColor Gray
Write-Host ""

# Clear config
php artisan config:clear 2>&1 | Out-Null

# Run tests
$result = php artisan test --filter=$Filter --compact 2>&1
$exitCode = $LASTEXITCODE

Write-Host ""
if ($exitCode -eq 0) {
    Write-Host "GREEN - All tests passed" -ForegroundColor Green
} else {
    Write-Host "RED - Tests failed (expected in red phase)" -ForegroundColor Red
}

exit $exitCode
