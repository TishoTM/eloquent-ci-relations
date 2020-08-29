<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Todo;
use TishoTM\Tests\Item;
use TishoTM\Tests\Meta;
use TishoTM\Tests\TodoCi;
use TishoTM\Tests\ItemCi;

/**
 * @runTestsInSeparateProcesses
 */
class HasOneHasOneThroughTest extends TestBase
{
    /**
     * Set up the DB tables.
     */
    public function setUp()
    {
        parent::setUp();

        $connection = $this->db->connection();

        $connection->statement('CREATE TABLE todos (
            key TEXT PRIMARY KEY unique NOT NULL COLLATE NOCASE,
            title TEXT,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE items (
            uuid TEXT PRIMARY KEY unique NOT NULL COLLATE NOCASE,
            title TEXT,
            description TEXT,
            todo_key TEXT NOT NULL COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE meta (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT,
            item_uuid TEXT NOT NULL COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');
    }

    /**
     * Test HasOne
     * @test
     */
    public function testRetrieveOne()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'todo_key' => 'not-important',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        $meta = Meta::create([
            'content' => 'meta content',
            'item_uuid' => 'aaa',
        ]);

        $item1 = Item::with('meta')->find($item->uuid);
        $this->assertNull($item1->meta);

        $item2 = ItemCi::with('meta')->find($item->uuid);
        $this->assertNotSame($item->uuid, $item2->meta->item_uuid); // AAA vs. aaa
    }

    /**
     * Test HasOneThrough
     * @test
     */
    public function testRetrieveOneThrough()
    {
        if (!class_exists(\Illuminate\Database\Eloquent\Relations\HasOneThrough::class)) {
            $this->markTestSkipped("Relation HasOneThrough is not implemented");
        }

        $todo = Todo::create([
            'key' => 'todo-key-for-bbb',
            'title' => 'Todo list',
        ]);

        $item = Item::create([
            'uuid' => 'BBB',
            'todo_key' => 'todo-KEY-for-BBB',
            'title' => 'Item 1',
            'description' => 'Description for bbb',
        ]);

        $meta = Meta::create([
            'content' => 'meta content',
            'item_uuid' => 'bbb',
        ]);

        $todo1 = Todo::with('itemMeta')->find('todo-key-for-bbb');
        $this->assertNull($todo1->itemMeta);

        $todo2 = TodoCi::with('itemMeta')->find('todo-key-for-bbb');
        $this->assertNotNull($todo2->itemMeta);
        $this->assertNotSame($item->uuid, $todo2->itemMeta->item_uuid); // BBB vs. bbb
    }
}
