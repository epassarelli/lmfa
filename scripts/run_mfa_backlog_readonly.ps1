param(
    [ValidateSet('self_test', 'read_backlog')]
    [string]$Mode = 'self_test',
    [string]$SpreadsheetId = '1kT8VnE83aUKfV0FtZH1sX1MSCI-njzf7K0lbmSFrXYs',
    [string]$SheetName = 'Backlog',
    [string]$ReadRange = 'A1:AG997',
    [string]$ProjectFragment = 'Mi Folklore Argentino',
    [switch]$NoLog
)

$ErrorActionPreference = 'Stop'

function ConvertTo-StructuredResult {
    param(
        [string]$Status,
        [string]$Summary,
        [hashtable]$Data
    )

    return [ordered]@{
        timestamp = (Get-Date).ToString('s')
        status = $Status
        summary = $Summary
        data = $Data
    }
}

function Write-StructuredLog {
    param(
        [hashtable]$Payload,
        [string]$LogDirectory,
        [switch]$SkipLog
    )

    if ($SkipLog) {
        return $null
    }

    if (-not (Test-Path -LiteralPath $LogDirectory)) {
        New-Item -ItemType Directory -Path $LogDirectory -Force | Out-Null
    }

    $logPath = Join-Path $LogDirectory ("backlog_readonly_{0}.json" -f (Get-Date -Format 'yyyyMMdd_HHmmss'))
    $Payload | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $logPath -Encoding UTF8

    return $logPath
}

function Get-ProjectRoot {
    return (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
}

function Get-ExpectedTaskCommand {
    $scriptPath = Join-Path $PSScriptRoot 'run_mfa_backlog_readonly.ps1'
    return ('powershell.exe -NoProfile -ExecutionPolicy Bypass -File "{0}" -Mode read_backlog' -f $scriptPath)
}

function Get-RequiredHeaders {
    return @(
        'ID',
        'Proyecto',
        'Tarea',
        'Estado delegacion',
        'Estado',
        'Prioridad sugerida',
        'Notas cierre'
    )
}

function Get-GoogleAccessToken {
    $clientId = $env:MFA_GOOGLE_CLIENT_ID
    $clientSecret = $env:MFA_GOOGLE_CLIENT_SECRET
    $refreshToken = $env:MFA_GOOGLE_REFRESH_TOKEN

    $missing = @()
    if ([string]::IsNullOrWhiteSpace($clientId)) { $missing += 'MFA_GOOGLE_CLIENT_ID' }
    if ([string]::IsNullOrWhiteSpace($clientSecret)) { $missing += 'MFA_GOOGLE_CLIENT_SECRET' }
    if ([string]::IsNullOrWhiteSpace($refreshToken)) { $missing += 'MFA_GOOGLE_REFRESH_TOKEN' }

    if ($missing.Count -gt 0) {
        throw ('Faltan variables de entorno OAuth: {0}' -f ($missing -join ', '))
    }

    $tokenResponse = Invoke-RestMethod -Method Post -Uri 'https://oauth2.googleapis.com/token' -ContentType 'application/x-www-form-urlencoded' -Body @{
        client_id = $clientId
        client_secret = $clientSecret
        refresh_token = $refreshToken
        grant_type = 'refresh_token'
    }

    if ([string]::IsNullOrWhiteSpace($tokenResponse.access_token)) {
        throw 'Google OAuth no devolvió access_token para el runner read-only.'
    }

    return $tokenResponse.access_token
}

function Get-BacklogRows {
    param(
        [string]$AccessToken,
        [string]$SpreadsheetId,
        [string]$SheetName,
        [string]$ReadRange
    )

    $escapedRange = [System.Uri]::EscapeDataString(("{0}!{1}" -f $SheetName, $ReadRange))
    $uri = "https://sheets.googleapis.com/v4/spreadsheets/$SpreadsheetId/values/$escapedRange?majorDimension=ROWS"
    $headers = @{
        Authorization = "Bearer $AccessToken"
    }

    return Invoke-RestMethod -Method Get -Uri $uri -Headers $headers
}

function ConvertTo-RowObjects {
    param(
        [object[]]$Values
    )

    if (-not $Values -or $Values.Count -lt 2) {
        throw 'La lectura del backlog no devolvió encabezado y filas suficientes.'
    }

    $headers = @($Values[0])
    $requiredHeaders = Get-RequiredHeaders
    $missingHeaders = @()

    foreach ($header in $requiredHeaders) {
        if ($headers -notcontains $header) {
            $missingHeaders += $header
        }
    }

    if ($missingHeaders.Count -gt 0) {
        throw ('Faltan encabezados esperados en Drive: {0}' -f ($missingHeaders -join ', '))
    }

    $rows = @()
    for ($i = 1; $i -lt $Values.Count; $i++) {
        $rawRow = @($Values[$i])
        if ($rawRow.Count -eq 0) {
            continue
        }

        $rowObject = [ordered]@{
            __row_number = $i + 1
        }

        for ($columnIndex = 0; $columnIndex -lt $headers.Count; $columnIndex++) {
            $header = [string]$headers[$columnIndex]
            $rowObject[$header] = if ($columnIndex -lt $rawRow.Count) { [string]$rawRow[$columnIndex] } else { '' }
        }

        $rows += [pscustomobject]$rowObject
    }

    return @{
        headers = $headers
        rows = $rows
    }
}

function Get-BacklogSummary {
    param(
        [object[]]$Rows,
        [string]$ProjectFragment
    )

    $projectRows = @($Rows | Where-Object { $_.Proyecto -like "*$ProjectFragment*" })
    $candidateRows = @(
        $projectRows |
        Where-Object {
            $_.Estado -in @('Pendiente', 'Parcial') -and
            $_.'Prioridad sugerida' -in @('P1', 'P2')
        } |
        Select-Object -First 10
    )

    return [ordered]@{
        total_rows = $Rows.Count
        project_rows = $projectRows.Count
        pending_or_partial = @($projectRows | Where-Object { $_.Estado -in @('Pendiente', 'Parcial') }).Count
        top_candidates = @(
            $candidateRows | ForEach-Object {
                [ordered]@{
                    row_number = $_.__row_number
                    id = $_.ID
                    title = $_.Tarea
                    status = $_.Estado
                    delegation_state = $_.'Estado delegacion'
                    priority = $_.'Prioridad sugerida'
                }
            }
        )
    }
}

$projectRoot = Get-ProjectRoot
$logDirectory = Join-Path $projectRoot 'project\automation\native_drive\logs'
$taskCommand = Get-ExpectedTaskCommand

if ($Mode -eq 'self_test') {
    $data = [ordered]@{
        project_root = $projectRoot
        script_path = (Join-Path $PSScriptRoot 'run_mfa_backlog_readonly.ps1')
        spreadsheet_id = $SpreadsheetId
        sheet_name = $SheetName
        read_range = $ReadRange
        log_directory = $logDirectory
        task_command = $taskCommand
        oauth_env_present = [ordered]@{
            MFA_GOOGLE_CLIENT_ID = -not [string]::IsNullOrWhiteSpace($env:MFA_GOOGLE_CLIENT_ID)
            MFA_GOOGLE_CLIENT_SECRET = -not [string]::IsNullOrWhiteSpace($env:MFA_GOOGLE_CLIENT_SECRET)
            MFA_GOOGLE_REFRESH_TOKEN = -not [string]::IsNullOrWhiteSpace($env:MFA_GOOGLE_REFRESH_TOKEN)
        }
    }

    $result = ConvertTo-StructuredResult -Status 'ready_for_readonly_probe' -Summary 'Self-test local completado sin llamadas a Google.' -Data $data
    $logPath = Write-StructuredLog -Payload $result -LogDirectory $logDirectory -SkipLog:$NoLog
    if ($logPath) {
        $result.log_path = $logPath
    }

    $result | ConvertTo-Json -Depth 8
    exit 0
}

try {
    $accessToken = Get-GoogleAccessToken
    $response = Get-BacklogRows -AccessToken $accessToken -SpreadsheetId $SpreadsheetId -SheetName $SheetName -ReadRange $ReadRange
    $converted = ConvertTo-RowObjects -Values $response.values
    $summary = Get-BacklogSummary -Rows $converted.rows -ProjectFragment $ProjectFragment

    $result = ConvertTo-StructuredResult -Status 'completed' -Summary 'Lectura read-only del backlog completada.' -Data ([ordered]@{
        spreadsheet_id = $SpreadsheetId
        sheet_name = $SheetName
        read_range = $ReadRange
        summary = $summary
    })

    $logPath = Write-StructuredLog -Payload $result -LogDirectory $logDirectory -SkipLog:$NoLog
    if ($logPath) {
        $result.log_path = $logPath
    }

    $result | ConvertTo-Json -Depth 8
    exit 0
} catch {
    $result = ConvertTo-StructuredResult -Status 'blocked' -Summary $_.Exception.Message -Data ([ordered]@{
        spreadsheet_id = $SpreadsheetId
        sheet_name = $SheetName
        read_range = $ReadRange
        task_command = $taskCommand
    })

    $logPath = Write-StructuredLog -Payload $result -LogDirectory $logDirectory -SkipLog:$NoLog
    if ($logPath) {
        $result.log_path = $logPath
    }

    $result | ConvertTo-Json -Depth 8
    exit 1
}
