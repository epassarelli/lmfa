$taskName = "MFA-Orquestador-Autonomo"
$projectRoot = "C:\proyectos\lmfa"
$command = "codex exec --cwd `"$projectRoot`" -- `"$projectRoot\artisan`" mfa:orchestrate-backlog --project=mfa"

Write-Host "Instalador preparado. No activa ninguna programación por sí mismo."
Write-Host ""
Write-Host "Nombre sugerido de la tarea: $taskName"
Write-Host "Comando sugerido:"
Write-Host $command
Write-Host ""
Write-Host "Ejemplo manual con schtasks (no se ejecuta automáticamente):"
Write-Host "schtasks /Create /TN `"$taskName`" /SC DAILY /ST 11:00 /TR `"$command`""
