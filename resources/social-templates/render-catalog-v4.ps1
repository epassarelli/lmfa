param([switch]$ValidateOnly)
$ErrorActionPreference='Stop'
& (Join-Path $PSScriptRoot 'render-catalog.ps1') -CatalogPath (Join-Path $PSScriptRoot 'catalog.v4.json') -LayoutVersion 4 -OutputDirectory (Join-Path $PSScriptRoot '../../project/artifacts/social-catalog-v4') -ValidateOnly:$ValidateOnly
