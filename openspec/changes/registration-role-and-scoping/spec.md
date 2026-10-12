# Spec: registration-role-and-scoping + Mi panel

Estado: implementada en local. Aprobación: explícita del usuario ("Ok con las 2 cosas y luego el C").

## Contexto
- El registro no asigna ningún rol (confirmado: `RegisterController::create`, sin listener de `Registered`).
- Un usuario sin rol veía los listados completos de Festivales, Mitos, Comidas e Intérpretes (el filtro "solo lo mío" dependía de `hasRole('colaborador')`). Solo lectura; la edición ya estaba protegida.
- Con el flag `BACKOFFICE_ACCESS_BY_ENTITIES` activo, un colaborador con una ficha podía dar de alta artistas/festivales directo, salteando "Solicitar alta".

## Alcance
1. Sin rol automático al registrarse (se documenta con test). El acceso sale de las fichas (membresía), que ya habilita discos, canciones y eventos sin rol.
2. Listados de Festivales, Mitos, Comidas e Intérpretes acotados a lo propio para todo no administrador (Intérpretes mantiene el listado completo para colaboradores con el flag apagado: comportamiento heredado).
3. Con el flag activo, alta directa de artistas y festivales solo para administrador y `verificado` (`User::canCreateEntityDirectly`). Sin flag, rige el rol habitual. Las secciones `interpretes` y `festivales` suman el rol `verificado`.
4. "Mi panel": el dashboard de todo no administrador (con o sin flag) muestra sus fichas, solicitudes (y las que debe revisar), contenido cargado y, si no tiene fichas, una invitación a solicitar una. Reemplaza el redirect a Mis fichas.

## Archivos
Controllers Festival/Comida/Mito/Interprete/Dashboard, políticas Festival e Interprete, trait `ManagesOrganizationEntities`, `config/backoffice_access.php`, vista `dashboard-member`, tests `RegistrationRoleAndScopingTest` y `ContributionFlowRemovedTest`.

## Fuera de alcance
Asignar rol al aprobar una ficha; alta directa de Mitos/Comidas; migraciones (ninguna).

## Resultado
mfa_test: 661 passed, 2 skipped.
