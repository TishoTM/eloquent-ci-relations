<?php
namespace TishoTM\Tests;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    public $table = 'todos';
    protected $guarded = [];
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    public function itemMeta()
    {
        return $this->hasOneThrough(Meta::class, Item::class, 'todo_key', 'item_uuid', 'key', 'uuid');
    }
}

class Item extends Model
{
    public $table = 'items';
    protected $guarded = ['id'];

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'items_tags', 'item_uuid', 'tag_id', 'uuid', 'id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'item_uuid', 'uuid');
    }

    public function keywords()
    {
        return $this->hasManyThrough(
            Keyword::class,
            Note::class,
            'item_uuid', // Foreign key on notes table...
            'note_key', // Foreign key on keywords table...
            'uuid', // Local key on items table...
            'key' // Local key on notes table...
        );
    }

    /**
     * Get all of the item's comments.
     */
    public function comments()
    {
        return $this->morphMany(
            Comment::class,
            'commentable',
            'commentable_type',
            'commentable_uuid',
            'uuid'
        );
    }    

    public function meta()
    {
        return $this->hasOne(Meta::class, 'item_uuid', 'uuid');
    }

    /**
     * Get all of the tags for the item.
     */
    public function morphed_tags()
    {
        return $this->morphToMany(
            Tag::class, // related
            'taggable', // name
            'taggables', // table
            'taggable_uuid', // foreignPivotKey
            'tag_key', // relatedPivotKey
            'uuid', // parentKey
            'key', // relatedKey
            $inverse = false);  // inverse
    }

    public function color()
    {
        return $this->morphOne(Color::class, 'colorable', 'colorable_type', 'colorable_key', 'uuid');
    }
}

class Note extends Model
{
    public $table = 'notes';
    protected $guarded = ['id'];

    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_uuid', 'uuid');
    }

    public function morphed_tags()
    {
        return $this->morphToMany(
            Tag::class, // related
            'taggable', // name
            'taggables', // table
            'taggable_uuid', // foreignPivotKey
            'tag_key', // relatedPivotKey
            'key', // parentKey
            'key', // relatedKey
            $inverse = false);  // inverse
    }

    /**
     * Get all of the notes' comments.
     */
    public function comments()
    {
        return $this->morphMany(
            Comment::class,
            'commentable',
            'commentable_type',
            'commentable_uuid',
            'key');
    }  

    public function color()
    {
        return $this->morphOne(Color::class, 'colorable', 'colorable_type', 'colorable_key', 'key');
    }
}

class Color extends Model
{
    public $table = 'colors';
    protected $guarded = ['id'];

    public function colorable()
    {
        return $this->morphTo(__FUNCTION__, 'colorable_type', 'colorable_key');
    }
}

class Keyword extends Model
{
    public $table = 'keywords';
    protected $guarded = ['id'];
}

class Comment extends Model
{
    public $table = 'comments';
    protected $guarded = ['id'];

    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo('commentable', 'commentable_type','commentable_uuid');
    }    
}

class Meta extends Model
{
    public $table = 'meta';
    protected $guarded = ['id'];
}

class Tag extends Model
{
    public $table = 'tags';
    protected $guarded = ['id']; 

    public function items()
    {
        return $this->belongsToMany(Item::class, 'items_tags', 'tag_id', 'item_uuid', 'id', 'uuid');
    }

    /**
     * Get all of the items that are assigned to this tag.
     */
    public function morphed_items()
    {
        return $this->morphedByMany(
            Item::class, // related
            'taggable', // name
            'taggables', // table
            'tag_key', // foreignPivotKey
            'taggable_uuid', // relatedPivotKey
            'key', // parentKey
            'uuid'); // relatedKey
    }
}

// Case-insensitive relationships

class TodoCi extends Todo
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;
}

class ItemCi extends Item
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;
}

class NoteCi extends Note
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;
}

class TagCi extends Tag
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;

    /**
     * Get all of the items that are assigned to this tag.
     */
    public function morphed_items()
    {
        return $this->morphedByMany(
            ItemCi::class, // related
            'taggable', // name
            'taggables', // table
            'tag_key', // foreignPivotKey
            'taggable_uuid', // relatedPivotKey
            'key', // parentKey
            'uuid'); // relatedKey
    }    
}

class CommentCi extends Comment
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;
}

class ColorCi extends Color
{
    use \TishoTM\Eloquent\Concerns\HasCiRelationships;
}
