Hush ChatBundle
=============

ChatBundle расширяет функционал symfony, добавляя возможность пользователям обмениваться сообщениями.

Зависимости:
 - [FosUserBundle](https://symfony.com/doc/master/bundles/FOSUserBundle/index.html)
 - [Sonata Media Bundle][https://sonata-project.org/bundles/media/master/doc/index.html]
 - [NelmioApiDocBundle][https://github.com/nelmio/NelmioApiDocBundle]

Возможности:
- ChatService реализует сервис - функции для обмена сообщениями. В том числе добавлять медиафайлы (картинки) к сообщениям.
- Rest API для доступа к чат-функциям.

Установка
------------

Предполагается что FosUserBundle и Sonata Media Bundle уже установлены.

Добавляем бандл через composer:

```sh
composer require hush\chat-bundle
```

Регистрируем бандл в приложение:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new Hush\ChatBundle\ChatBundle(),
        // ...
    );
}
```

Настраиваем config.yml:

```yml
doctrine:
    orm:
        resolve_target_entities:
            FOS\UserBundle\Model\UserInterface: AppBundle\Entity\User
```

Вместо AppBundle\Entity\User необходимо указать ваш конкретный класс, расширяющий базовую модель FOSUserBundle.
Это тот же класс, который указан в конфиге как fos_user.user_class.

После этого необходимо расширить структуру БД командой:

```sh
php app/console doctrine:schema:update --force
```

Настройка
---------------------------------------
Необходимо настроить дополнительный контекст для Media Bundle для сохранения изображений в сообщениях.
config.yml
```yml
sonata_media:
    contexts:
        message:
            providers:
                - sonata.media.provider.image
            formats:
                small: { width: 150 , quality: 95}
                big:   { width: 500 , quality: 90}
    providers:
        image:
          allowed_extensions: ['jpg', 'png', 'gif', 'jpeg']
          allowed_mime_types: ['image/pjpeg','image/jpeg','image/png','image/x-png', 'image/gif']
```
Для контекста message можно настроить несколько форматов, в примере настроено 2: small и big.
После этого необходимо зафиксировать новый контекст в БД:
```php
php app/console sonata:media:fix-media-context
```
Использование
---------------------------------------

Возможно 2 варианта использования бандла:
- Прямое использование функций чат-сервиса
- Использование Rest API бандла

Использование функций чат - сервиса
---------------------------------------
Регистрируем сервис в services.yml
```yml
chat:
    class      Hush\ChatBundle\Service\ChatService
    arguments:    ["@service_container"]
```

Использование:
```php
$container->get('chat')->someMethod($arguments)
```
Список методов и их описание смотрите в классе сервиса Hush\ChatBundle\Service\ChatService

Использование Rest API
---------------------------------------
Регистрируем контроллер в routing.yml:
```yml
chat:
    resource: "@ChatBundle/Resources/config/routing.yml"
```

Закрываем доступ неавторизованным пользователям в security.yml:
```yml
security:
    access_control:
        - { path: ^/messages, roles: ROLE_USER }
```

Также необходимо реализовать метод аутентификации пользователя.
Например по определённому [apikey][http://symfony.com/doc/current/security/api_key_authentication.html] пользователя.

