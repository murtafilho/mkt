# PowerShell Script: Gerar Favicons PNG a partir do SVG
# Requer: ImageMagick instalado (https://imagemagick.org/script/download.php#windows)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Gerador de Favicons - Marketplace" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se ImageMagick está instalado
$magickPath = Get-Command magick -ErrorAction SilentlyContinue

if (-not $magickPath) {
    Write-Host "ERRO: ImageMagick não encontrado!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Instale o ImageMagick primeiro:" -ForegroundColor Yellow
    Write-Host "1. Via Chocolatey: choco install imagemagick" -ForegroundColor White
    Write-Host "2. Ou baixe: https://imagemagick.org/script/download.php#windows" -ForegroundColor White
    Write-Host ""
    Write-Host "Alternativa: Use RealFaviconGenerator online" -ForegroundColor Yellow
    Write-Host "https://realfavicongenerator.net/" -ForegroundColor White
    exit 1
}

Write-Host "✓ ImageMagick encontrado: $($magickPath.Source)" -ForegroundColor Green
Write-Host ""

# Definir caminhos
$svgPath = "public/favicon.svg"
$publicDir = "public"

# Verificar se SVG existe
if (-not (Test-Path $svgPath)) {
    Write-Host "ERRO: $svgPath não encontrado!" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Arquivo fonte: $svgPath" -ForegroundColor Green
Write-Host ""
Write-Host "Gerando arquivos PNG..." -ForegroundColor Cyan
Write-Host ""

# Array de tamanhos e nomes
$favicons = @(
    @{ Size = "16x16"; File = "favicon-16x16.png" },
    @{ Size = "32x32"; File = "favicon-32x32.png" },
    @{ Size = "180x180"; File = "apple-touch-icon.png" },
    @{ Size = "192x192"; File = "android-chrome-192x192.png" },
    @{ Size = "512x512"; File = "android-chrome-512x512.png" }
)

# Gerar cada favicon
$successCount = 0
$errorCount = 0

foreach ($favicon in $favicons) {
    $outputPath = Join-Path $publicDir $favicon.File

    Write-Host "  ► $($favicon.File) ($($favicon.Size))..." -NoNewline

    try {
        # Comando ImageMagick
        $arguments = @(
            $svgPath,
            "-resize", $favicon.Size,
            "-background", "white",
            "-flatten",
            $outputPath
        )

        & magick @arguments 2>&1 | Out-Null

        if (Test-Path $outputPath) {
            $fileSize = (Get-Item $outputPath).Length
            Write-Host " OK ($fileSize bytes)" -ForegroundColor Green
            $successCount++
        } else {
            Write-Host " FALHOU" -ForegroundColor Red
            $errorCount++
        }
    }
    catch {
        Write-Host " ERRO: $($_.Exception.Message)" -ForegroundColor Red
        $errorCount++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Resultado" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✓ Sucesso: $successCount arquivos" -ForegroundColor Green
if ($errorCount -gt 0) {
    Write-Host "✗ Erros: $errorCount arquivos" -ForegroundColor Red
}
Write-Host ""

# Listar arquivos gerados
Write-Host "Arquivos gerados em public/:" -ForegroundColor Cyan
Get-ChildItem $publicDir -Filter "*icon*.png" | ForEach-Object {
    $size = "{0:N2} KB" -f ($_.Length / 1KB)
    Write-Host "  • $($_.Name) - $size" -ForegroundColor White
}

Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Yellow
Write-Host "1. Teste no navegador: http://localhost:8000" -ForegroundColor White
Write-Host "2. Limpe o cache: Ctrl+Shift+Delete" -ForegroundColor White
Write-Host "3. Verifique o manifest: http://localhost:8000/site.webmanifest" -ForegroundColor White
Write-Host ""
Write-Host "Documentação completa: docs/FAVICON_GUIA.md" -ForegroundColor Cyan
