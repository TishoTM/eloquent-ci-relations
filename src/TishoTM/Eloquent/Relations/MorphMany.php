<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;

class MorphMany extends EloquentMorphMany
{
    use \TishoTM\Eloquent\Concerns\MatchesOneOrMany;
}
