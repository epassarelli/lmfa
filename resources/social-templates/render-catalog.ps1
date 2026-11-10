param(
  [string]$OutputDirectory = (Join-Path $PSScriptRoot '../../project/artifacts/social-catalog'),
  [switch]$ValidateOnly,
  [string]$CatalogPath = (Join-Path $PSScriptRoot 'catalog.v1.json'),
  [ValidateSet(1,3,4)][int]$LayoutVersion = 1
)
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$catalog = Get-Content -LiteralPath $CatalogPath -Raw -Encoding UTF8 | ConvertFrom-Json
$identity = $catalog.identity
$ids = @($catalog.templates.id)
if(($ids | Select-Object -Unique).Count -ne 12 -or $ids.Count -ne 12){throw 'Se esperan 12 IDs únicos'}
if(@($catalog.policy.active_templates).Count -ne 0){throw 'El catálogo de revisión no debe activar templates'}
$bitmap = [System.Drawing.Bitmap]::new(1080,1350)
$graphics = [System.Drawing.Graphics]::FromImage($bitmap)
$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit
$svg = [System.Collections.Generic.List[string]]::new()
$utf8 = [System.Text.UTF8Encoding]::new($false)
if($LayoutVersion -ge 3 -and @($catalog.templates.accent | Select-Object -Unique).Count -ne 12){throw 'Se necesitan 12 colores de sección distintos'}
function Luminance([string]$color){
  $channels=@(1,3,5 | ForEach-Object {[Convert]::ToInt32($color.Substring($_,2),16)/255.0})
  $linear=@($channels | ForEach-Object {if($_ -le 0.04045){$_/12.92}else{[Math]::Pow(($_+0.055)/1.055,2.4)}})
  return 0.2126*$linear[0]+0.7152*$linear[1]+0.0722*$linear[2]
}
function Contrast([string]$a,[string]$b){$x=Luminance $a;$y=Luminance $b;return ([Math]::Max($x,$y)+0.05)/([Math]::Min($x,$y)+0.05)}
function TabInk([string]$color){if((Contrast $color '#FFFFFF') -gt (Contrast $color $identity.background)){return '#FFFFFF'};return $identity.background}
function Esc([string]$value){return [System.Security.SecurityElement]::Escape($value)}
function NewFont([int]$size,[bool]$bold=$false){
  $style = if($bold){[System.Drawing.FontStyle]::Bold}else{[System.Drawing.FontStyle]::Regular}
  return [System.Drawing.Font]::new($identity.font,$size,$style,[System.Drawing.GraphicsUnit]::Pixel)
}
function Width([string]$value,[int]$size,[bool]$bold=$false){
  $font = NewFont $size $bold
  try {return $graphics.MeasureString($value,$font).Width} finally {$font.Dispose()}
}
function Wrap([string]$value,[int]$size,[int]$width,[bool]$bold=$false){
  if([string]::IsNullOrWhiteSpace($value)){throw 'Texto obligatorio vacío'}
  $result = [System.Collections.Generic.List[string]]::new(); $line = ''
  foreach($word in ($value.Trim() -split '\s+')){
    if((Width $word $size $bold) -gt $width){throw 'Palabra demasiado larga; requiere revisión editorial'}
    $candidate = if($line){"$line $word"}else{$word}
    if((Width $candidate $size $bold) -le $width){$line=$candidate}else{$result.Add($line);$line=$word}
  }
  if($line){$result.Add($line)}
  return $result.ToArray()
}
function FitHeadline([string]$value){
  if([string]::IsNullOrWhiteSpace($value)){throw 'Título obligatorio vacío'}
  for($size=[int]$identity.title_max_font;$size -ge [int]$identity.title_min_font;$size-=2){
    try {$lines=@(Wrap $value $size 925 $true)} catch {continue}
    $lineHeight=[int][Math]::Ceiling($size*1.23)
    $availableHeight=if($LayoutVersion -ge 3){414}else{492}
    if($lines.Count*$lineHeight -le $availableHeight){return @{lines=$lines;size=$size;lineHeight=$lineHeight}}
  }
  throw 'Título no entra; requiere revisión editorial sin truncar'
}
function Box($x,$y,$w,$h,$color){
  $brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($color))
  try {$graphics.FillRectangle($brush,$x,$y,$w,$h)} finally {$brush.Dispose()}
  $svg.Add("<rect x=`"$x`" y=`"$y`" width=`"$w`" height=`"$h`" fill=`"$color`"/>")
}
function Label([string]$value,$x,$y,[int]$size,[string]$color,[bool]$bold=$false){
  $font=NewFont $size $bold
  $brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($color))
  try {
    $measure=$graphics.MeasureString($value,$font)
    if($x+$measure.Width -gt 1010 -or $y+$measure.Height -gt 1310){throw "Desborde: $value"}
    $graphics.DrawString($value,$font,$brush,[single]$x,[single]$y)
    $weight=if($bold){700}else{400};$baseline=$y+$size
    $svg.Add("<text x=`"$($x+3)`" y=`"$baseline`" font-family=`"Segoe UI, sans-serif`" font-size=`"$size`" font-weight=`"$weight`" fill=`"$color`">$(Esc $value)</text>")
  } finally {$font.Dispose();$brush.Dispose()}
}
function ResolveTemplate([string]$id){
  $matches=@($catalog.templates | Where-Object {$_.id -eq $id})
  if($matches.Count -ne 1){throw 'Template desconocido; bloquear para revisión'}
  return $matches[0]
}
function DrawApprovedBrand {
  $format=[System.Drawing.StringFormat]::GenericTypographic.Clone()
  $format.FormatFlags=$format.FormatFlags -bor [System.Drawing.StringFormatFlags]::MeasureTrailingSpaces
  $font=NewFont 46 $true;$cursor=72.0
  $svg.Add('<g id="approved-multicolor-brand">')
  try {
    foreach($segment in @(@('Mi Folk','#93C5FD'),@('lor','#FFFFFF'),@('e','#FACC15'),@('Arg','#FFFFFF'),@('entino','#93C5FD'))){
      $brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($segment[1]))
      try {$graphics.DrawString($segment[0],$font,$brush,[System.Drawing.PointF]::new($cursor,57),$format)} finally {$brush.Dispose()}
      $svg.Add("<text x=`"$cursor`" y=`"103`" font-family=`"Segoe UI, sans-serif`" font-size=`"46`" font-weight=`"700`" fill=`"$($segment[1])`">$(Esc $segment[0])</text>")
      $cursor+=$graphics.MeasureString($segment[0],$font,10000,$format).Width
    }
  } finally {$font.Dispose();$format.Dispose()}
  $svg.Add('</g>')
}
function DrawV3($template){
  $accent=$template.accent;$white='#FFFFFF'
  Box 0 0 1080 1350 $identity.background
  DrawApprovedBrand
  Box 73 168 934 3 $accent
  if($LayoutVersion -eq 4){
    $tabWidth=[int][Math]::Ceiling((Width $template.section 36 $true)+42)
    if($tabWidth -gt 934){throw 'Solapa demasiado ancha'}
    $ink=TabInk $accent
    if((Contrast $accent $ink) -lt 4.5){throw 'Contraste insuficiente en solapa'}
    Box 73 225 $tabWidth 64 $accent
    Label $template.section 90 233 36 $ink $true
    Label $template.category 73 323 22 $white
  }else{
    $sectionSize=112
    while((Width $template.section $sectionSize $true) -gt 934){$sectionSize-=2;if($sectionSize -lt 48){throw 'Sección demasiado larga'}}
    Label $template.section 66 214 $sectionSize $accent $true
    Label $template.category 73 371 22 $white
  }
  $fit=FitHeadline $template.sample.headline
  for($i=0;$i -lt $fit.lines.Count;$i++){
    $color=if($i -eq $fit.lines.Count-1){$accent}else{$white}
    Label $fit.lines[$i] 68 (438+$fit.lineHeight*$i) $fit.size $color $true
  }
  Box 74 887 76 5 $accent
  Label $template.sample.details[0] 71 919 26 $white $true
  Label $template.sample.details[1] 71 959 26 $white $true
  $noteLines=@(if(-not [string]::IsNullOrWhiteSpace($template.sample.note)){Wrap $template.sample.note 28 810})
  if($noteLines.Count -gt 0 -and ($noteLines -join ' ') -ne (($template.sample.note.Trim() -split '\s+') -join ' ')){throw 'La bajada no se conservó íntegra'}
  for($i=0;$i -lt $noteLines.Count;$i++){Label $noteLines[$i] 71 (1023+38*$i) 28 $white}
  for($i=0;$i -lt 4;$i++){
    $y=928+$i*40
    $points=[System.Drawing.PointF[]]@([System.Drawing.PointF]::new(973,$y),[System.Drawing.PointF]::new(985,$y+12),[System.Drawing.PointF]::new(973,$y+24),[System.Drawing.PointF]::new(961,$y+12))
    $brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($accent))
    try {$graphics.FillPolygon($brush,$points)} finally {$brush.Dispose()}
    $svg.Add("<path d=`"M973 $y l12 12 -12 12 -12 -12Z`" fill=`"$accent`"/>")
  }
  Box 73 1166 934 2 $accent
  Label $template.cta 71 1195 23 $white $true
  Label $identity.domain 71 1238 26 $accent
  $watermark=if($LayoutVersion -eq 4 -and $template.sample_origin -eq 'api'){ 'PRUEBA CON CONTENIDO REAL · SIN PUBLICAR' }else{'SIMULACIÓN · DATOS DE EJEMPLO · NO PUBLICAR'}
  Label $watermark 71 1282 16 $white
}
function AssertRejected([scriptblock]$operation,[string]$case){
  $rejected=$false;try {& $operation | Out-Null} catch {$rejected=$true}
  if(-not $rejected){throw "No rechazó caso: $case"}
}
try {
  foreach($template in $catalog.templates){
    ResolveTemplate $template.id | Out-Null
    FitHeadline $template.sample.headline | Out-Null
    if(@($template.sample.details).Count -ne 2){throw "Muestra sin dos detalles: $($template.id)"}
    foreach($detail in $template.sample.details){if((Width $detail 26 $true) -gt 820){throw "Detalle largo: $($template.id)"}}
    if(-not [string]::IsNullOrWhiteSpace($template.sample.note) -and @(Wrap $template.sample.note 28 810).Count -gt 3){throw "Bajada larga: $($template.id)"}
    if($LayoutVersion -eq 4 -and (Contrast $template.accent (TabInk $template.accent)) -lt 4.5){throw 'Contraste insuficiente'}
  }
  FitHeadline 'Un título breve' | Out-Null
  FitHeadline 'Un recorrido por las distintas expresiones de nuestra música de raíz, sus artistas, sus encuentros y sus tradiciones' | Out-Null
  AssertRejected {FitHeadline ''} 'vacío'
  AssertRejected {FitHeadline ('palabra '*180)} 'título excesivo'
  AssertRejected {FitHeadline ('A'*140)} 'palabra indivisible'
  AssertRejected {ResolveTemplate 'inexistente'} 'template inexistente'
  if($LayoutVersion -eq 4 -and (TabInk '#242424') -ne '#FFFFFF'){throw 'No se eligió blanco sobre fondo oscuro'}
  if($ValidateOnly){Write-Output 'OK: 12 templates, títulos breves/largos y 4 rechazos controlados.';return}
  New-Item -ItemType Directory -Force $OutputDirectory | Out-Null
  $OutputDirectory=(Resolve-Path -LiteralPath $OutputDirectory).Path
  foreach($template in $catalog.templates){
    $svg.Clear()
    $svg.Add("<svg xmlns=`"http://www.w3.org/2000/svg`" width=`"1080`" height=`"1350`" viewBox=`"0 0 1080 1350`"><title>$(Esc $template.section) — MFA</title><desc>Simulación de diseño con datos de ejemplo. No publicar.</desc>")
    if($LayoutVersion -ge 3){
      DrawV3 $template
      $svg.Add('</svg>')
      $svgString=$svg -join "`n"
      $outsideBrand=[regex]::Replace($svgString,'(?s)<g id="approved-multicolor-brand">.*?</g>','')
      $colors=@([regex]::Matches($outsideBrand,'fill="(#[0-9A-Fa-f]{6})"') | ForEach-Object {$_.Groups[1].Value.ToUpperInvariant()} | Select-Object -Unique)
      $allowed=@($identity.background.ToUpperInvariant(),'#FFFFFF',$template.accent.ToUpperInvariant())
      if($colors.Count -ne 3 -or @($colors | Where-Object {$_ -notin $allowed}).Count -gt 0){throw 'Paleta fuera de la identidad v3'}
      $bitmap.Save((Join-Path $OutputDirectory "$($template.id).png"),[System.Drawing.Imaging.ImageFormat]::Png)
      [System.IO.File]::WriteAllText((Join-Path $OutputDirectory "$($template.id).svg"),$svgString,$utf8)
      continue
    }
    Box 0 0 1080 1350 $identity.background
    Box 0 0 1080 12 $template.accent
    Box 70 67 6 67 $identity.gold
    Label $identity.brand_lines[0] 94 55 34 $identity.sky $true
    Label $identity.brand_lines[1] 94 96 34 $identity.white $true
    Label $identity.tagline 590 91 19 $identity.muted
    Box 73 179 934 1 $identity.line
    $sectionWidth=[int][Math]::Ceiling((Width $template.section 23 $true)+36)
    Box 73 225 $sectionWidth 44 $template.accent
    Label $template.section 88 230 23 $identity.background $true
    Label $template.category (96+$sectionWidth) 235 18 $identity.muted
    $fit=FitHeadline $template.sample.headline
    for($i=0;$i -lt $fit.lines.Count;$i++){
      $color=if($i -eq $fit.lines.Count-1){$template.accent}else{$identity.white}
      Label $fit.lines[$i] 68 (320+$fit.lineHeight*$i) $fit.size $color $true
    }
    Box 76 850 78 5 $identity.gold
    Label $template.sample.details[0] 71 893 26 $identity.white $true
    Label $template.sample.details[1] 71 935 26 $identity.white $true
    $noteLines=@(Wrap $template.sample.note 28 810)
    for($i=0;$i -lt $noteLines.Count;$i++){Label $noteLines[$i] 71 (1000+38*$i) 28 $identity.muted}
    for($i=0;$i -lt 4;$i++){
      $y=881+$i*44
      $points=[System.Drawing.PointF[]]@([System.Drawing.PointF]::new(970,$y),[System.Drawing.PointF]::new(984,$y+14),[System.Drawing.PointF]::new(970,$y+28),[System.Drawing.PointF]::new(956,$y+14))
      $brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($template.accent))
      try {$graphics.FillPolygon($brush,$points)} finally {$brush.Dispose()}
      $svg.Add("<path d=`"M970 $y l14 14 -14 14 -14 -14Z`" fill=`"$($template.accent)`"/>")
    }
    Box 73 1152 934 1 $identity.line
    Label $template.cta 71 1189 23 $identity.white $true
    Label $identity.domain 71 1233 26 $identity.sky
    Label 'SIMULACIÓN · DATOS DE EJEMPLO · NO PUBLICAR' 71 1282 16 $identity.gold
    $svg.Add('</svg>')
    $bitmap.Save((Join-Path $OutputDirectory "$($template.id).png"),[System.Drawing.Imaging.ImageFormat]::Png)
    [System.IO.File]::WriteAllText((Join-Path $OutputDirectory "$($template.id).svg"),($svg -join "`n"),$utf8)
  }
  $cards=foreach($template in $catalog.templates){
    '<article><a href="'+$template.id+'.png"><img src="'+$template.id+'.png" alt="Simulación de '+(Esc $template.section)+'" loading="lazy" width="1080" height="1350"></a><h2>'+(Esc $template.section)+'</h2><p><code>'+$template.id+'</code></p><p>Recurso API: '+$template.api_resource+'</p><p><a href="'+$template.id+'.svg">SVG editable</a> · <a href="'+$template.id+'.png">PNG completo</a></p></article>'
  }
  $html='<!doctype html><html lang="es-AR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MFA · Catálogo de plantillas v1</title><style>body{margin:0;background:#eef2f6;color:#111e30;font:16px system-ui}header,main{max-width:1440px;margin:auto;padding:32px}header p{max-width:900px;line-height:1.6}main{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}article{background:white;border-radius:12px;overflow:hidden;padding-bottom:18px}article img{display:block;width:100%;height:auto}h2,article p{margin:16px 20px}a{color:#174f7c}code{font-size:13px}.tag{display:inline-block;padding:7px 12px;background:#e7c569;border-radius:4px}</style><header><span class="tag">CATÁLOGO EN REVISIÓN · V1</span><h1>Una identidad. Doce formas de contar MFA.</h1><p>Simulaciones con datos de ejemplo. Ninguna pieza está publicada ni habilita difusión automática. Azul oscuro, celeste, blanco y acento amarillo; firma, tipografía, márgenes y pie compartidos. Cada sección tiene un identificador fijo. Hacé clic en una placa para verla completa.</p><p>La existencia de una plantilla no acredita que el módulo tenga contenido curado o esté operativo. Canciones: solo ficha y créditos, sin letras ni audio. Agenda: verificar vigencia, lugar y fecha antes de difundir.</p></header><main>'+($cards -join "`n")+'</main></html>'
  [System.IO.File]::WriteAllText((Join-Path $OutputDirectory 'index.html'),$html,$utf8)
  if($LayoutVersion -ge 3){
    $html=$html.Replace('v1','v3').Replace('V1','V3').Replace('Azul oscuro, celeste, blanco y acento amarillo; firma, tipografía, márgenes y pie compartidos.','Fondo casi negro y blanco, con un color protagonista diferente por sección. La única excepción multicolor es el logo aprobado. Firma, tipografía, márgenes y pie compartidos.')
    [System.IO.File]::WriteAllText((Join-Path $OutputDirectory 'index.html'),$html,$utf8)
  }
  if($LayoutVersion -eq 4){
    $html=$html.Replace('v3','v4').Replace('V3','V4').Replace('Simulaciones con datos de ejemplo. Ninguna pieza está publicada ni habilita difusión automática.','Pruebas: nueve piezas con datos leídos de la API y tres simulaciones identificadas porque Peñas, Radios y Programas no devolvieron registros publicados. Ninguna pieza está publicada ni habilita difusión automática.').Replace('Cada sección tiene un identificador fijo.','La sección aparece en una solapa de color con texto de contraste calculado. Cada sección tiene un identificador fijo.')
    [System.IO.File]::WriteAllText((Join-Path $OutputDirectory 'index.html'),$html,$utf8)
  }
  for($page=0;$page -lt 2;$page++){
    $sheet=[System.Drawing.Bitmap]::new(1260,1190);$canvas=[System.Drawing.Graphics]::FromImage($sheet)
    $canvas.Clear([System.Drawing.ColorTranslator]::FromHtml('#E8EDF3'))
    $canvas.InterpolationMode=[System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    try {
      for($i=0;$i -lt 6;$i++){
        $template=$catalog.templates[$page*6+$i]
        $image=[System.Drawing.Image]::FromFile((Join-Path $OutputDirectory "$($template.id).png"))
        $x=30+($i%3)*410;$y=25+[Math]::Floor($i/3)*580
        try {$canvas.DrawImage($image,[int]$x,[int]$y,390,488)} finally {$image.Dispose()}
        $font=NewFont 17 $true;$brush=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml('#111E30'))
        try {$canvas.DrawString($template.id,$font,$brush,[single]$x,[single]($y+503))} finally {$font.Dispose();$brush.Dispose()}
      }
      $sheet.Save((Join-Path $OutputDirectory "catalogo-$($page+1).png"),[System.Drawing.Imaging.ImageFormat]::Png)
    } finally {$canvas.Dispose();$sheet.Dispose()}
  }
  foreach($template in $catalog.templates){
    $image=[System.Drawing.Image]::FromFile((Join-Path $OutputDirectory "$($template.id).png"))
    try {if($image.Width -ne 1080 -or $image.Height -ne 1350){throw 'Dimensiones incorrectas'}} finally {$image.Dispose()}
  }
  Write-Output "OK: 12 PNG 1080x1350, 12 SVG, catálogo HTML y 2 hojas de contacto en $OutputDirectory"
} finally {$graphics.Dispose();$bitmap.Dispose()}
