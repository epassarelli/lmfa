param([string]$OutputDirectory=(Join-Path $PSScriptRoot '../../project/artifacts/social-preview'))
$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.Drawing
$payload=Get-Content -LiteralPath (Join-Path $PSScriptRoot '../../project/artifacts/social-preview/sample.json') -Raw -Encoding UTF8 | ConvertFrom-Json
New-Item -ItemType Directory -Force $OutputDirectory | Out-Null
$bitmap=[System.Drawing.Bitmap]::new(1080,1350)
$g=[System.Drawing.Graphics]::FromImage($bitmap)
$g.SmoothingMode=[System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint=[System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit
$svg=[System.Collections.Generic.List[string]]::new()
$svg.Add('<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1350" viewBox="0 0 1080 1350"><title>Noticias MFA — propuesta v2</title><desc>Vista previa de una noticia publicada. Sin publicación en redes.</desc>')
$cream='#F6EFE3';$brown='#503324';$orange='#CF632D';$blue='#93C5FD';$navy='#293241';$white='#FFFFFF'
$format=[System.Drawing.StringFormat]::GenericTypographic.Clone()
$format.FormatFlags=$format.FormatFlags -bor [System.Drawing.StringFormatFlags]::MeasureTrailingSpaces
function Font($size,$bold=$false){$style=if($bold){[System.Drawing.FontStyle]::Bold}else{[System.Drawing.FontStyle]::Regular};return [System.Drawing.Font]::new('Segoe UI',$size,$style,[System.Drawing.GraphicsUnit]::Pixel)}
function MeasureText($value,$size,$bold=$false){$font=Font $size $bold;try{return $g.MeasureString($value,$font,10000,$format).Width}finally{$font.Dispose()}}
function Rect($x,$y,$w,$h,$color){$b=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($color));try{$g.FillRectangle($b,$x,$y,$w,$h)}finally{$b.Dispose()};$svg.Add("<rect x=`"$x`" y=`"$y`" width=`"$w`" height=`"$h`" fill=`"$color`"/>")}
function Text($value,$x,$y,$size,$color,$bold=$false){
  $font=Font $size $bold;$b=[System.Drawing.SolidBrush]::new([System.Drawing.ColorTranslator]::FromHtml($color))
  try{
    $width=$g.MeasureString($value,$font,10000,$format).Width
    if($x+$width -gt 1010){throw "Texto fuera de margen: $value"}
    $g.DrawString($value,$font,$b,[System.Drawing.PointF]::new($x,$y),$format)
    $escaped=[System.Security.SecurityElement]::Escape($value);$weight=if($bold){700}else{400};$baseline=$y+$size
    $svg.Add("<text x=`"$x`" y=`"$baseline`" font-family=`"Segoe UI, sans-serif`" font-size=`"$size`" font-weight=`"$weight`" fill=`"$color`">$escaped</text>")
  }finally{$font.Dispose();$b.Dispose()}
}
function Lines($value,$size,$width,$bold=$false){
  $result=[System.Collections.Generic.List[string]]::new();$line=''
  foreach($word in ($value -split '\s+')){
    if((MeasureText $word $size $bold) -gt $width){throw 'Palabra demasiado larga'}
    $candidate=if($line){"$line $word"}else{$word}
    if((MeasureText $candidate $size $bold) -le $width){$line=$candidate}else{$result.Add($line);$line=$word}
  };if($line){$result.Add($line)};return $result.ToArray()
}
try{
  Rect 0 0 1080 1350 $cream
  Rect 0 0 1080 168 $navy
  Rect 0 166 1080 8 $orange
  # Reproduce los segmentos y colores de navbar.blade.php, sin separar palabras.
  $segments=@(@('Mi Folk',$blue),@('lor',$white),@('e','#FACC15'),@('Arg',$white),@('entino',$blue))
  $cursor=72.0
  foreach($segment in $segments){Text $segment[0] $cursor 57 46 $segment[1] $true;$cursor+=MeasureText $segment[0] 46 $true}
  Text 'NOTICIAS' 68 208 112 $orange $true
  Rect 73 351 929 2 '#D9C6B3'
  Text 'LANZAMIENTOS' 74 371 24 $brown $true
  $fontSize=80
  do{$titleLines=@(Lines $payload.plate_title $fontSize 920 $true);if($titleLines.Count -le 4){break};$fontSize-=2}while($fontSize -ge 54)
  if($titleLines.Count -gt 4){throw 'Título excede el bloque'}
  $lineHeight=[int][Math]::Ceiling($fontSize*1.22)
  for($i=0;$i -lt $titleLines.Count;$i++){
    $color=if($i -eq $titleLines.Count-1){$orange}else{$brown}
    Text $titleLines[$i] 70 (439+$lineHeight*$i) $fontSize $color $true
  }
  Rect 74 900 62 6 $orange
  $description='Distintas provincias y generaciones. Homenajes, rescates y registros en vivo.'
  $descriptionLines=@(Lines $description 30 760)
  if($descriptionLines.Count -gt 3){throw 'Bajada excede el bloque'}
  for($i=0;$i -lt $descriptionLines.Count;$i++){Text $descriptionLines[$i] 74 (937+$i*43) 30 $brown}
  # Recurso gráfico propio: surcos de un disco, sin fotos ni portadas de terceros.
  foreach($radius in @(80,108,136)){
    $pen=[System.Drawing.Pen]::new([System.Drawing.ColorTranslator]::FromHtml('#DDB99A'),2)
    try{$g.DrawEllipse($pen,1080-$radius,1034-$radius,$radius*2,$radius*2)}finally{$pen.Dispose()}
    $svg.Add("<circle cx=`"1080`" cy=`"1034`" r=`"$radius`" stroke=`"#DDB99A`" fill=`"none`" stroke-width=`"2`"/>")
  }
  Rect 0 1138 1080 212 $brown
  Rect 74 1180 7 94 $orange
  Text 'LEÉ LA NOTA COMPLETA' 104 1180 29 $cream $true
  Text 'mifolkloreargentino.com' 104 1230 31 $blue
  Text 'VISTA PREVIA · SIN PUBLICAR' 104 1292 15 '#DABFA9'
  $svg.Add('</svg>')
  $bitmap.Save((Join-Path $OutputDirectory 'noticias-v2.png'),[System.Drawing.Imaging.ImageFormat]::Png)
  [System.IO.File]::WriteAllText((Join-Path $OutputDirectory 'noticias-v2.svg'),($svg -join "`n"),[System.Text.UTF8Encoding]::new($false))
  Write-Output "OK: Noticias v2 1080x1350; marca multicolor en una línea; sección 112 px; título $fontSize px / $($titleLines.Count) líneas; sin desbordes horizontales."
}finally{$format.Dispose();$g.Dispose();$bitmap.Dispose()}
