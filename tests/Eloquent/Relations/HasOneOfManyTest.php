<?php
namespace TishoTM\Tests\Eloquent\Relations;

use TishoTM\Tests\Activity;
use TishoTM\Tests\Eloquent\TestBase;
use TishoTM\Tests\Todo;
use TishoTM\Tests\TodoCi;

/**
 * @runTestsInSeparateProcesses
 */
class HasOneOfManyTest extends TestBase
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

        $connection->statement('CREATE TABLE activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            todo_key TEXT NOT NULL COLLATE NOCASE,
            description TEXT,
            created_at DATETIME,
            updated_at DATETIME
        )');
    }    

    /**
     * Test HasOne->ofMany
     * @test 
     */
    public function testHasOneOfMany()
    {
        if (!class_exists(\Illuminate\Database\Eloquent\Relations\HasOne::class) ||
            !method_exists(\Illuminate\Database\Eloquent\Relations\HasOne::class, 'ofMany')) {
            $this->markTestSkipped("Relation HasOne of Many is not implemented");
        }

        Todo::create([
            'key' => 'todo-key',
            'title' => 'Todo list',
        ]);

        Activity::create([
            'id' => 1,
            'todo_key' => 'TODO-KEY',
            'description' => 'Activity One',
        ]);

        Activity::create([
            'id' => 2,
            'todo_key' => 'todo-key',
            'description' => 'Activity Two',
        ]);

        Activity::create([
            'id' => 3,
            'todo_key' => 'TODO-key',
            'description' => 'Activity Three',
        ]);

        // The hasOne->ofMany() relationship uses a sub-query to find for ex. MAX(id)
        // the sub-query is grouped by the PARENT model KEY
        // the grouped records based on the DB type could return different parent KEY value
        // i.e
        // mysql 5.7 shows that the first record from the group is returned - "TODO-KEY"
        // sqlite shows that the last record from the group is returned - "TODO-key"

        // Since the PARENT-RELATED mapping occurs on the PHP side, then the relation would be NULL.
        $todo = Todo::with('latestActivity')->find('todo-key');
        $this->assertNull($todo->latestActivity);

        // With Case-Insensitive mapping - the record is properly set!
        $todoCi = TodoCi::with('latestActivity')->find('todo-key');
        $this->assertNotNull($todoCi->latestActivity);
        $this->assertEquals('Activity Three', $todoCi->latestActivity->description);
        $this->assertEquals(3, $todoCi->latestActivity->id);
    }
}
