<?php

use PHPUnit\Framework\TestCase;
use ppEntity\BasicEntity;
use ppEntity\List\BasicList;

class BasicTest extends TestCase {

    protected static bool $initialized = FALSE;
    protected static $totalTests = 0;
    protected static $currentTest = 0;

    protected static BasicEntity $myObj;
    protected static BasicEntity $myObj2;
    protected static BasicEntity $myObj3;
    protected static BasicEntity $myObj4;

    public static function setUpBeforeClass(): void {
        $methods = get_class_methods(static::class);
        self::$totalTests = array_reduce($methods, function($total, $item) {

            if(str_starts_with($item, 'test')){
                $total++;
            }

            return $total;
        });
    }

    public function setUp(): void {
        parent::setUp();
        self::$currentTest++;
        if (!self::$initialized) {
            if (!file_exists(__DIR__ . '/../data')) {
                mkdir(__DIR__ . '/../data');
                chmod(__DIR__ . '/../data', 0777);
            }
            if (!file_exists(__DIR__ . '/../data/testdb.sqlite')) {
                touch(__DIR__ . '/../data/testdb.sqlite');
                chmod(__DIR__ . '/../data/testdb.sqlite', 0777);
            }
            file_put_contents(__DIR__ . '/../.env', "DB_TYPE=sqlite\nDB_PATH=" . __DIR__ . '/../data/testdb.sqlite');
            self::$initialized = TRUE;
        }


    }

    public function testInitialize() {
        // Initialize object
        self::$myObj = new ppEntity\BasicEntity("test");
        // check if initializations worked
        $this->assertNotNull(self::$myObj);;
        $this->assertTrue(self::$myObj->isInitialized());
        // check if bean name was correctly passed
        $this->assertEquals("test", self::$myObj->name);



    }

    public function testFirstObject() {
        // set some values
        self::$myObj->value1 = 'String 1';
        $this->assertIsString(self::$myObj->value1);;
        $this->assertEquals('String 1', self::$myObj->value1);
        self::$myObj->value2 = 123;
        $this->assertIsInt(self::$myObj->value2);
        $this->assertEquals(123, self::$myObj->value2);
        // save object
        self::$myObj->save();
        // save id for later
        $id = self::$myObj->id;
        // now load object and check if values are correct
        self::$myObj = new ppEntity\BasicEntity("test", $id);
        $this->assertNotNull(self::$myObj);;
        $this->assertTrue(self::$myObj->isInitialized());
        $this->assertEquals("test", self::$myObj->name);
        $this->assertIsString(self::$myObj->value1);;
        $this->assertEquals('String 1', self::$myObj->value1);
        $this->assertIsString(self::$myObj->value2);
        $this->assertEquals('123', self::$myObj->value2);
    }

    public function testSecondObject() {

        // add another object
        self::$myObj2 = new ppEntity\BasicEntity("test");
        // check if initializations worked
        $this->assertNotNull(self::$myObj2);;
        $this->assertTrue(self::$myObj2->isInitialized());
        // check if bean name was correctly passed
        $this->assertEquals("test", self::$myObj2->name);
        // set some values
        self::$myObj2->value1 = 'String 2';
        $this->assertIsString(self::$myObj2->value1);;
        $this->assertEquals('String 2', self::$myObj2->value1);
        self::$myObj2->value2 = 456;
        $this->assertIsInt(self::$myObj2->value2);
        $this->assertEquals(456, self::$myObj2->value2);
        // save object
        self::$myObj2->save();
        unset($myObj2);
    }

    public function testThirdObject() {

        // add another object
        self::$myObj3 = new ppEntity\BasicEntity("test");
        // check if initializations worked
        $this->assertNotNull(self::$myObj3);;
        $this->assertTrue(self::$myObj3->isInitialized());
        // check if bean name was correctly passed
        $this->assertEquals("test", self::$myObj3->name);
        // set some values
        self::$myObj3->value1 = 'String 3';
        $this->assertIsString(self::$myObj3->value1);;
        $this->assertEquals('String 3', self::$myObj3->value1);
        self::$myObj3->value2 = 789;
        $this->assertIsInt(self::$myObj3->value2);
        $this->assertEquals(789, self::$myObj3->value2);
        // save object
        self::$myObj3->save();
        unset($myObj3);
    }


    public function testFourthObject() {

        // add another object
        self::$myObj4 = new ppEntity\BasicEntity("test");
        // check if initializations worked
        $this->assertNotNull(self::$myObj4);;
        $this->assertTrue(self::$myObj4->isInitialized());
        // check if bean name was correctly passed
        $this->assertEquals("test", self::$myObj4->name);
        // set some values
        self::$myObj4->value1 = 'Special';
        $this->assertIsString(self::$myObj4->value1);;
        $this->assertEquals('Special', self::$myObj4->value1);
        self::$myObj4->value2 = 999;
        $this->assertIsInt(self::$myObj4->value2);
        $this->assertEquals(999, self::$myObj4->value2);
        // save object
        self::$myObj4->save();
        unset($myObj4);
    }

    public function testBasicListOfAll() {
        // now test the list funtion
        $count = BasicList::getAllCount('test');
        $this->assertEquals(4, $count);
        $myList = BasicList::getAll("test");
        $this->assertIsArray($myList);
        $this->assertEquals(4, count($myList));
        $this->assertEquals("String 1", $myList[0]->value1);
        $this->assertEquals("String 2", $myList[1]->value1);
        $this->assertEquals("String 3", $myList[2]->value1);
        $this->assertEquals("Special", $myList[3]->value1);
    }

    public function testBasicFindBy() {
        // now test the list funtion
        $myList = BasicList::findBy('test', 'value2 > 500');
        $this->assertEquals(2, count($myList));
        $this->assertEquals("String 3", $myList[0]->value1);
        $this->assertEquals("Special", $myList[1]->value1);
    }

    public function testExtendedFindBy() {
        $myList = BasicList::findBy('test', 'value1 LIKE "String%"');
        $this->assertEquals(3, count($myList));
        $this->assertEquals("String 1", $myList[0]->value1);
        $this->assertEquals("String 2", $myList[1]->value1);
        $this->assertEquals("String 3", $myList[2]->value1);
    }


    public function tearDown(): void {
        if(self::$currentTest == self::$totalTests) {
            self::$initialized = FALSE;
            unlink(__DIR__ . '/../data/testdb.sqlite');
            unlink(__DIR__ . '/../.env');
        }
        parent::tearDown();
    }
}