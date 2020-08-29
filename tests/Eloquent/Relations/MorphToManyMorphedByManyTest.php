<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Note;
use TishoTM\Tests\NoteCi;
use TishoTM\Tests\Tag;
use TishoTM\Tests\TagCi;

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @runTestsInSeparateProcesses
 */
class MorphToManyMorphedByManyTest extends TestBase
{
    protected $data = [];

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

        $connection->statement('CREATE TABLE tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT COLLATE NOCASE,
            key TEXT COLLATE NOCASE,
            created_at DATETIME,
            updated_at DATETIME
        )');

        $connection->statement('CREATE TABLE taggables (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tag_key TEXT COLLATE NOCASE,
            taggable_uuid TEXT COLLATE NOCASE,
            taggable_type TEXT
        )');

        $this->seedData();
    }

    /**
     * Seed the data.
     */
    protected function seedData()
    {
        $this->data['item'] = $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for AAA',
        ]);

        $this->data['tag1'] = $tag1 = Tag::create([
            'title' => 'tag 1',
            'key' => 'tag-1',
        ]);

        $this->data['tag2'] = $tag2 = Tag::create([
            'title' => 'tag 2',
            'key' => 'tag-2',
        ]);

        $this->data['note'] = $note = Note::create([
            'key' => 'NOTE-1',
            'content' => 'First note',
            'item_uuid' => 'AAA',
        ]);

        $this->db->table('taggables')->insert([
            'tag_key' => strtoupper($tag1->key),
            'taggable_uuid' => 'aaa',
            'taggable_type' => 'items',
        ]);

        $this->db->table('taggables')->insert([
            'tag_key' => $tag1->key,
            'taggable_uuid' => 'note-1',
            'taggable_type' => 'notes',
        ]);

        $this->db->table('taggables')->insert([
            'tag_key' => $tag2->key,
            'taggable_uuid' => 'AAA',
            'taggable_type' => 'items',
        ]);
    }

    /**
     * Test MorphToMany
     * @test
     */
    public function testRetrieveMorphToMany()
    {
        $item = $this->data['item'];
        $tag1 = $this->data['tag1'];
        $tag2 = $this->data['tag2'];

        $item->morphed_tags()->morphMap([
            'items' => Item::class,
            'notes' => Note::class,
        ]);

        // Assert the case-sensitivity.
        $AAA = Item::with('morphed_tags')->find($item->uuid);

        $this->assertCount(1, $AAA->morphed_tags);
        $this->assertSame($item->uuid, $AAA->morphed_tags[0]->pivot->taggable_uuid);
        $this->assertEquals($tag2->key, $AAA->morphed_tags[0]->pivot->tag_key);

        $item->morphed_tags()->morphMap([
            'items' => ItemCi::class,
            'notes' => NoteCi::class,
        ]);

        // Assert the case-insensitivity.
        $AAAci = ItemCi::with('morphed_tags')->find($item->uuid);

        $this->assertCount(2, $AAAci->morphed_tags);
        $this->assertNotSame($item->uuid, $AAAci->morphed_tags[0]->pivot->taggable_uuid); // AAA vs. aaa
        $this->assertSame($item->uuid, strtoupper($AAAci->morphed_tags[0]->pivot->taggable_uuid));
        $this->assertNotSame($tag1->key, $AAAci->morphed_tags[0]->pivot->tag_key); // tag-1 vs TAG-1
        $this->assertSame($tag1->key, strtolower($AAAci->morphed_tags[0]->pivot->tag_key));

        $this->assertSame($item->uuid, $AAAci->morphed_tags[1]->pivot->taggable_uuid); // AAA vs. AAA
        $this->assertEquals($tag2->key, $AAAci->morphed_tags[1]->pivot->tag_key);
    }

    /**
     * Test MorphedByMany
     * @test
     */
    public function testRetrieveMorphedByMany()
    {
        $item = $this->data['item'];
        $tag1 = $this->data['tag1'];
        $tag2 = $this->data['tag2'];

        // case-sensitive
        $tag1->morphed_items()->morphMap([
            'items' => Item::class,
            'notes' => Note::class,
        ]);

        $tagOne = Tag::with('morphed_items')->find($tag1->id);

        // taggables.tag_key (TAG-1) is uppercase vs tags.tag_key is lowercase (tag-1)
        $this->assertCount(0, $tagOne->morphed_items);

        // case-insensitive
        $tag1->morphed_items()->morphMap([
            'items' => ItemCi::class,
            'notes' => NoteCi::class,
        ]);

        $tagOneCi = TagCi::with('morphed_items')->find($tag1->id);

        $this->assertCount(1, $tagOneCi->morphed_items);
        $this->assertSame($item->uuid, $tagOneCi->morphed_items[0]->uuid);
        $this->assertSame($item->uuid, strtoupper($tagOneCi->morphed_items[0]->pivot->taggable_uuid));
    }
}
