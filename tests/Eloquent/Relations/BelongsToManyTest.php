<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Tag;
use TishoTM\Tests\TagCi;

/**
 * @runTestsInSeparateProcesses
 */
class BelongsToManyTest extends TestBase
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

        $connection->statement('CREATE TABLE tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE items_tags (
            item_uuid TEXT COLLATE NOCASE,
            tag_id INTEGER
        )');
    }

    /**
     * Test BelongsToMany
     * @test
     */
    public function testRetrieveRelatedRecordsWithStringKey()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        $tag1 = Tag::create([
            'title' => 'tag 1',
        ]);

        $tag2 = Tag::create([
            'title' => 'tag 2',
        ]);

        $this->db->table('items_tags')->insert([
            'item_uuid' => 'AAA',
            'tag_id' => $tag1->id,
        ]);

        $this->db->table('items_tags')->insert([
            'item_uuid' => 'aaa',
            'tag_id' => $tag2->id,
        ]);

        // case-sensitive
        $item1 = Item::with('tags')->find($item->uuid);
        $this->assertCount(1, $item1->tags);
        $this->assertEquals($item1->tags[0]->id, $tag1->id);
        $this->assertSame($item->uuid, $item1->tags[0]->pivot->item_uuid);

        // case-insensitive
        $item2 = ItemCi::with('tags')->find($item->uuid);
        $this->assertCount(2, $item2->tags);
        
        $this->assertSame($tag1->id, $item2->tags[0]->id);
        $this->assertSame($item->uuid, $item2->tags[0]->pivot->item_uuid);
        
        $this->assertSame($tag2->id, $item2->tags[1]->id);
        $this->assertNotSame($item->uuid, $item2->tags[1]->pivot->item_uuid); // AAA vs. aaa
        $this->assertSame($item->uuid, strtoupper($item2->tags[1]->pivot->item_uuid)); // AAA vs. AAA
    }

    /**
     * @test
     */
    public function testRetrieveRelatedRecordsWithIntegerKey()
    {
        $tag = Tag::create([
            'title' => 'tag 1',
        ]);

        $item1 = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for AAA',
        ]);

        $item2 = Item::create([
            'uuid' => 'BBB',
            'title' => 'Item 2',
            'description' => 'Description for BBB',
        ]);

        $this->db->table('items_tags')->insert([
            'item_uuid' => 'aaa',
            'tag_id' => $tag->id,
        ]);

        $this->db->table('items_tags')->insert([
            'item_uuid' => 'bbb',
            'tag_id' => $tag->id,
        ]);

        // the dictionary mapping key is the tag ID
        $tag1 = Tag::with('items')->find($tag->id);
        $this->assertCount(2, $tag1->items);

        $this->assertSame($item1->uuid, strtoupper($tag1->items[0]->uuid));
        $this->assertNotSame($item1->uuid, $tag1->items[0]->pivot->item_uuid); // AAA vs. aaa
        $this->assertSame($item2->uuid, $tag1->items[1]->uuid);
        $this->assertNotSame($item2->uuid, $tag1->items[1]->pivot->item_uuid); // BBB vs. bbb

        // the dictionary mapping key is the tag ID
        $tag2 = TagCi::with('items')->find($tag->id);
        $this->assertCount(2, $tag2->items);
        $this->assertSame($item1->uuid, strtoupper($tag2->items[0]->uuid));
        $this->assertNotSame($item1->uuid, $tag2->items[0]->pivot->item_uuid);  // AAA vs. aaa
        $this->assertSame($item2->uuid, $tag2->items[1]->uuid);
        $this->assertNotSame($item2->uuid, $tag2->items[1]->pivot->item_uuid);  // BBB vs. bbb
    }
}
