<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contacto</title>
</head>

<body>
    <h3>Datos del contacto</h3>
    <p><strong>Nombre: </strong>{{ $contact['name'] }}</p>
    <p><strong>Apellido: </strong>{{ $contact['lastName'] }}</p>
    <p><strong>Asunto: </strong>{{ $contact['issue'] }}</p>
    <p><strong>Email: </strong>{{ $contact['email'] }}</p>
    <p><strong>Teléfono: </strong>{{ $contact['phone'] }}</p>
    <p><strong>Empresa: </strong>{{ $contact['company'] }}</p>
    <p><strong>Mensaje: </strong>{{ $contact['message'] }}</p>
</body>

</html>
