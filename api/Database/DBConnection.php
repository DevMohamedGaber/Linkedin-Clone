<?php
namespace Database;
// Database Singleton Class
class DBConnection
{
  private $dbh = null;
  private static $instance = null;

  private function __construct()
  {
    $config = require(base_path('api/config.php'));

    $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset={$config['DB_CHARSET']}";

    $this->dbh = new \PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORT'], $config['DB_OPTIONS']);
  }
  private static function GetConnection()
  {
    if (self::$instance == null)
    {
      self::$instance = new DBConnection();
    }
    return self::$instance->dbh;
  }

  public static function Query($query)
  {
    $stmt = self::GetConnection()->prepare($query);
    $stmt->execute();

    return $stmt;
  }
}