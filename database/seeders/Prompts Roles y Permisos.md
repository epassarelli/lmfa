Necesito que me ayudes a crear un sistema de roles y permisos a determinadas acciones en el backend de mi gestion de contenidos. Tengo interpretes, noticias, canciones y quiero que dependiendo del Rol que tenga el usuario pueda o no realizar ciertas acciones.
Por ejemplo, un Editor no deberia poder eliminar contenidos.
UN Rol autor no deberia poder eliminar ni editar y un Rol Colaborador sólo podria agregar contenidos y con un estado=0.
Mi proyecto es con Lravel 10, UI, Boostrap, AdminLTE3, Vite y hoy tengo usaurios registradospero no un sistema de rolesy permisos. Me ayudas a implementarlo? Necesito que me ayudes a instalar lo necesario y luego requiero ejemlos puntuales de como usarlo.
En principio vayamos solo por la instalacion y configuracion de lo necesario y luego te voy pidiendo mas cosas.


Bien, ahora necesito que me pases el seeder RolesAndPermissionsSeeder  con las siguientes consideraciones.

En mi sistema los roles serán Administrador, Prensa y Colaborador.
El adminsitrador puede hacer todo y con todo me refiero a las 4 acciones de un crud. Mis entidades en cuestion son user, interprete, noticia, show, cancion, album, festival, mito y comida.

Alguien con rol Prensa puede de cada CRUD hacer lo siguiente:
Interpretes C R
Noticias C R U
Shows C R U
Album C R U
Cancion C R U
Festival C R 
Mito C R 
Comida   C R 

Alguien con rol Colaborador puede de cada CRUD hacer lo siguiente:
Interpretes C R
Noticias C R 
Shows C R 
Album C R 
Cancion C R 
Festival C R 
Mito C R 
Comida   C R 
Dime si se entiende y de ser así necesito que me pases el codigo para el seeder.