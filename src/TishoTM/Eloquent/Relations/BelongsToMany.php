<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;

class BelongsToMany extends EloquentBelongsToMany
{
    use \TishoTM\Eloquent\Concerns\MatchesBelongingToMany;
}
