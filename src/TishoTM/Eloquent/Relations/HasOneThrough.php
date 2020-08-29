<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOneThrough as EloquentHasOneThrough;
use TishoTM\Eloquent\Concerns\MatchesOneOrManyThrough;

class HasOneThrough extends EloquentHasOneThrough
{
    use MatchesOneOrManyThrough;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->ciMatch($models, $results, $relation, true);
    }    
}
