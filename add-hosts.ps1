# Script à exécuter EN TANT QU'ADMINISTRATEUR
# Ajoute miensafleet.test dans le fichier hosts Windows

$hostsFile = 'C:\Windows\System32\drivers\etc\hosts'
$content = Get-Content $hostsFile -Raw

$entries = @(
    '127.0.0.1      miensafleet.test    #laragon magic!'
)

foreach ($entry in $entries) {
    $domain = ($entry -split '\s+')[1]
    if ($content -notmatch [regex]::Escape($domain)) {
        Add-Content $hostsFile ("`n" + $entry)
        Write-Host "AJOUTE : $domain" -ForegroundColor Green
    } else {
        Write-Host "DEJA PRESENT : $domain" -ForegroundColor Yellow
    }
}

Write-Host "`nRedemarrez Laragon (Stop > Start) pour appliquer le nouveau vhost." -ForegroundColor Cyan
Read-Host "Appuyez sur Entree pour fermer"
