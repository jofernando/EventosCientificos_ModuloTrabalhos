<?php

return [
    'client' => [
        'id' => env('BANCOBRASIL_CLIENT_ID', ''),
        'secret' => env('BANCOBRASIL_CLIENT_SECRET', ''),
    ],
    'chave_pix' => env('BANCOBRASIL_CHAVE_PIX', ''),
    'gw_dev_app_key' => env('BANCOBRASIL_GW_DEV_APP_KEY', ''),
];
