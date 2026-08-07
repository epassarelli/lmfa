$zipPath = 'C:\proyectos\lmfa\storage\app\exports\recipes\paquete_ia_recetas_20260806.zip'

if (Test-Path $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

$items = @(
    'C:\proyectos\lmfa\app\Console\Commands\AuditRecipesCommand.php',
    'C:\proyectos\lmfa\app\Support\RecipeContent.php',
    'C:\proyectos\lmfa\app\Http\Controllers\Frontend\RecetasController.php',
    'C:\proyectos\lmfa\app\Http\Controllers\Frontend\SitemapController.php',
    'C:\proyectos\lmfa\resources\views\frontend\recetas\show.blade.php',
    'C:\proyectos\lmfa\resources\css\app.css',
    'C:\proyectos\lmfa\tests\Feature\Recipes\PublicRecipesFrontendTest.php',
    'C:\proyectos\lmfa\tests\Feature\Recipes\RecipeAuditCommandTest.php',
    'C:\proyectos\lmfa\storage\app\exports\recipes\recetas_auditoria_20260806_175107.json',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\README.md',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\PAQUETE_IA_RESUMEN.txt',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\01-locro.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\02-humita-en-chala.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\03-empanadas-saltenas.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\04-guiso-de-lentejas.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\05-carbonada-en-zapallo.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\06-alfajores-de-maicena.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\07-empanadas-riojanas.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\08-humita-nortena.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\09-alfajores-santafesinos.html',
    'C:\proyectos\lmfa\storage\app\drafts\recipes\piloto_20260806\10-alfajores-saltenos.html'
)

Compress-Archive -Path $items -DestinationPath $zipPath -CompressionLevel Optimal

Get-Item $zipPath | Select-Object FullName, Length, LastWriteTime | Format-List
