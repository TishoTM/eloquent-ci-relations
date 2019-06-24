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
        foreach ($results as $result) {
            $key = $this->normalizeDictionaryKey($result->getKey());
            if (isset($this->dictionary[$type][$key])) {
                foreach ($this->dictionary[$type][$key] as $model) {
                    $model->setRelation($this->relation, $result);
                }
            }
        }
    }
}
