<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;

class MorphOne extends EloquentMorphOne
{
    use \TishoTM\Eloquent\Concerns\MatchesOneOrMany;
}
