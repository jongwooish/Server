# =====================================================================
#  Prepare-WindowsTemplate.ps1  (Windows Server 2022 / 2025)
#  Run inside the Windows Template VM right before taking the "Template" snapshot.
#  Generalizes the install with Sysprep (new SID on every clone) and shuts down.
#
#  usage (PowerShell as Administrator):
#    Set-ExecutionPolicy -Scope Process Bypass
#    .\Prepare-WindowsTemplate.ps1
# =====================================================================

$admin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole(
    [Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $admin) {
    Write-Error "Run PowerShell as Administrator."
    exit 1
}

# Sysprep must not run on a domain controller or a domain-joined machine
$cs = Get-CimInstance Win32_ComputerSystem
if ($cs.PartOfDomain) {
    Write-Error "This machine is joined to a domain. Leave the domain before running Sysprep."
    exit 1
}

Write-Host "[1/2] Clearing temp files"
Remove-Item -Path "$env:TEMP\*" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "C:\Windows\Temp\*" -Recurse -Force -ErrorAction SilentlyContinue

Write-Host "[2/2] Running Sysprep: /generalize /oobe /shutdown"
Write-Host "      The VM powers off when finished - then take the 'Template' snapshot."
& "$env:WINDIR\System32\Sysprep\sysprep.exe" /generalize /oobe /shutdown
