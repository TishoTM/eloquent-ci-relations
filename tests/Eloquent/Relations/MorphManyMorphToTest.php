<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Item;
use TishoTM\Tests\ItemCi;
use TishoTM\Tests\Note;
use TishoTM\Tests\NoteCi;
use TishoTM\Tests\Comment;
use TishoTM\Tests\CommentCi;

class MorphManyMorphToTest extends TestBase
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

        $connection->statement('CREATE TABLE comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT,
            commentable_uuid TEXT NOT NULL COLLATE NOCASE,
            commentable_type TEXT NOT NULL,
            created_at DATETIME,
            updated_at DATETIME
        )');        
    }

    public function testRetrieveMorphTo()
    {
        $item = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for aaa',
        ]);

        $firstComment = Comment::create([
            'commentable_uuid' => 'aaa',
            'commentable_type' => 'items',
            'content' => 'second comment',
        ]);

        // case-sensitive
        $item->comments()->morphMap([
            'items' => 'TishoTM\Tests\Item',
        ]);

        $comment = Comment::with('commentable')->find($firstComment->id);
        $this->assertNull($comment->commentable);

        // case-insensitive
        $item->comments()->morphMap([
            'items' => 'TishoTM\Tests\ItemCi',
        ]);

        $commentCi = CommentCi::with('commentable')->find($firstComment->id);
        $this->assertNotNull($commentCi->commentable);
    }

    public function testRetrieveMorphMany()
    {
        $AAA = Item::create([
            'uuid' => 'AAA',
            'title' => 'Item 1',
            'description' => 'Description for AAA',
        ]);

        $firstComment = Comment::create([
            'commentable_uuid' => 'aaa',
            'commentable_type' => 'items',
            'content' => 'first comment',
        ]);

        $secondComment = Comment::create([
            'commentable_uuid' => 'AAA',
            'commentable_type' => 'items',
            'content' => 'second comment',
        ]);

        // case-sensitive
        $AAA->comments()->morphMap([
            'items' => 'TishoTM\Tests\Item',
            'notes' => 'TishoTM\Tests\Note',
        ]);

        $item = Item::with('comments')->find($AAA->uuid);
        $this->assertCount(1, $item->comments);
        $this->assertSame($AAA->uuid, $item->comments[0]->commentable_uuid);

        // case-insensitive
        $AAA->comments()->morphMap([
            'items' => 'TishoTM\Tests\ItemCi',
            'notes' => 'TishoTM\Tests\NoteCi',
        ]);

        $itemCi = ItemCi::with('comments')->find($AAA->uuid);
        $this->assertCount(2, $itemCi->comments);
        $this->assertNotSame($AAA->uuid, $itemCi->comments[0]->commentable_uuid);  // AAA vs. aaa
        $this->assertSame($AAA->uuid, strtoupper($itemCi->comments[0]->commentable_uuid));
        $this->assertSame($AAA->uuid, $itemCi->comments[1]->commentable_uuid);
    }
}
