param([switch]$ValidateOnly)
$ErrorActionPreference='Stop'
& (Join-Path $PSScriptRoot 'render-catalog.ps1') -CatalogPath (Join-Path $PSScriptRoot 'catalog.v3.json') -LayoutVersion 3 -OutputDirectory (Join-Path $PSScriptRoot '../../project/artifacts/social-catalog-v3') -ValidateOnly:$ValidateOnly
