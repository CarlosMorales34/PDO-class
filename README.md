# Clase PDO para Conexión a Base de Datos

Esta clase PHP proporciona una interfaz sencilla para conectarse a una base de datos utilizando PDO (PHP Data Objects). Es ideal para proyectos que requieren una conexión a bases de datos MySQL o MariaDB.

## Descripción

La clase `DB` maneja la conexión a una base de datos y proporciona un método para la depuración. Los parámetros de conexión se pueden ajustar según sea necesario, y el host por defecto es `localhost`.

## Instalación

1. **Clona el repositorio:**

    ```bash
    git clone git@github.com:CarlosMorales34/PDO-class.git
    ```

2. **Incluye la clase en tu proyecto:**

    Asegúrate de que el archivo `class.db.php` esté en la ruta correcta o ajusta la ruta en el siguiente `require`:

    ```php
    require 'ruta/a/src/class.db.php';
    ```

## Uso

Para usar la clase, instánciala con los parámetros necesarios:

```php
require 'ruta/a/class.db.php';

// Configura desde class.db.php los accesos para la clase
en getInstance
cambia lo valores:
$host
$db
$user
$pass
para acceder a tu base de datos y usar los metodos de la clase.

// Método para comprobar la conexión y depurar
$db->debug();
Revisa en errors.log los error
Deberás cambiar
// Debug
    private bool $debug = false;
en class.db.php a 
// Debug
    private bool $debug = true;



