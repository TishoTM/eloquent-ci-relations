<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\Note;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Keyword;

class HasManyThroughTest extends TestBase
{
    public function setUp()
    {
        parent::setUp();

        $connection = $this->db->connection();

        $connection->statement('CREATE TABLE items (
            uuid TEXT PRIMARY KEY UNIQUE NOT NULL COLLATE NOCASE,
            title TEXT,
            description TEXT,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT,
            key TEXT unique NOT NULL COLLATE NOCASE,
            item_uuid TEXT NOT NULL COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE keywords (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            note_key TEXT NOT NULL COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');
    }

    /**
     * @test
     */
    public function testRetrieveManyThrough()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        Note::create([
            'item_uuid' => 'aaa',
            'key' => 'note_aaa',
            'content' => 'note 1',
        ]);

        Note::create([
            'item_uuid' => 'bbb',
            'key' => 'note_bbb',
            'content' => 'note 2',
        ]);

        Keyword::create([
            'note_key' => 'note_aaa',
            'title' => 'bar',
        ]);

        Keyword::create([
            'note_key' => 'note_AAA',
            'title' => 'foo',
        ]);

        $item1 = Item::with('keywords')->find($item->uuid);
        $this->assertCount(0, $item1->keywords);

        $item2 = ItemCi::with('keywords')->find($item->uuid);
        $this->assertCount(2, $item2->keywords);
    }
}
