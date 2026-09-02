# Checklist de release — Discografía y Cancionero

## Alcance

Modernización compatible de Album y Cancion.

Incluye:
- metadatos editoriales y SEO de álbumes;
- créditos y estado de derechos de canciones;
- letra nullable;
- eliminación de placeholders nuevos;
- API moderna;
- backoffice;
- frontend capaz de mostrar fichas sin letra;
- auditor read-only.

No incluye:
- borrado o reemplazo masivo de letras legacy;
- automatización de incorporación de letras completas;
- cambio de URLs públicas;
- nueva entidad Work/Recording separada en esta etapa.

## Migración

2026_09_01_000500_modernize_discography_and_songs.php

La migración agrega campos opcionales y vuelve canciones.letra nullable.

Rollback:
- elimina campos nuevos;
- deliberadamente no vuelve letra a NOT NULL porque podrían existir fichas legítimas creadas sin letra después del release.

## Smoke API

- POST/PUT albums
- POST/PUT songs
- song sin letra -> 201
- duplicate slug por intérprete -> 422
- album_ids sincronizados
- instrumental -> letra null
- defaults de autor/estado
- imagen de álbum vía media transversal

## Smoke backoffice

Álbum:
- tipo, año, fecha, sello, resumen, SEO, portada, tracklist

Canción:
- compositor, autor de letra, derechos, fuente, instrumental, letra opcional, SEO

Alta rápida desde álbum:
- nunca crea "No disponible aun"
- crea letra null + rights_status not_available

## Smoke frontend

- álbum con tracklist
- canción con letra
- canción sin letra
- instrumental
- títulos SEO no prometen letra cuando no existe
- MusicRecording schema grounded

## Auditor

php artisan mfa:music:audit --active

Revisar:
- placeholders legacy
- letras con derechos no verificados
- álbumes/canciones P1

## Gate

No desplegar hasta:
- CI verde
- migración revisada
- smoke local
- auditor validado
- política de letras aceptada como "no automatizar contenido completo sin derechos verificables".
