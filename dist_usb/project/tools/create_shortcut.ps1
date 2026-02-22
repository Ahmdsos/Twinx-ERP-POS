# PowerShell Script: Create Desktop Shortcut + Convert Logo to ICO
# Called automatically by SETUP.bat

param(
    [string]$ProjectPath = "",
    [string]$AppUrl = "http://localhost/twinx-erp/public"
)

$ErrorActionPreference = "SilentlyContinue"

if (-not $ProjectPath) {
    $ProjectPath = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
}

$LogoSrc = Join-Path $ProjectPath "Logo\Untitled design.png"
$IcoFile = Join-Path $ProjectPath "Logo\twinx.ico"
$Desktop = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = Join-Path $Desktop "Twinx ERP.lnk"

Write-Host ""
Write-Host "  Creating desktop shortcut..." -ForegroundColor Cyan

# --- Convert PNG to ICO ---
if ((Test-Path $LogoSrc) -and -not (Test-Path $IcoFile)) {
    try {
        Add-Type -AssemblyName System.Drawing
        $img = [System.Drawing.Image]::FromFile($LogoSrc)

        $bmp = New-Object System.Drawing.Bitmap(256, 256)
        $g = [System.Drawing.Graphics]::FromImage($bmp)
        $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $g.DrawImage($img, 0, 0, 256, 256)
        $g.Dispose()
        $img.Dispose()

        $icon = [System.Drawing.Icon]::FromHandle($bmp.GetHicon())
        $fs = [System.IO.FileStream]::new($IcoFile, [System.IO.FileMode]::Create)
        $icon.Save($fs)
        $fs.Close()
        $icon.Dispose()
        $bmp.Dispose()

        Write-Host "  [OK] Logo converted to ICO" -ForegroundColor Green
    }
    catch {
        Write-Host "  [WARN] Could not convert logo: $_" -ForegroundColor Yellow
    }
}

# --- Locate launcher script (silent VBS wrapper) ---
$LauncherScript = Join-Path $ProjectPath "tools\launch_twinx.vbs"

if (-not (Test-Path $LauncherScript)) {
    Write-Host "  [FAIL] launch_twinx.vbs not found at: $LauncherScript" -ForegroundColor Red
    exit 1
}

Write-Host "  [OK] Launcher script found" -ForegroundColor Green

# --- Create Desktop shortcut (points to silent launcher) ---
$WshShell = New-Object -ComObject WScript.Shell
$shortcut = $WshShell.CreateShortcut($ShortcutPath)
$shortcut.TargetPath = (Get-Command wscript.exe).Source
$shortcut.Arguments = "`"$LauncherScript`""
$shortcut.WorkingDirectory = $ProjectPath
$shortcut.Description = "Twinx ERP - Enterprise Resource Planning"
$shortcut.WindowStyle = 1

if (Test-Path $IcoFile) {
    $shortcut.IconLocation = "$IcoFile,0"
    Write-Host "  [OK] Custom icon set" -ForegroundColor Green
}
else {
    Write-Host "  [WARN] Using default browser icon" -ForegroundColor Yellow
}

$shortcut.Save()
Write-Host "  [OK] Desktop shortcut created: $ShortcutPath" -ForegroundColor Green

# --- Create Start Menu shortcut ---
$StartMenu = [Environment]::GetFolderPath("Programs")
$StartShortcut = Join-Path $StartMenu "Twinx ERP.lnk"
$shortcut2 = $WshShell.CreateShortcut($StartShortcut)
$shortcut2.TargetPath = (Get-Command wscript.exe).Source
$shortcut2.Arguments = "`"$LauncherScript`""
$shortcut2.WorkingDirectory = $ProjectPath
$shortcut2.Description = "Twinx ERP - Enterprise Resource Planning"
$shortcut2.WindowStyle = 1

if (Test-Path $IcoFile) {
    $shortcut2.IconLocation = "$IcoFile,0"
}
$shortcut2.Save()
Write-Host "  [OK] Start Menu shortcut created" -ForegroundColor Green

Write-Host ""
Write-Host "  Desktop shortcut 'Twinx ERP' ready!" -ForegroundColor Green
Write-Host "  Auto-starts Apache + MySQL on launch!" -ForegroundColor Green
Write-Host "  POS printing will be SILENT." -ForegroundColor Green
Write-Host ""
