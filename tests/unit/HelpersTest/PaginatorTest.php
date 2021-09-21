<?php 

use PHPUnit\Framework\TestCase;
use App\Helpers\Paginator;

class PaginatorTest extends TestCase {

    private $paginator;

    private $path;

    private $rows = array (
        [
            'id' => 1,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel',
            'phone' => '0797886161',
            'email' => 'ismaeel1.hadidi@gmail.com'
        ],
        [
            'id' => 2,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel2',
            'phone' => '0780001234',
            'email' => 'ismaeel3.hadidi@gmail.com',
        ],
        [
            'id' => 3,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel3',
            'phone' => '0777886161',
            'email' => 'ismaeel2.hadidi@gmail.com', 
        ]
    );

    protected function setUp() : void {

        $this->paginator = new Paginator($this->rows, 9, 3, 1);

        $this->path = 'http://test.com';
    }
    
    public function test_if_it_is_iterator() {

        $this->assertIsIterable($this->paginator);

        $i = 0;
        foreach($this->paginator as $user) {

            $this->assertEquals($user["id"], $this->rows[$i]["id"]);
            $this->assertEquals($user["username"], $this->rows[$i]["username"]);
            $this->assertEquals($user["phone"], $this->rows[$i]["phone"]);
            $this->assertEquals($user["email"], $this->rows[$i]["email"]);

            $i++;
        }

        $this->assertEquals($i, 3);
    }

    public function test_if_it_is_json_serializable() {

        $paginator = json_decode(json_encode($this->paginator), true);

        $this->assertIsArray($paginator);

        $paginator_keys = array_keys($paginator);

        $this->assertTrue(in_array('total', $paginator_keys));
        $this->assertTrue(in_array('per_page', $paginator_keys));
        $this->assertTrue(in_array('current_page', $paginator_keys));
        $this->assertTrue(in_array('last_page', $paginator_keys));
        $this->assertTrue(in_array('first_page_url', $paginator_keys));
        $this->assertTrue(in_array('last_page_url', $paginator_keys));
        $this->assertTrue(in_array('next_page_url', $paginator_keys));
        $this->assertTrue(in_array('prev_page_url', $paginator_keys));
        $this->assertTrue(in_array('path', $paginator_keys));
        $this->assertTrue(in_array('from', $paginator_keys));
        $this->assertTrue(in_array('to', $paginator_keys));
        $this->assertTrue(in_array('data', $paginator_keys));

        $data = $paginator['data'];

        $this->assertCount(3, $data);

        for($i = 0; $i < count($data); $i++) {

            $this->assertIsArray($data[$i]);
            
            $keys = array_keys($data[$i]);

            $this->assertCount(6, $keys);

            $this->assertContains('id', $keys);
            $this->assertContains('first_name', $keys);
            $this->assertContains('last_name', $keys);
            $this->assertContains('username', $keys);
            $this->assertContains('phone', $keys);
            $this->assertContains('email', $keys);

        }
    }

    public function test_count_method() {

        $this->assertTrue(method_exists(Paginator::class, 'count'), 'Paginator Class does not have method count');

        $this->assertEquals($this->paginator->count(), 3);
    }

    public function test_items_method() {

        $this->assertTrue(method_exists(Paginator::class, 'items'), 'Paginator Class does not have method items');


        $items = $this->paginator->items();

        $this->assertCount(3, $items);

        for($i = 0; $i < count($items); $i++) {

            $this->assertIsArray($items[$i]);
            
            $keys = array_keys($items[$i]);

            $this->assertCount(6, $keys);

            $this->assertContains('id', $keys);
            $this->assertContains('first_name', $keys);
            $this->assertContains('last_name', $keys);
            $this->assertContains('username', $keys);
            $this->assertContains('phone', $keys);
            $this->assertContains('email', $keys);

        }

    }

    public function test_currentPage_method() {

        $this->assertTrue(method_exists(Paginator::class, 'currentPage'), 'Paginator Class does not have method currentPage');

        $this->assertEquals($this->paginator->currentPage(), 1);
    }

    public function test_nextPageUrl_method() {

        $this->assertTrue(method_exists(Paginator::class, 'nextPageUrl'), 'Paginator Class does not have method nextPageUrl');

        $this->assertEquals($this->paginator->nextPageUrl(), $this->path . "?page=2");

    }

    public function test_previousPageUrl_method() {
        
        $this->assertTrue(method_exists(Paginator::class, 'previousPageUrl'), 'Paginator Class does not have method previousPageUrl');

        $this->assertEquals($this->paginator->previousPageUrl(), null);
    }

    public function test_total_method() {
        
        $this->assertTrue(method_exists(Paginator::class, 'total'), 'Paginator Class does not have method total');

        $this->assertEquals($this->paginator->total(), 9);
    }

    public function test_url_method() {
        
        $this->assertTrue(method_exists(Paginator::class, 'url'), 'Paginator Class does not have method url');
        
        $this->assertEquals($this->paginator->url(3), $this->path . "?page=3");
    }

    public function test_perPage_method() {
        
        $this->assertTrue(method_exists(Paginator::class, 'perPage'), 'Paginator Class does not have method perPage');

        $this->assertEquals($this->paginator->perPage(), 3);
    }
}