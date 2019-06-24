<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;

class HasOne extends EloquentHasOne
{
    use \TishoTM\Eloquent\Concerns\MatchesOneOrMany;
}
