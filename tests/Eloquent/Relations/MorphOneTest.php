<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Note;
use TishoTM\Tests\NoteCi;
use TishoTM\Tests\Color;
use TishoTM\Tests\ColorCi;

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @runTestsInSeparateProcesses
 */
class MorphOneTest extends TestBase
{
    /**
     * Set up the DB tables.
     */
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

        $connection->statement('CREATE TABLE colors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hex TEXT,
            colorable_key TEXT COLLATE NOCASE,
            colorable_type TEXT COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');
    }

    /**
     * Test MorphOne.
     * @test
     */
    public function testMorphOne()
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

        $colorableItem = Color::create([
            'hex' => '#FFFFFF',
            'colorable_key' => 'aaa',       // different than AAA
            'colorable_type' => 'items'
        ]);
        $colorableNote = Color::create([
            'hex' => '#FFFF00',
            'colorable_key' => 'note-1',    // different than NOTE-1
            'colorable_type' => 'notes'
        ]);

        // case-sensitive models
        Relation::morphMap([
            'items' => 'TishoTM\Tests\Item',
            'notes' => 'TishoTM\Tests\Note',
        ]);

        $item1 = Item::with('color')->find('AAA');
        $this->assertNull($item1->color);

        $note1 = Note::with('color')->find('NOTE-1');
        $this->assertNull($note1->color);

        $colorableCs1 = Color::with('colorable')->find($colorableItem->id);
        $this->assertNull($colorableCs1->colorable);

        $colorableCs2 = Color::with('colorable')->find($colorableNote->id);
        $this->assertNull($colorableCs2->colorable);

        // case-insensitive models
        Relation::morphMap([
            'items' => 'TishoTM\Tests\ItemCi',
            'notes' => 'TishoTM\Tests\NoteCi',
        ]);

        $item2 = ItemCi::with('color')->find('AAA');
        $this->assertNotNull($item2->color);
        $this->assertSame($item2->color->id, $colorableItem->id);

        $note2 = NoteCi::with('color')->find('NOTE-1');
        $this->assertNotNull($note2->color);
        $this->assertSame($note2->color->id, $colorableNote->id);

        $colorableCiForItem = ColorCi::with('colorable')->find($colorableItem->id);
        $this->assertNotNull($colorableCiForItem->colorable);
        // make sure we get the original record from the DB as it is.
        $this->assertSame($colorableCiForItem->colorable->uuid, 'AAA'); // AAA vs. aaa
        $this->assertNotSame($colorableCiForItem->colorable->uuid, 'aaa'); // AAA vs. aaa

        $colorableCiForNote = ColorCi::with('colorable')->find($colorableNote->id);
        $this->assertNotNull($colorableCiForNote->colorable);
        // make sure we get the original record from the DB as it is.
        $this->assertSame($colorableCiForNote->colorable->key, 'NOTE-1'); // NOTE-1 vs. note-1
        $this->assertNotSame($colorableCiForNote->colorable->key, 'note-1'); // NOTE-1 vs. note-1
    }
}
