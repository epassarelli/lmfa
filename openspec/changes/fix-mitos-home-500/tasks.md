## 1. Correccion del controlador

- [x] 1.1 Reemplazar en `MitosController@index` las claves de cache que usan una variable inexistente por claves estables del indice.
- [x] 1.2 Verificar que la home siga cargando `ultimos`, `visitados` y el alfabeto sin cambiar las URLs publicas.

## 2. Correccion de enlaces relacionados

- [x] 2.1 Ajustar el componente lateral de mitos para usar el nombre de ruta publico correcto.

## 3. Validacion

- [x] 3.1 Agregar o actualizar un test feature que cubra la respuesta `200` de `/mitos-y-leyendas-argentinas`.
- [x] 3.2 Ejecutar la validacion disponible y dejar nota de cualquier limitacion del entorno.

Nota de validacion: se intento ejecutar la validacion local, pero la shell actual no dispone de `php` ni expone un `php.exe` resoluble, por lo que no fue posible correr `artisan` o `phpunit` desde este entorno. Queda pendiente validar el test nuevo en un entorno con PHP disponible.
