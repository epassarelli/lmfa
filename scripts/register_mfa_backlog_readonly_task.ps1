param(
    [string]$TaskName = 'MFA-Backlog-Drive-ReadOnly',
    [string]$StartTime = '11:00'
)

$ErrorActionPreference = 'Stop'

$runnerPath = Join-Path $PSScriptRoot 'run_mfa_backlog_readonly.ps1'
$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument ('-NoProfile -ExecutionPolicy Bypass -File "{0}" -Mode read_backlog' -f $runnerPath)
$trigger = New-ScheduledTaskTrigger -Daily -At ([datetime]::ParseExact($StartTime, 'HH:mm', $null))
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries

Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -Description 'MFA backlog de Drive en modo nativo read-only. Se registra deshabilitada por defecto.' -Force | Out-Null
Disable-ScheduledTask -TaskName $TaskName | Out-Null

[ordered]@{
    status = 'registered_disabled'
    task_name = $TaskName
    start_time = $StartTime
    runner = $runnerPath
    action = ('powershell.exe -NoProfile -ExecutionPolicy Bypass -File "{0}" -Mode read_backlog' -f $runnerPath)
} | ConvertTo-Json -Depth 5
