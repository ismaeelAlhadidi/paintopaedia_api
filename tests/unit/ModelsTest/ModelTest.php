<?php 

use PHPUnit\Framework\TestCase;
use App\Models\Model;
use App\Interfaces\ModelInterFace;
use App\Config\Database;
use App\Helpers\Paginator;
use App\Exceptions\ModelException;

include_once ('Traits/HelperMethods.php');
include_once ('Traits/GeneraterMethods.php');

include_once ('FakesModels/myModel.php');
include_once ('FakesModels/myModelWithFillable.php');

class ModelTest extends TestCase {

    use HelperMethods, GeneraterMethods;

    public static function setUpBeforeClass() : void {

        $database = new Database();

        ModelTest::$connection = $database->getConnection();

        try {

            $prepare = ModelTest::$connection->prepare ("
                CREATE TABLE IF NOT EXISTS table_for_test_model (
                    id integer,
                    first_name VARCHAR(25), 
                    last_name VARCHAR(25),
                    username text,
                    phone char(10),
                    email VARCHAR(255), 
                    profile_pictuer text DEFAULT NULL,
                    PRIMARY KEY (id)
                );
            ");
            
            $prepare->execute();

        } catch (PDOException $e) {

        }
    }

    public static function tearDownAfterClass() : void {
        
        try {

            $prepare = ModelTest::$connection->prepare ("
                TRUNCATE TABLE table_for_test_model;
            ");
            
            $prepare->execute();

        } catch (PDOException $e) {

        }

        ModelTest::$connection = null;
    }

    public function test_if_has_need_protected_attribute() {

        $this->assertClassHasAttribute('table', Model::class);

        $this->assertClassHasAttribute('fillable', Model::class);

        $this->assertClassHasAttribute('hidden', Model::class);

        $this->assertClassHasAttribute('primaryKey', Model::class);

        $this->assertClassHasStaticAttribute('connection', Model::class);
        
    }

    public function test_set_static_connection_when_create_instance() {

        $model = new myModel();

        $this->assertInstanceOf(PDO::class, $model->getStaticConnectionForTest());

    }

    public function test_default_attributes_values() {
        $model = new myModel();

        $this->assertEquals($model->getMyPrimaryKey(), 'id');
    }


    public function test_magic_methods_set_and_get() {
        $model = new myModel();

        $this->assertEquals($model->esmaeel, null);
        $this->assertEquals($model->x, null);
        $this->assertEquals($model->z, null);
        $this->assertEquals($model->null, null);

        $model->esmaeel = "not null";
        $model->x = 123;
        $model->z = 12.8;
        $model->null = array(
            'item1', 'item2', 'item3'
        );

        $this->assertEquals($model->esmaeel, "not null");
        $this->assertEquals($model->x, 123);
        $this->assertEquals($model->z, 12.8);

        $this->assertCount(3, $model->null);
        $this->assertContains('item1', $model->null);
        $this->assertContains('item2', $model->null);
        $this->assertContains('item3', $model->null);

        $this->assertEquals($model->temp, null);

        $model->temp = "temp value";
        $this->assertEquals($model->temp, "temp value");
    }

    public function test_array_access() {
        $model = new myModel();

        $this->assertEquals($model['esmaeel'], null);
        $this->assertEquals($model['x'], null);
        $this->assertEquals($model['z'], null);
        $this->assertEquals($model['null'], null);

        $model->esmaeel = "not null";
        $model['x'] = 123;
        $model['z'] = 12.8;
        $model->null = array(
            'item1', 'item2', 'item3'
        );

        $this->assertTrue(isset($model['esmaeel']));
        $this->assertTrue(isset($model['x']));
        $this->assertTrue(isset($model['z']));
        $this->assertTrue(isset($model['null']));
        $this->assertTrue(! isset($model['table']));

        $this->assertEquals($model['esmaeel'], "not null");
        $this->assertEquals($model['x'], 123);
        $this->assertEquals($model['z'], 12.8);

        $this->assertEquals($model['esmaeel'], $model->esmaeel);
        $this->assertEquals($model['x'], $model->x);
        $this->assertEquals($model['z'], $model->z);

        $this->assertCount(3, $model['null']);
        $this->assertContains('item1', $model['null']);
        $this->assertContains('item2', $model['null']);
        $this->assertContains('item3', $model['null']);
    }

    public function test_if_json_serializable() {

        $model = new myModel();

        $model->esmaeel = "not null";
        $model['x'] = 123;
        $model['z'] = 12.8;
        $model->null = array(
            'item1', 'item2', 'item3'
        );

        $arrayFromModel = json_decode(json_encode($model), true);

        $this->assertTrue(isset($arrayFromModel['esmaeel']));
        $this->assertTrue(isset($arrayFromModel['x']));
        $this->assertTrue(isset($arrayFromModel['z']));
        $this->assertTrue(isset($arrayFromModel['null']));
        $this->assertTrue(! isset($arrayFromModel['table']));

        $this->assertEquals($arrayFromModel['esmaeel'], "not null");
        $this->assertEquals($arrayFromModel['x'], 123);
        $this->assertEquals($model['z'], 12.8);

        $this->assertEquals($arrayFromModel['esmaeel'], $model->esmaeel);
        $this->assertEquals($arrayFromModel['x'], $model->x);
        $this->assertEquals($arrayFromModel['z'], $model->z);

        $this->assertCount(3, $arrayFromModel['null']);
        $this->assertContains('item1', $arrayFromModel['null']);
        $this->assertContains('item2', $arrayFromModel['null']);
        $this->assertContains('item3', $arrayFromModel['null']);

        $arrayFromModel = $model->jsonSerialize();

        $this->assertTrue(isset($arrayFromModel['esmaeel']));
        $this->assertTrue(isset($arrayFromModel['x']));
        $this->assertTrue(isset($arrayFromModel['z']));
        $this->assertTrue(isset($arrayFromModel['null']));
        $this->assertTrue(! isset($arrayFromModel['table']));

        $this->assertEquals($arrayFromModel['esmaeel'], "not null");
        $this->assertEquals($arrayFromModel['x'], 123);
        $this->assertEquals($model['z'], 12.8);

        $this->assertEquals($arrayFromModel['esmaeel'], $model->esmaeel);
        $this->assertEquals($arrayFromModel['x'], $model->x);
        $this->assertEquals($arrayFromModel['z'], $model->z);

        $this->assertCount(3, $arrayFromModel['null']);
        $this->assertContains('item1', $arrayFromModel['null']);
        $this->assertContains('item2', $arrayFromModel['null']);
        $this->assertContains('item3', $arrayFromModel['null']);
        
    }

    public function test_static_create_method() {

        $dataForRow = $this->rows[0];

        $dataForRowWithFillable = $this->rows[1];

        $newRow = myModel::create($dataForRow);

        $newRowWithFillable = myModelWithFillable::create($dataForRowWithFillable);

        $this->assertInstanceOf(myModel::class, $newRow);

        $this->assertInstanceOf(myModelWithFillable::class, $newRowWithFillable);

        try {

            $prepare = ModelTest::$connection->prepare (
                "select * from " . $newRow->get_table() . 

                " where id='" . $dataForRow['id'] . 
                "' or id='" . $dataForRowWithFillable['id'] . "';"
            );
            
            $prepare->execute();

            $rows = $prepare->fetchAll();

        } catch (PDOException $e) {
            $this->fail("error in fetch data");
        }

        $this->assertCount(2, $rows);

        $row = $rows[0];
        $rowWithFillable = $rows[1];

        if($rows[0]['id'] == $dataForRowWithFillable['id']) {
            $row = $rows[1];
            $rowWithFillable = $rows[0];
        }


        foreach($dataForRow as $key => $value) {

            $this->assertEquals($row[$key], $value);
        }

        $fillable = $newRowWithFillable->getFillable();

        foreach($dataForRowWithFillable as $key => $value) {

            if(in_array($key, $fillable)) {
                $this->assertEquals($rowWithFillable[$key], $value);
                continue;
            }

            $this->assertNotEquals($rowWithFillable[$key], $value);
        }

        $this->assertTrue($this->rowEquals($newRow->get(), $row));

        $this->assertTrue($this->rowEquals($newRowWithFillable->get(), $rowWithFillable));
    }

    public function test_static_delete_method() {

        $id1 = $this->rows[0]['id'];

        myModel::delete($id1);

        $id2 = $this->rows[1]['id'];

        myModel::delete($id2);

        try {

            $prepare = ModelTest::$connection->prepare (
                "select * from " . myModel::get_table() . " where id='" . $id1 . "' or id='" . $id2 . "';"
            );
            
            $prepare->execute();

            $rows = $prepare->fetchAll();

        } catch (PDOException $e) {

            $this->fail("error in fetch data");
        }

        $this->assertCount(0, $rows);
    }

    public function test_static_all_method() {
        $row1 = $this->rows[0];
        $row2 = $this->rows[1];
        $row3 = $this->rows[2];

        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::all();

        $this->assertNotEquals($rows, null);

        $this->assertCount(3, $rows);

        foreach($rows as $row) {
            $this->assertEquals(isset($row['id']), true);

            switch($row['id']) {
                case 1:
                    $data = $row1;
                    break;
                case 2:
                    $data = $row2;
                    break;
                case 3:
                    $data = $row3;
                    break;
                default: 
                    $this->fail("id not equals my input ids");
            }

            foreach($data as $key => $value) {
                $this->assertEquals($row[$key], $value);
            }
        }

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_static_find_method() {

        myModel::create($this->rows[0]);

        $notFoundRow = myModel::find(100);

        $this->assertEquals($notFoundRow, null);

        $row = myModel::find($this->rows[0]['id']);

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[0]['id']);

        $this->assertEquals($row->first_name, $this->rows[0]['first_name']);

        $this->assertEquals($row->last_name, $this->rows[0]['last_name']);

        $this->assertEquals($row->username, $this->rows[0]['username']);

        $this->assertEquals($row->phone, $this->rows[0]['phone']);

        $this->assertEquals($row->email, $this->rows[0]['email']);
        
        myModel::delete($this->rows[0]['id']);    
        
    }

    public function test_get_method() {

        $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $row1 = myModel::find($this->rows[0]['id'])->get();

        $this->assertNotEquals($row1, null);
        foreach($this->rows[0] as $key => $value) {
            $this->assertEquals($row1[$key], $value);
        }


        $row2 = myModel::find($this->rows[1]['id'])->get();

        $this->assertNotEquals($row2, null);
        foreach($this->rows[1] as $key => $value) {
            $this->assertEquals($row2[$key], $value);
        }


        $row3 = myModel::find($this->rows[2]['id'])->get();

        $this->assertNotEquals($row3, null);
        foreach($this->rows[2] as $key => $value) {
            $this->assertEquals($row3[$key], $value);
        }

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_if_model_instance_is_iterator() {
        myModel::create($this->rows[0]);

        $row = myModel::find($this->rows[0]['id']);

        $this->assertIsIterable($row);

        $row1 = myModel::find($this->rows[0]['id'])->get();

        foreach($row as $key => $value) {
            $this->assertEquals($row1[$key], $value);
        }

        myModel::delete($this->rows[0]['id']);
    }

    public function test_static_select_method() {
        
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::select(["id", "email", "phone"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        foreach($rows as $row) {
            $keys = array_keys($row->get());

            $this->assertCount(3, $keys);
            $this->assertContains('id', $keys);
            $this->assertContains('email', $keys);
            $this->assertContains('phone', $keys);

            $this->assertEquals($realRows[$row['id']]->email, $row['email']);
            $this->assertEquals($realRows[$row['id']]->phone, $row['phone']);
        }

        $this->deleteCreatedRowsOfMyModel();
    }
    
    public function test_static_where_method() {

        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        /* STATRT static where test 1 */
        $rows = myModel::where("id", "=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["1"]->get(), $rows[0]));
        /* END static where test 1 */

        /* STATRT static where test 2 */
        $rows = myModel::where("id", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["2"]->get(), $rows[0]));
        /* END static where test 2 */

        /* STATRT static where test 3 */
        $rows = myModel::where("id", "!=", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["3"]->get()], $rows));
        /* END static where test 3 */

        /* STATRT static where test 4 */
        $rows = myModel::where("id", ">", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END static where test 4 */

        /* STATRT static where test 5 */
        $rows = myModel::where("id", "<", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get()], $rows));
        /* END static where test 5 */

        /* STATRT static where test 6 */
        $rows = myModel::where("id", "<=", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END static where test 6 */

        /* STATRT static where test 7 */
        $rows = myModel::where("id", ">=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END static where test 7 */

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_static_orderBy_method() {

        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::orderBy(["email", "DESC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] > $rows[$i+1]['email']) );
        }


        $rows = myModel::orderBy(["email", "ASC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] < $rows[$i+1]['email']) );
        }


        $rows = myModel::orderBy(["email"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] < $rows[$i+1]['email']) );
        }


        $rows = myModel::orderBy(["id"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['id'] < $rows[$i+1]['id']) );
        }


        $rows = myModel::orderBy(["id", "DESC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['id'] > $rows[$i+1]['id']) );
        }

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_static_limit_method() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::limit(11)->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));


        $rows = myModel::limit(2)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::limit(1)->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::limit(3)->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::limit(3, 1)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["2"]->get(), $realRows["3"]->get() ]));

        $rows = myModel::limit(2, 0)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["1"]->get(), $realRows["2"]->get() ]));

        $rows = myModel::limit(3, 2)->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["3"]->get() ]));

        $this->deleteCreatedRowsOfMyModel();

        $this->expectException(\InvalidArgumentException::class);
        $rows = myModel::limit(0)->get();
    }

    public function test_static_paginate_method() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $result = myModel::paginate(12);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(3, $result->items());
        $this->assertTrue($this->rowsEquals($items, $realRows));

        $result = myModel::paginate(3);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(3, $result->items());
        $this->assertTrue($this->rowsEquals($items, $realRows));

        $result = myModel::paginate(2);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(2, $result->items());
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $result = myModel::paginate(1);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(1, $result->items());
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $this->deleteCreatedRowsOfMyModel();

        $this->expectException(\InvalidArgumentException::class);
        $result = myModel::paginate(0);
    }

    public function test_where_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();
        
        /* STATRT where test 1 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", "=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["1"]->get(), $rows[0]));
        /* END where test 1 */

        /* STATRT where test 2 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["2"]->get(), $rows[0]));
        /* END where test 2 */

        /* STATRT where test 3 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", "!=", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["3"]->get()], $rows));
        /* END where test 3 */

        /* STATRT where test 4 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", ">", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END where test 4 */

        /* STATRT where test 5 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", "<", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get()], $rows));
        /* END where test 5 */

        /* STATRT where test 6 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", "<=", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END where test 6 */

        /* STATRT where test 7 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->where("id", ">=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END where test 7 */

        /* STATRT where test 8 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", "1")
            ->where("id", "2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(0, $rows);
        /* END where test 8 */

        /* STATRT where test 9 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", ">", "1")
            ->where("id", "=", "2")
            ->where("id", "<", "3")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["2"]->get()], $rows));
        /* END where test 9 */

        /* STATRT where test 10 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", "!=", "1")
            ->where("id", "!=", "2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["3"]->get()], $rows));
        /* END where test 10 */

        /* STATRT where test 11 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("first_name", "=", "esmaeel")
            ->where("last_name", "=", "al-hadidi")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END where test 11 */

        /* STATRT where test 11 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("first_name", "=", "esmaeel")
            ->where("last_name", "=", "al-hadidi")
            ->where("username", "=", "esmaeel2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["2"]->get()], $rows));
        /* END where test 11 */

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_orderBy_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->orderBy(["email", "DESC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] > $rows[$i+1]['email']) );
        }


        $rows = myModel::select(['email'])->orderBy(["email", "ASC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] < $rows[$i+1]['email']) );
        }


        $rows = myModel::select(['email'])->orderBy(["email"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['email'] < $rows[$i+1]['email']) );
        }


        $rows = myModel::select(['email', 'id'])->orderBy(["id"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['id'] < $rows[$i+1]['id']) );
        }


        $rows = myModel::select(['email', 'id'])->orderBy(["id", "DESC"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(3, $rows);

        for($i = 0; $i < count($rows)-1; $i++) {

            $this->assertTrue( ($rows[$i]['id'] > $rows[$i+1]['id']) );
        }

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_limit_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(11)->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));


        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(2)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(1)->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(3)->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsInRows($rows, $realRows));

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(3, 1)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["2"]->get(), $realRows["3"]->get() ]));

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(2, 0)->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["1"]->get(), $realRows["2"]->get() ]));

        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->limit(3, 2)->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowsEquals($rows, [ $realRows["3"]->get() ]));

        $this->deleteCreatedRowsOfMyModel();

        $this->expectException(\InvalidArgumentException::class);
        $rows = myModel::limit(0)->get();
    }

    public function test_orWhere_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();
        
        /* STATRT orWhere test 1 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", "=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["1"]->get(), $rows[0]));
        /* END orWhere test 1 */

        /* STATRT orWhere test 2 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($realRows["2"]->get(), $rows[0]));
        /* END orWhere test 2 */

        /* STATRT orWhere test 3 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", "!=", "2")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 3 */

        /* STATRT orWhere test 4 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", ">", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 4 */

        /* STATRT orWhere test 5 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", "<", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get()], $rows));
        /* END orWhere test 5 */

        /* STATRT orWhere test 6 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", "<=", "3")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 6 */

        /* STATRT orWhere test 7 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->orWhere("id", ">=", "1")->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 7 */

        /* STATRT orWhere test 8 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", "1")
            ->orWhere("id", "2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get()], $rows));
        /* END orWhere test 8 */

        /* STATRT orWhere test 9 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", ">", "1")
            ->orWhere("id", "=", "2")
            ->orWhere("id", "<", "3")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 9 */

        /* STATRT orWhere test 10 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("id", "!=", "1")
            ->orWhere("id", "!=", "2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 10 */

        /* STATRT orWhere test 11 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("first_name", "=", "esmaeel")
            ->orWhere("last_name", "=", "al-hadidi")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 11 */

        /* STATRT orWhere test 11 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("first_name", "=", "esmaeel")
            ->orWhere("last_name", "=", "al-hadidi")
            ->where("username", "=", "esmaeel2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 11 */

        /* STATRT orWhere test 12 */
        $rows = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where("first_name", "=", "esmaeel")
            ->orWhere("last_name", "=", "al-hadidi")
            ->orWhere("username", "=", "esmaeel2")
            ->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals([$realRows["1"]->get(), $realRows["2"]->get(), $realRows["3"]->get()], $rows));
        /* END orWhere test 12 */

        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_paginate_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();
        
        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->paginate(12);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertTrue($this->rowsEquals($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->paginate(3);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertTrue($this->rowsEquals($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->paginate(2);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->paginate(1);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where('id', '1')->paginate(2);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertEquals($result->total(), 1);
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where('id', '1')->orWhere('id', '2')->paginate(3);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertEquals($result->total(), 2);
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertTrue($this->rowsInRows($items, $realRows));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where('id', '>', '1')->orWhere('id', '1')->orderBy(['id', 'DESC'])->paginate(2);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertEquals($result->total(), 3);
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertTrue($this->rowsEquals($items, [$realRows['3']->get(), $realRows['2']->get()]));

        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])
            ->where('id', '>', '1')->orWhere('id', '1')->orderBy(['email'])->paginate(2);
        $this->assertInstanceOf(Paginator::class, $result);
        $items = $result->items();
        $this->assertEquals($result->total(), 3);
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertTrue($this->rowsEquals($items, [$realRows['1']->get(), $realRows['3']->get()]));

        $this->deleteCreatedRowsOfMyModel();
        
        $this->expectException(\InvalidArgumentException::class);
        $result = myModel::select(['id', 'first_name', 'last_name', 'username', 'phone', 'email', 'profile_pictuer'])->paginate(0);

        $this->expectException(ModelException::class);
        myModel::limit(10)->paginate(2);
    }

    public function test_delete_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();
        
        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(count($realRows), $rows);
        $this->assertTrue($this->rowsEquals($rows, $realRows));

        myModel::find("2")->delete();

        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(count($realRows) - 1, $rows);
        $this->assertTrue($this->rowsEquals($rows, [$realRows['1']->get(), $realRows['3']->get()]));

        myModel::find("1")->delete();

        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(count($realRows) - 2, $rows);
        $this->assertTrue($this->rowsEquals($rows, [$realRows['3']->get()]));

        myModel::find("3")->delete();

        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(count($realRows) - 3, $rows);

        $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $this->expectException(\InvalidArgumentException::class);
        myModel::find("3")->delete(3);

    }

    public function test_select_method_of_instance() {
        $this->deleteCreatedRowsOfMyModel();
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::limit(2)->select(["id", "email", "phone"])->get();

        $this->assertIsArray($rows);

        $this->assertCount(2, $rows);

        foreach($rows as $row) {
            $keys = array_keys($row->get());

            $this->assertCount(3, $keys);
            $this->assertContains('id', $keys);
            $this->assertContains('email', $keys);
            $this->assertContains('phone', $keys);

            $this->assertEquals($realRows[$row['id']]->email, $row['email']);
            $this->assertEquals($realRows[$row['id']]->phone, $row['phone']);
        }

        $this->deleteCreatedRowsOfMyModel();

        $this->expectException(ModelException::class);
        myModel::select(["id", "email", "phone"])->select(["email", "phone"])->get();

    }

    public function test_update_method_of_instance() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(count($realRows), $rows);
        $this->assertTrue($this->rowsEquals($rows, $realRows));

        $newDataOfRow1 = [
            'first_name' => 'esmaeel_after_update',
            'last_name' => 'al-hadidi_after_update',
            'username' => 'esmaeel_after_update',
            'phone' => '0797886161',
            'email' => 'ismaeel1_after_update.hadidi@gmail.com'
        ];
        $updated = myModel::find('1')->update($newDataOfRow1);
        $rows = myModel::where('id', '1')->select(['first_name', 'last_name', 'username', 'phone', 'email'])->get();
        $this->assertNotEquals($updated, false);
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($rows[0], $newDataOfRow1));
        $this->assertTrue($this->rowEquals(myModel::find(1), $updated));

        $newDataOfRow2 = [
            'first_name' => 'esmaeel_after_update',
            'last_name' => 'al-hadidi_after_update',
            'username' => 'esmaeel2_after_update',
            'phone' => '0780001234',
            'email' => 'ismaeel3_after_update.hadidi@gmail.com'
        ];
        $updated = myModel::find('2')->update($newDataOfRow2);
        $rows = myModel::select(['first_name', 'last_name', 'username', 'phone', 'email'])->where('id', '2')->get();
        $this->assertNotEquals($updated, false);
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($rows[0], $newDataOfRow2));
        $this->assertTrue($this->rowEquals(myModel::find(2), $updated));

        $newDataOfRow3 = [
            'id' => 4,
            'first_name' => 'esmaeel_after_update',
            'last_name' => 'al-hadidi_after_update',
            'username' => 'esmaeel3_after_update',
            'phone' => '0777886161'
        ];
        $updated = myModelWithFillable::find('3')->update($newDataOfRow3);
        $rows = myModelWithFillable::select(['id', 'first_name', 'last_name', 'username', 'phone'])->where('id', '4')->get();
        $this->assertNotEquals($updated, false);
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertTrue($this->rowEquals($rows[0], $newDataOfRow3));
        $this->assertTrue($this->rowEquals(myModelWithFillable::find(4), $updated));


        $newDataOfRow3['email'] = $realRows['3']->email;
        unset($newDataOfRow3['id']);

        $rows = myModel::select(['first_name', 'last_name', 'username', 'phone', 'email'])->get();
        $this->assertIsArray($rows);
        $this->assertCount(3, $rows);
        $this->assertTrue($this->rowsEquals($rows, [$newDataOfRow1, $newDataOfRow2, $newDataOfRow3]));

        $newData = [
            'first_name' => 'esmaeel_after_two_update',
            'last_name' => 'hadidi_after_two_update',
            'username' => 'esmaeel3_after_two_update',
            'phone' => '0777886161',
            'email' => 'ismaeel2_after_two_update.hadidi@gmail.com', 
        ];
        $updated = myModelWithFillable::find('4')->update($newData);
        $rows = myModelWithFillable::select(['first_name', 'last_name', 'username', 'phone', 'email'])->where('id', '4')->get();
        $this->assertNotEquals($updated, false);
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $newData['email'] = $realRows['3']->email;
        $this->assertTrue($this->rowEquals($rows[0], $newData));
        $this->assertTrue($this->rowEquals(myModelWithFillable::find(4), $updated));

        $this->assertTrue(myModelWithFillable::delete(4));
        $this->deleteCreatedRowsOfMyModel();
    }

    public function test_chain_where_methods() {
        $this->genertateFactorRowsOfMyModel();

        $rows = myModel::where('id', '<', 25)->where('id', '>', 5)->get();
        $this->assertIsArray($rows);
        $this->assertCount(19, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] < 25 && $row['id'] > 5);


        $rows = myModel::where('id', '<', 15)->where('id', '>', 10)->get();
        $this->assertIsArray($rows);
        $this->assertCount(4, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] < 15 && $row['id'] > 10);

        $rows = myModel::where('id', '<', 5)->orWhere('id', '>', 25)->get();
        $this->assertIsArray($rows);
        $this->assertCount(9, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] < 5 || $row['id'] > 25);

        $rows = myModel::where('id', '!=', 1)->where('id', '!=', 2)
            ->where('id', '!=', 3)->where('id', '!=', 4)->where('id', '!=', 5)
            ->where('id', '<', 25)->get();
        $this->assertIsArray($rows);
        $this->assertCount(19, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] < 25 && $row['id'] > 5);

        $rows = myModel::where('id', '<=', 5)->orWhere('id', '>=', 25)->get();
        $this->assertIsArray($rows);
        $this->assertCount(11, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] <= 5 || $row['id'] >= 25);

        $rows = myModel::where('id', '<=', 5)->orWhere('id', '>=', 25)->where('id', '!=', '1')->get();
        $this->assertIsArray($rows);
        $this->assertCount(11, $rows);
        foreach($rows as $row) $this->assertTrue($row['id'] <= 5 || $row['id'] >= 25 && $row['id'] != 1);
        
        $this->removeFactorRowsOfMyModel();
    }

    public function test_chain_query_methods() {
        $this->genertateFactorRowsOfMyModel();

        $rows = myModel::select(['first_name', 'last_name'])->where('id', '<', 25)->where('id', '>', 5)->get();
        $this->assertIsArray($rows);
        $this->assertCount(19, $rows);
        foreach($rows as $row) {
            $this->assertTrue(str_replace('esmaeel', '', $row['first_name']) < 25 && str_replace('esmaeel', '', $row['first_name']) > 5);
            $keys = array_keys($row->get());
            $this->assertCount(2, $keys);
            $this->assertContains('first_name', $keys);
            $this->assertContains('last_name', $keys);
        }

        $rows = myModel::select(['id', 'email'])
            ->where('id', '<', 5)->orWhere('id', '>', 25)
            ->orderBy(['id', 'DESC'])->limit(7)->get();
        $this->assertIsArray($rows);
        $this->assertCount(7, $rows);
        $last = 31;
        foreach($rows as $row) {
            // for where
            $this->assertTrue($row['id'] > 25 || $row['id'] < 5);
            // for orderBy
            $this->assertTrue($row['id'] < $last);
            $last = $row['id'];
            // for select
            $keys = array_keys($row->get());
            $this->assertCount(2, $keys);
            $this->assertContains('id', $keys);
            $this->assertContains('email', $keys);
        }

        $rows = myModel::select(['id', 'username', 'phone'])
            ->where('id', '>', 5)->where('id', '>', 10)
            ->orderBy(['id', 'ASC'])->limit(12, 5)->get();
        $this->assertIsArray($rows);
        $this->assertCount(12, $rows);
        $last = 0;
        $this->assertTrue($rows[0]['id'] > 15);
        foreach($rows as $row) {
            // for where
            $this->assertTrue($row['id'] > 10);
            // for orderBy
            $this->assertTrue($row['id'] > $last);
            $last = $row['id'];
            // for select
            $keys = array_keys($row->get());
            $this->assertCount(3, $keys);
            $this->assertContains('id', $keys);
            $this->assertContains('username', $keys);
            $this->assertContains('phone', $keys);
        }

        $result = myModel::select(['id', 'username', 'phone'])
            ->where('id', '>', 5)->where('id', '>', 10)
            ->orderBy(['id', 'ASC'])->paginate(12);
        $this->assertInstanceOf(Paginator::class, $result);
        $rows = $result->items();
        $this->assertIsArray($rows);
        $this->assertCount(12, $rows);
        $last = 0;
        foreach($rows as $row) {
            // for where
            $this->assertTrue($row['id'] > 10);
            // for orderBy
            $this->assertTrue($row['id'] > $last);
            $last = $row['id'];
            // for select
            $keys = array_keys($row->get());
            $this->assertCount(3, $keys);
            $this->assertContains('id', $keys);
            $this->assertContains('username', $keys);
            $this->assertContains('phone', $keys);
        }
        $limitedRows = myModel::select(['id', 'username', 'phone'])
            ->where('id', '>', 5)->where('id', '>', 10)
            ->orderBy(['id', 'ASC'])->limit(12)->get();
        $this->assertTrue($this->rowsEquals($rows, $limitedRows));

        $this->removeFactorRowsOfMyModel();

        $this->expectException(ModelException::class);
        $rows = myModel::select(['id', 'username', 'phone'])
            ->where('id', '>', 5)->where('id', '>', 10)
            ->orderBy(['id', 'ASC'])->limit(12, 5)->paginate(11);
    }

    public function test_first_method() {
        $realRows = $this->createAndReturnThreeRowsOfMyModelAsAssociativeArray();

        $notFoundRow = myModel::where('id', '>', 10)->first();

        $this->assertEquals($notFoundRow, null);

        $row = myModel::where('id', $this->rows[0]['id'])->first();

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[0]['id']);

        $this->assertEquals($row->first_name, $this->rows[0]['first_name']);

        $this->assertEquals($row->last_name, $this->rows[0]['last_name']);

        $this->assertEquals($row->username, $this->rows[0]['username']);

        $this->assertEquals($row->phone, $this->rows[0]['phone']);

        $this->assertEquals($row->email, $this->rows[0]['email']);


        $row = myModel::where('id', $this->rows[1]['id'])->first();

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[1]['id']);

        $this->assertEquals($row->first_name, $this->rows[1]['first_name']);

        $this->assertEquals($row->last_name, $this->rows[1]['last_name']);

        $this->assertEquals($row->username, $this->rows[1]['username']);

        $this->assertEquals($row->phone, $this->rows[1]['phone']);

        $this->assertEquals($row->email, $this->rows[1]['email']);


        $row = myModel::where('id', '>', $this->rows[0]['id'])->orderBy(['id', 'DESC'])->first();

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[2]['id']);

        $this->assertEquals($row->first_name, $this->rows[2]['first_name']);

        $this->assertEquals($row->last_name, $this->rows[2]['last_name']);

        $this->assertEquals($row->username, $this->rows[2]['username']);

        $this->assertEquals($row->phone, $this->rows[2]['phone']);

        $this->assertEquals($row->email, $this->rows[2]['email']);


        $row = myModel::where('id', '>', $this->rows[0]['id'])->orderBy(['id', 'ASC'])->first();

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[1]['id']);

        $this->assertEquals($row->first_name, $this->rows[1]['first_name']);

        $this->assertEquals($row->last_name, $this->rows[1]['last_name']);

        $this->assertEquals($row->username, $this->rows[1]['username']);

        $this->assertEquals($row->phone, $this->rows[1]['phone']);

        $this->assertEquals($row->email, $this->rows[1]['email']);


        $row = myModel::select(['id', 'first_name'])
            ->where('id', '>', $this->rows[0]['id'])
            ->orderBy(['id', 'ASC'])
            ->first();

        $this->assertInstanceOf(myModel::class, $row);

        $this->assertEquals($row->id, $this->rows[1]['id']);

        $this->assertEquals($row->first_name, $this->rows[1]['first_name']);

        $keys = array_keys($row->get());

        $this->assertCount(2, $keys);


        $this->removeFactorRowsOfMyModel();
    }

}
