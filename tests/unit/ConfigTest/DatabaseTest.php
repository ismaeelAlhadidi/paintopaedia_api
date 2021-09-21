<?php 

use PHPUnit\Framework\TestCase;
use App\Config\Database;

class DatabaseTest extends TestCase {
    
    private $database;

    protected function setUp() : void {
        $this->database = new Database();
    }

    public function test_connection_with_database() {

        $this->assertInstanceOf(Database::class, $this->database);

        $connection = $this->database->getConnection();

        $this->assertInstanceOf(PDO::class, $connection);
    }

    public function test_if_migration_and_tables_exists() {

        $migration = __DIR__ . "/../../../database/migration.php";

        $databaseCreateTablesCode = include_once $migration;
        
        $this->assertIsArray($databaseCreateTablesCode);

        foreach($databaseCreateTablesCode as $tableName => $createCode) {

            try {

                $connection = $this->database->getConnection();
                $prepare = $connection->prepare("SELECT 1 FROM $tableName LIMIT 1");
                $result = $prepare->execute();
                
            } catch (PDOException $e) {
                
                $result = false;
            }

            $this->assertNotEquals($result, false);
        }

    }

}