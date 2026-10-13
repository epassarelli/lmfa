<?php

return [
    // Tope de correos del newsletter por minuto. Es un valor de ajuste (no un interruptor): los proveedores de
    // correo limitan los envíos por minuto/hora y superarlos genera rechazos temporales. Si un proveedor permite
    // más, se sube; si es más estricto (hosting compartido), se baja. Los envíos que exceden el tope no se
    // pierden: vuelven a la cola y salen en el minuto siguiente.
    'max_per_minute' => (int) env('NEWSLETTER_MAX_PER_MINUTE', 60),
];
