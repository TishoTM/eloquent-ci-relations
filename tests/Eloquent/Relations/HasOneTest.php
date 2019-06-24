<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\Meta;
use TishoTM\Tests\ItemCi;

class HasOneTest extends TestBase
{
    public function setUp()
    {
        parent::setUp();

        $connection = $this->db->connection();

        $connection->statement('CREATE TABLE items (
            -- id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT PRIMARY KEY unique NOT NULL COLLATE NOCASE,
            title TEXT,
            description TEXT,
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
     * @test
     */
    public function testRetrieveOne()
    {
        $item = Item::create([
            'uuid' => 'AAA',
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
}
