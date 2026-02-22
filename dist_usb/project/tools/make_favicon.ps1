# Convert Twinx logo PNG to favicon.ico
Add-Type -AssemblyName System.Drawing

$scriptDir = Split-Path -Parent $PSScriptRoot
if (-not $scriptDir) { $scriptDir = $PSScriptRoot }
$logoPath = Join-Path $scriptDir "Logo\Untitled design.png"
$faviconPath = Join-Path $scriptDir "public\favicon.ico"

if (-not (Test-Path $logoPath)) {
    $logoPath = Join-Path (Split-Path -Parent $scriptDir) "Logo\Untitled design.png"
    $faviconPath = Join-Path (Split-Path -Parent $scriptDir) "public\favicon.ico"
}

Write-Host "Source: $logoPath"
Write-Host "Target: $faviconPath"

$img = [System.Drawing.Image]::FromFile($logoPath)
$bmp = New-Object System.Drawing.Bitmap(256, 256)
$gfx = [System.Drawing.Graphics]::FromImage($bmp)
$gfx.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$gfx.DrawImage($img, 0, 0, 256, 256)
$gfx.Dispose()
$img.Dispose()

$hIcon = $bmp.GetHicon()
$icon = [System.Drawing.Icon]::FromHandle($hIcon)
$stream = [System.IO.FileStream]::new($faviconPath, [System.IO.FileMode]::Create)
$icon.Save($stream)
$stream.Close()
$icon.Dispose()
$bmp.Dispose()

Write-Host "Favicon created at: $faviconPath"
