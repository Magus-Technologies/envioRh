<?php

return [
    'default' => [
        'tipo_documento' => env('EMISOR_DEFAULT_TIPO_DOC', 'DNI'),
        'numero_documento' => env('EMISOR_DEFAULT_DOCUMENTO', ''),
        'nombre' => env('EMISOR_DEFAULT_NOMBRE', ''),
    ],
];
