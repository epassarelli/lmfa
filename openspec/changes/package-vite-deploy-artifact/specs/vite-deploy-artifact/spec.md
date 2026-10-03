## ADDED Requirements

### Requirement: Validación integral del bundle Vite
El proyecto SHALL proporcionar un comando reproducible que valide que `public/build/manifest.json` sea JSON válido, que sus rutas permanezcan dentro de `public/build` y que todos los archivos declarados existan.

#### Scenario: Bundle completo
- **WHEN** el manifiesto es válido y todos los archivos declarados están presentes
- **THEN** el comando termina exitosamente e informa la cantidad de archivos verificados

#### Scenario: Asset faltante
- **WHEN** el manifiesto referencia un archivo que no existe dentro de `public/build`
- **THEN** el comando termina con error e identifica el archivo faltante

#### Scenario: Ruta insegura
- **WHEN** el manifiesto contiene una ruta absoluta o que escapa de `public/build`
- **THEN** el comando termina con error sin leer archivos fuera del bundle

### Requirement: Artefacto descargable de CI
El workflow CI SHALL construir, validar y publicar el directorio completo `public/build` como un único artefacto descargable asociado al commit.

#### Scenario: Build exitoso en CI
- **WHEN** Vite compila y la validación de integridad finaliza correctamente
- **THEN** CI publica un artefacto que contiene el manifiesto y todos los assets referenciados

#### Scenario: Bundle inválido en CI
- **WHEN** la validación detecta un manifiesto o asset inválido
- **THEN** el job falla antes de publicar un artefacto incompleto

### Requirement: Deploy sin Node en producción
La documentación operativa SHALL indicar cómo generar o descargar el bundle, subir `public/build` como unidad y verificar su presencia sin ejecutar NPM en producción.

#### Scenario: Operador prepara un deploy
- **WHEN** el servidor objetivo no dispone de Node/NPM
- **THEN** el operador puede completar el deploy usando el artefacto precompilado y una verificación posterior documentada
