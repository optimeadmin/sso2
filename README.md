# New SSO System

Este repo contiene la funcionalidad para hacer un simple login que intenta evitar los problemas del
uso de iframe y cookies de terceros.

Consta de dos bundles, un bundle como servidor un bundle como cliente.

## Importante

* **Si vas a implementar login del lado del cliente (por ejemplo dentro del iframe)** debes usar el ClientBundle.
* **Si vas a compartir la sesion con un cliente (por ejemplo, tu generas el link del iframe)** debes usar el
  ServerBundle.

## ServerBundle

Este bundle se encarga de permitir pasar la info de la sesión a uno o varios clientes para que estos puedan
obtener e iniciar la sesion del usuario de su lado.

### Instalación

```
composer require "optimeconsulting/sso2" "@dev"
```

### Configuración

Agregar como un bundle en el config/bundles.php:

```php
<?php

return [
    ...
    Optime\Sso\Bundle\Server\OptimeSsoServerBundle::class => ['all' => true],
];
```

#### Configuración de opciones:

Crear/Ajustar el archivo config/packages/optime_sso_server.yaml:

```yaml
optime_sso_server:
  server_code: video_2_market # Codigo unico para el server (el cliente puedo conectarse a varios servers).
  user_data_factory_service: # App\Security\User\SsoUserDataFactory Servicio que genera la data necesaria para la sesión.
  jwt_secret_key: # Clave secreta para JWT, se debe establecer un valor
```

El servicio que se agrega a **user_data_factory_service** debe ser una clase que implemente la interfaz:
`Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface`

#### Agregar rutas:

Crear/Ajustar el archivo config/routes/optime_sso_server.yaml:

```yaml
optime_sso_server:
  resource: "@OptimeSsoServerBundle/Controller/"
  type: attribute
  prefix: /
```

Correr comando de doctrine:

```
symfony console doctrine:schema:update -f
```

### Uso

Para iniciar la sesión, el link que cargará la url del cliente debe generarse de la siguiente forma:

En twig:

```jinja
<iframe 
    id="my_iframe" 
    src="{{ iframe_sso_url('client_code', 'https://client-url/page') }}"
    width='100%'
    rameborder="0"
    scrolling='no'
></iframe>

<script type="module">
  import { initialize } from "{{ asset('bundles/optimessoserver/iframe-resizer.js') }}";

  initialize({}, "#my_iframe");
</script>
```

Recordar incluir el script del iframe resizer (codigo anterior).

Tener en cuenta que la funcion de twig `iframe_sso_url` recibe 2 parametros:

* `clientCode` este es un string que identifica a la app cliente, esto porque pueden haber varios clientes
  y se debe tener una sesion independiente por cliente.
* `regenerateAfter` (opcional, default 10 segundos) este es el tiempo en segundos durante el cual no se
  vuelve a mandar un nuevo token de sesion al cliente una vez se haya iniciado sesión correctamente.

Si se necesita generar la url en el momento que un usuario da click en un link, se debe usar la forma:

```jinja
<a href="{{ generate_sso_url('client_code', 'http://url-cliente.com/test') }}">My Link</a>
```

<hr>

## ClientBundle

Este bundle se encarga de recibir la información para iniciar la sesión en el cliente.

### Instalación

```
composer require "optimeconsulting/sso2" "@dev"
```

### Configuración

Agregar como un bundle en el config/bundles.php:

```php
<?php

return [
    ...
    Optime\Sso\Bundle\Server\OptimeSsoClientBundle::class => ['all' => true],
];
```

Ajustar el security.yaml, agregar el autenticador sso como un custom_authenticator:

```yaml
security:
  providers:
    sso: # Opcional
      id: Optime\Sso\Bundle\Client\Security\User\Provider\SsoUserProvider
  firewalls:
    main:
      # ...
      # provider: sso # Opcional. 
      entry_point: Optime\Sso\Bundle\Client\Security\Authenticator\SsoAuthenticator
      #entry_point: Optime\Sso\Bundle\Client\Security\Authenticator\SsoEntryPoint
      custom_authenticators:
        - Optime\Sso\Bundle\Client\Security\Authenticator\SsoAuthenticator
```

Crear el servicio que creara al usuario a partir de la informacion que viene por sso.
Este servicio es una clase que implementa:

`Optime\Sso\Bundle\Client\Factory\UserFactoryInterface`

Y definir el servicio en config/packages/optime_sso_client.yaml:

```yaml
optime_sso_client:
  user_factory_service: App\Security\SsoUserFactory
  # el local data factory es opcional y permite hacer login localmente sin usar un server sso real.
  # se debe usar el comando symfony console optime:sso:login para obtener un token temporal de inicio de sesion.
  local_data_factory_service: # Implementar \Optime\Sso\Bundle\Client\Security\Local\LocalSsoDataFactoryInterface
  # auto_inject_iframe_resizer: true Por defecto true, indica si se incluye el script de iframe resize automaticamente 
  # local_extra_ip: # Opcional, si se usa docker por ejemplo, colocar la ip de docker para poder hacer login localmente.
  # cookie_partitioned: true # Opcional, por defecto true, determina si las cookies son partitioned o no (iframe).
```

Correr comando de doctrine:

```
symfony console doctrine:schema:update -f
```

### Auto resize del iframe:

**Por defecto el script necesario se agregará en las páginas htmld e forma automática.**

Sin embargo, cuando se configure el `auto_inject_iframe_resizer` en false, se deberá cargar el siguiente script en el cliente:

```jinja
<script type="module" src="{{ asset('bundles/optimessoclient/iframe-resizer.js') }}" async></script>
```

Recordar que en el servidor donde se genera el iframe, se debe incluir lo siguiente luego del iframe:

```jinja
<script type="module">
  import { initialize } from "{{ asset('bundles/optimessoserver/iframe-resizer.js') }}";

  initialize({}, "#<id-del-la-etiqueta-iframe>");
</script>
```

<hr />

### Llamados a api del servidor.

Este bundle permite implementar seguridad para llamados a apis del server desde el cliente sso.

Para ello se debe configurar lo siguiente:

#### Server config

Agregar el SsoApiTokenAuthenticator en el security.yaml del servidor:

```yaml
main:
  provider: app_user_provider
  custom_authenticators:
    - ...
    - Optime\Sso\Bundle\Server\Security\Authenticator\SsoApiTokenAuthenticator
```

Opcionalmente se puede implementar la interfaz `Optime\Sso\Bundle\Server\Security\SsoApiUserProviderInterface`
en la clase UserDataFactory que se ha creado previamente.

Implementar esta interfaz permitirá definir la logica para cargar al usuario en sesion a partir
del identificador que llega desde el cliente.

#### Añadiedo información adicional al token del usuario

Si se necesita añadir información extra a parte de el identificador del usuario, como por ejemplo
el perfil o la empresa con la que inició sesion, la clase UserDataFactory debe implementar la interfaz
`Optime\Sso\Bundle\Server\Security\SsoApiTokenDataProviderInterface`, con esto será posible
retornar la data adicional que va a contener el token.

#### Uso en el cliente

Para hacer uso de llamados a apis en el server se puede usar el servicio:

`Optime\Sso\Bundle\Client\Api\SsoHttpClient` el cual implementa `Symfony\Contracts\HttpClient\HttpClientInterface`

Y tiene los mismos métodos para llamar a endpoints, haciendo automático el envio de headers para autenticar
al usuario en el server al llamar a las apis.

Ejemplo:

```php

use Optime\Sso\Bundle\Client\Api\SsoHttpClient;

class DataProvider
{
    public function __construct(private readonly SsoHttpClient $httpClient)
    {
    }

    public function byUser(User $user): array
    {
        // SsoHttpClient sabe cual es el domino del server de forma automática.
        // Además, establece los headers necesarios para que el servidor autentique al usuario.
        $this->httpClient->request('GET', '/api/data');
    }
}
```

