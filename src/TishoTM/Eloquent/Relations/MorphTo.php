<?php
namespace TishoTM\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;

use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;

class MorphTo extends EloquentMorphTo
{
    use \TishoTM\Eloquent\Concerns\UsesDictionary;

    /**
     * Build a dictionary with the models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $key = $this->normalizeDictionaryKey($model->{$this->foreignKey});
                $this->dictionary[$model->{$this->morphType}][$key][] = $model;
            }
        }
    }

    /**
     * Match the results for a given type to their parents.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchToMorphParents($type, Collection $results)
    {
        // It is hacky but there is no reliable and efficient
        // way to determine the eloquent version installed
        // in version <= 5.7 name of the relation is set in the `relation` property
        // in version >= 5.8 the name of the relation is set in the `relationName` property
        // for Eloquent version     5.7 ?? 5.8
        $relationName = $this->relation ?? $this->relationName;
        foreach ($results as $result) {

            $ownerKey = ! is_null($this->ownerKey) ? $result->{$this->ownerKey} : $result->getKey();
            $key = $this->normalizeDictionaryKey($ownerKey);

            if (isset($this->dictionary[$type][$key])) {
                foreach ($this->dictionary[$type][$key] as $model) {
                    $model->setRelation($relationName, $result);
                }
            }
        }
    }
}
