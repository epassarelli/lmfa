$scriptPath = Join-Path $PSScriptRoot 'run_mfa_backlog_readonly.ps1'

powershell.exe -NoProfile -ExecutionPolicy Bypass -File $scriptPath -Mode self_test
