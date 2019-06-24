<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;

class MorphToMany extends EloquentMorphToMany
{
    use \TishoTM\Eloquent\Concerns\MatchesBelongingToMany;
}
