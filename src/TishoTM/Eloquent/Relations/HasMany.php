<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;

class HasMany extends EloquentHasMany
{
    use \TishoTM\Eloquent\Concerns\MatchesOneOrMany;
}
