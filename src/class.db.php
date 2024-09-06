<?php

/**
 * Clase PHP PDO Database
 *
 * Esta clase implementa el patrón Singleton para manejar la conexión a una base de datos MySQL utilizando PDO.
 * La clase proporciona métodos para ejecutar consultas SQL y verificar el estado de la conexión.
 * 
 * Esta clase asegura que solo exista una instancia de la conexión a la base de datos en toda la aplicación,
 * lo que ayuda a evitar múltiples conexiones innecesarias y simplifica la gestión de la conexión.
 * 
 * @link              https://github.com/CarlosMorales34/PDO-class
 * @version           1.0.0
 *
 * Description:       Un envoltorio de base de datos PDO para PHP con implementación Singleton.
 * Last Update:       2024-09-06
 * Author:            Carlos Morales
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 */

class DB{
    private static ?DB  $instance = null; //Instancia Singleton de la clase DB
    private        ?PDO $conn     = null; //Objeto PDO para la conexión a la base de datos
    private string $host;                //Host del servidor de la base de datos
    private string $db;                  //Nombre de la base de datos
    private string $user;                 //Usuario de la base de datos
    private string $pass;                //Contraseña de la base de datos
    
    // Debug
    private bool $debug = false;
    

    /**
     * Constructor privados para implementar el patrón Singleton
     * 
     * @param string $host El host del servidor de la base de datos
     * @param string $db   El nombre de la base de datos
     * @param string $user El usuario de la base de datos
     * @param string $pass La contraseña para el usuario de la base de datos
     */
    private function __construct(string $host, string $db, string $user, string $pass)
    {
        $this->host = $host;
        $this->db   = $db;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * Obtener la instancia del Singleton de la clase DB.
     * 
     * Este método proporciona una instancia única de la clase `DB`, asegurando que solo exista una instancia 
     * de la conexión a la base de datos en todo el sistema (patrón Singleton). Si la instancia no existe, 
     * se crea una nueva utilizando los parámetros proporcionados. Si ya existe una instancia, se devuelve la existente.
     * 
     * @example
     * // Obtener la instancia única de la clase DB
     * $db = DB::getInstance(); 
     * 
     * @param string $host (Opcional) El host del servidor de la base de datos. Por defecto es 'localhost'.
     * @param string $db    Nombre de la base de datos.
     * @param string $user  Usuario de la base de datos.
     * @param string $pass  Contraseña del usuario de la base de datos. 
     * 
     * @return DB          La instancia única de la clase `DB`.
     */
    public static function getInstance(string $host = 'localhost', string $db = 'dbname', string $user = 'user', string $pass = 'password'): DB
    {
        if (self::$instance === null) {
            self::$instance = new self($host, $db, $user, $pass);
        }
        return self::$instance;
    }


    /**
     * Obtener la conexión a la base de datos.
     * 
     * Este método se encarga de crear una nueva conexión PDO a la base de datos si aún no existe. Si la conexión ya está
     * establecida, simplemente la devuelve. La conexión se configura para lanzar excepciones en caso de error, utilizando
     * el modo de error `PDO::ERRMODE_EXCEPTION`.
     * 
     * @return PDO       La conexión PDO a la base de datos. Si la conexión aún no se ha creado, se crea en este momento.
     * 
     * @throws Exception Lanza una excepción si ocurre un error al intentar conectar a la base de datos. La excepción
     *                   contiene el mensaje de error proporcionado por PDO en caso de fallo de conexión.
     */
    private function getConnection(): PDO 
    {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db}";
                $this->conn = new PDO($dsn, $this->user, $this->pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $this->logError("Error al conectarse a la base de datos: " . $e->getMessage());
                throw new Exception("Error al conectarse a la base de datos: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    private function logError(string $message): void
    {
        if($this->debug){
            error_log($message, 3, 'errors.log');
        }
    }


    /**
     * Verificar si la conexión a la base de datos está activa.
     * 
     * Este método realiza una consulta simple para comprobar si la conexión a la base de datos está activa.
     * La consulta ejecutada es una consulta SELECT básica que siempre debe devolver un resultado si la conexión es válida.
     * 
     * @example
     * // Verificar si la conexión a la base de datos está activa.
     * if ($db->isConnected()) {
     *     echo "Conectado a la base de datos.";
     * } else {
     *     echo "No se pudo conectar a la base de datos.";
     * }
     * 
     * @return bool Devuelve true si la conexión está activa y la consulta se ejecuta correctamente, false en caso contrario.
     */
    public function isConnected(): bool
    {
        try {
            $stmt = $this->getConnection()->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            $this->logError("Error en la verificación de conexión: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Ejecutar una consulta SELECT en la base de datos.
     * 
     * Este método ejecuta una consulta SELECT para recuperar datos de la base de datos. La consulta SQL 
     * debe ser proporcionada como una cadena, y los parámetros opcionales pueden ser pasados como un array 
     * asociativo para reemplazar los marcadores de posición en la consulta.
     * 
     * @param string $sql    La consulta SQL a ejecutar. Puede contener marcadores de posición para parámetros.
     * @param array  $params (Opcional) Un array asociativo donde las claves son los nombres de los parámetros
     *                       en la consulta SQL (para consultas preparadas con marcadores de posición con nombre) 
     *                       o los valores para los marcadores de posición numerados (para consultas con `?`).
     *                       Ejemplo: ['id' => 5, 'nombre' => 'Epack'].
     * 
     * @return array         Devuelve los resultados de la consulta como un array asociativo. Cada elemento del
     *                       array representa una fila de la tabla, con las columnas como claves y los valores de
     *                       las columnas como valores.
     * 
     * @example
     * // Ejecutar una consulta SELECT para obtener todos los registros de la tabla 'users'.
     * $sql = "SELECT * FROM users";
     * 
     * // Sin parámetros.
     * $results = $db->query($sql);
     * print_r($results);
     * 
     * @example
     * // Ejecutar una consulta SELECT con parámetros para filtrar los resultados en la tabla 'cliente'.
     * $sql = "SELECT * FROM cliente WHERE id = :id AND nombre = :nombre";
     * $params = ['id' => 5, 'nombre' => 'Epack'];
     * $results = $db->query($sql, $params);
     * print_r($results);
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError("Error en la consulta: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Ejecutar una consulta INSERT, UPDATE o DELETE en la base de datos.
     * 
     * Este método permite ejecutar consultas SQL que modifican la base de datos, como INSERT, UPDATE o DELETE.
     * La consulta se debe proporcionar como una cadena SQL, y los parámetros de la consulta deben ser
     * proporcionados en un array. Estos parámetros se utilizarán para reemplazar los marcadores de posición
     * en la consulta SQL.
     * 
     * @param string $sql   La consulta SQL a ejecutar. Debe contener marcadores de posición (?) para los parámetros.
     * @param array  $params (Opcional) Un array de parámetros que se usarán para reemplazar los marcadores de posición
     *                       en la consulta SQL. El número y el orden de los parámetros deben coincidir con los
     *                       marcadores de posición en la consulta SQL. Ejemplo: ['5', '35', '5', $descripcion, $today].
     * 
     * @return bool         Devuelve true si la consulta se ejecutó correctamente, false en caso contrario.
     * 
     * @example
     * // Ejecutar una consulta INSERT en la tabla 'tabla' con cinco valores.
     * $sql = "INSERT INTO tabla (column1, column2, column3, column4, column5) VALUES (?, ?, ?, ?, ?)";
     * $params = ['5', '35', '5', $descripcion, $today];
     * $success = $db->execute($sql, $params);
     * 
     * @example
     * // Ejecutar una consulta UPDATE para modificar el campo 'descripcion' en la tabla 'tabla' donde 'id' es 1.
     * $sql = "UPDATE tabla SET descripcion = ? WHERE id = ?";
     * $params = [$nuevaDescripcion, 1];
     * $success = $db->execute($sql, $params);
     * 
     * @example
     * // Ejecutar una consulta DELETE para eliminar registros de la tabla 'tabla' donde 'fecha' es menor a la fecha actual.
     * $sql = "DELETE FROM tabla WHERE fecha < ?";
     * $params = [$fechaActual];
     * $success = $db->execute($sql, $params);
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError("Error en la ejecución de consulta: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Insertar un nuevo registro en la base de datos.
     * 
     * Este método ejecuta una consulta INSERT para agregar un nuevo registro a una tabla específica. 
     * Los datos a insertar deben ser proporcionados como un array asociativo, donde las claves representan
     * los nombres de las columnas y los valores representan los valores a insertar en esas columnas.
     * 
     * @param string $table La tabla en la que se insertará el nuevo registro.
     * @param array  $data  Un array asociativo donde las claves son los nombres de las columnas y los valores
     *                       son los datos a insertar en esas columnas. Ejemplo: ['name' => 'John Doe', 'email' => 'john@gmail.com'].
     * 
     * @return bool         Devuelve true si la consulta INSERT se ejecutó correctamente, false en caso contrario.
     * 
     * @example
     * // Insertar un nuevo registro en la tabla 'users' con el nombre 'John Doe' y el email 'john@gmail.com'.
     * $data = ['name' => 'John Doe', 'email' => 'john@gmail.com'];
     * $db->insert('users', $data);
     */
    public function insert(string $table, array $data = []): bool 
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        return $this->execute($sql, array_values($data));
    }


    /**
     * Leer los registros de la base de datos (con opción de filtrar).
     * 
     * Este método ejecuta una consulta SELECT para recuperar registros de una tabla específica. 
     * Si se proporcionan condiciones, se añadirán a la cláusula WHERE para filtrar los registros. 
     * Si no se proporcionan condiciones, se recuperarán todos los registros de la tabla.
     * 
     * @param string $table     El nombre de la tabla de la cual se leerán los registros.
     * @param array  $conditions (Opcional) Un array asociativo donde las claves representan los nombres de las columnas
     *                           y los valores son los valores a comparar en la cláusula WHERE. Si se omite, se
     *                           recuperarán todos los registros.
     *                           Ejemplo: ['id' => 1].
     * 
     * @return array            Devuelve un array asociativo con los resultados de la consulta. Cada elemento del array
     *                           representa una fila de la tabla, con las columnas como claves y los valores de las columnas como valores.
     * 
     * @example
     * // Recuperar todos los registros de la tabla 'users'.
     * $results = $db->read('users');
     * 
     * @example
     * // Recuperar registros de la tabla 'users' donde 'id' es igual a 1.
     * $conditions = ['id' => 1];
     * $results = $db->read('users', $conditions);
     */
    public function read(string $table, array $conditions = []): array {
        $sql = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($conditions)));
        }
        return $this->query($sql, array_values($conditions));
    }


    /**
     * Actualizar registros en la base de datos.
     * 
     * Este método ejecuta una consulta UPDATE para modificar registros en una tabla específica. 
     * Los datos a actualizar deben ser proporcionados como un array asociativo, donde las claves son
     * los nombres de las columnas y los valores son los nuevos valores que se establecerán. Las condiciones
     * deben ser pasadas como un array asociativo que especifica qué registros deben ser actualizados.
     * 
     * @param string $table      La tabla en la que se realizarán las actualizaciones.
     * @param array  $data       Un array asociativo donde las claves representan los nombres de las columnas
     *                           a actualizar y los valores representan los nuevos valores a establecer.
     *                           Ejemplo: ['email' => 'john.doe@gmail.com'].
     * @param array  $conditions Un array asociativo donde las claves representan los nombres de las columnas
     *                           que se utilizarán en la cláusula WHERE para seleccionar los registros a actualizar,
     *                           y los valores representan los valores para esas columnas.
     *                           Ejemplo: ['id' => 1].
     * 
     * @return bool Devuelve true si la consulta UPDATE se ejecutó correctamente, false en caso contrario.
     * 
     * @example
     * // Actualizar el campo 'email' en la tabla 'users' para el registro con 'id' igual a 1.
     * $updateData = ['email' => 'john.doe@gmail.com'];
     * $conditions = ['id' => 1];
     * $db->update('users', $updateData, $conditions);
     * 
     * @example
     * // Cambiar el campo 'status' a 'activo' en la tabla 'products' para todos los registros con 'category' igual a 'electronics'.
     * $updateData = ['status' => 'activo'];
     * $conditions = ['category' => 'electronics'];
     * $db->update('products', $updateData, $conditions);
     */
    public function update(string $table, array $data, array $conditions): bool {
        $setClause = implode(", ", array_map(fn($col)=>"$col = ?", array_keys($data)));
        $whereClause = implode(" AND ", array_map(fn($col)=>"$col = ?", array_keys($conditions)));
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        return $this->execute($sql, array_merge(array_values($data), array_values($conditions)));
    }


    /**
     * Borrar registros de una tabla específica.
     * 
     * Este método ejecuta una consulta DELETE para eliminar registros de una tabla según las condiciones
     * proporcionadas. Las condiciones deben ser pasadas como un array asociativo, donde las claves son
     * los nombres de las columnas y los valores son los valores a comparar en la cláusula WHERE.
     * 
     * @param string $table      El nombre de la tabla de la que se eliminarán los registros.
     * @param array  $conditions Un array asociativo donde las claves representan los nombres de las columnas
     *                           y los valores representan los valores a comparar en la cláusula WHERE.
     *                           Ejemplo: ['column1' => 'value1', 'column2' => 'value2'].
     * 
     * @return bool Devuelve true si la consulta DELETE se ejecutó correctamente, false en caso contrario.
     * 
     * @example
     * // Eliminar registros de la tabla 'usuarios' donde 'id' es igual a 5 y 'activo' es igual a 1.
     * $db->delete('usuarios', ['id' => 5, 'activo' => 1]);
     * 
     * @example
     * // Eliminar todos los registros de la tabla 'productos' donde 'categoria' es 'electrónica'.
     * $db->delete('productos', ['categoria' => 'electrónica']);
     */
    public function delete(string $table, array $conditions = []): bool
    {
        $whereClause = implode(" AND ", array_map(fn($col)=> "$col = ?", array_keys($conditions)));
        $sql = "DELETE FROM $table WHERE $whereClause";
        return $this->execute($sql, array_values($conditions));
    }





}
