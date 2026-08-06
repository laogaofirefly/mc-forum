<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'minecraft' => [
        'host' => env('MINECRAFT_SERVER_HOST', 'localhost'),
        'port' => env('MINECRAFT_SERVER_PORT', 25565),
        'log_path' => env('MC_SERVER_PATH', ''),
        'start_command' => env('MC_START_COMMAND', ''),
        'java_path' => env('MC_JAVA_PATH', 'java'),
        'java_xms' => env('MC_JAVA_XMS', '1G'),
        'java_xmx' => env('MC_JAVA_XMX', '4G'),
        'stop_command' => env('MC_STOP_COMMAND', 'stop'),
        'auto_restart' => env('MC_AUTO_RESTART', false),
        'backup_path' => env('MC_BACKUP_PATH', ''),
        'query_port' => env('MC_QUERY_PORT', 25565),
        // Dynmap 对外访问地址，例如 https://map.example.com 或 http://127.0.0.1:8123
        'dynmap_url' => env('DYNMAP_URL', ''),
        // 可选：Dynmap 的 web 输出目录；留空时自动使用 MC_SERVER_PATH/plugins/dynmap/web。
        'dynmap_web_path' => env('DYNMAP_WEB_PATH', ''),
        'rcon' => [
            'host' => env('MC_RCON_HOST', '127.0.0.1'),
            'port' => (int) env('MC_RCON_PORT', 25575),
            'password' => env('MC_RCON_PASSWORD', ''),
        ],
    ],

];
