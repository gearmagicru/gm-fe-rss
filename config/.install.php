<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации установки модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'use'         => FRONTEND,
    'id'          => 'gm.fe.rss',
    'name'        => 'Publishing RSS feeds',
    'description' => 'Publishing your site\'s RSS feeds',
    'namespace'   => 'Gm\Frontend\Rss',
    'path'        => '/gm/gm.fe.rss',
    'route'       => 'feed', // использует BACKEND
    'routes'      => [
        [
            'use'     => FRONTEND,
            'type'    => 'parts',
            'options' => [
                'module' => 'gm.fe.rss',
                'route'  => 'feed',
                'size'   => 2,
                'assign' => ['slug' => 1]
            ]
        ],
        [
            'use'     => BACKEND,
            'type'    => 'crudSegments',
            'options' => [
                'module' => 'gm.fe.rss',
                'route'  => 'feed',
                'prefix' => BACKEND
            ]
        ]
    ],
    'locales'     => ['ru_RU', 'en_GB'],
    'permissions' => ['info'],
    'events'      => [],
    'required'    => [
        ['php', 'version' => '8.2'],
        ['app', 'code' => 'GM CMS']
    ]
];
