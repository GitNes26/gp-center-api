<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // 'paths' => ['*', 'sanctum/csrf-cookie'],  #Servidor 
    // 'allowed_origins' => ['*,*'], #Servidor

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Rutas permitidas
    'allowed_methods' => ['*'],                 // Métodos HTTP permitidos
    'allowed_origins' => ['https://gpcenter.gomezpalacio.gob.mx'], // Orígenes permitidos
    'allowed_origins_patterns' => [],           // Patrones de orígenes permitidos
    'allowed_headers' => ['*'],                 // Encabezados permitidos
    'exposed_headers' => [],                    // Encabezados expuestos
    'max_age' => 0,
    'supports_credentials' => false,            // Habilitar cookies en CORS

];
