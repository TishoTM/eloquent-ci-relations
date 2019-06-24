<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Note;
use TishoTM\Tests\NoteCi;

class BelongsToHasManyTest extends TestBase
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
    }

    /**
     * @test
     */
    public function testRetrieveParent()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        $note = Note::create([
            'key' => 'NOTE-1',
            'content' => 'First note',
            'item_uuid' => 'aaa',
        ]);

        $note1 = Note::with('item')->find($note->id);
        $this->assertNull($note1->item);

        $note2 = NoteCi::with('item')->find($note->id);

        $this->assertSame($item->uuid, $note2->item->uuid); // AAA vs. AAA
        $this->assertNotSame($item->uuid, $note2->item_uuid); // AAA vs. aaa
    }

    /**
     * @test
     */
    public function testRetrieveChildren()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        $note1 = Note::create([
            'key' => 'NOTE-1',
            'content' => 'First note',
            'item_uuid' => 'aaa',
        ]);

        $note2 = Note::create([
            'key' => 'NOTE-2',
            'content' => 'Second note',
            'item_uuid' => 'AAA',
        ]);

        $item1 = Item::with('notes')->find($item->uuid);

        // assert that code is case-sensitive
        // only ONE related note will be attached to the item

        $this->assertCount(1, $item1->notes);
        $this->assertSame($item->uuid, $item1->notes[0]->item_uuid);

        // Assert that the code is case-insensitive
        // TWO related notes will be attached to the item

        $item2 = ItemCi::with('notes')->find($item->uuid);
        $this->assertCount(2, $item2->notes);
        $this->assertNotSame($item->uuid, $item2->notes[0]->item_uuid); // AAA vs. aaa
        $this->assertSame($item->uuid, $item2->notes[1]->item_uuid); // AAA vs. AAA
    }
}
