<?php
namespace TishoTM\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Collection;

trait MatchesOneOrManyThrough
{
    use UsesDictionary;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @param  bool  $single - either single or many through
     * @return array
     */
    private function ciMatch(array $models, Collection $results, $relation, $single = true)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $this->normalizeDictionaryKey($model->getAttribute($this->localKey))])) {
                
                $value = $dictionary[$key];

                $relationItems = $single === true ?
                    reset($value) :
                    $this->related->newCollection($value);

                $model->setRelation(
                    $relation, $relationItems
                );
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $normalizedKey = $this->ciNormalizedDictionaryKey($result);            
            $dictionary[$normalizedKey][] = $result;
        }

        return $dictionary;
    }

    /**
     * @param  Model  $result
     * @return string
     */
    private function ciNormalizedDictionaryKey($result)
    {
        // It is hacky but there is no reliable and efficient
        // way to determine the eloquent version installed
        // for Eloquent version       >=5.8 ?? <=5.7
        $key = $result->laravel_through_key ?? $result->{$this->firstKey};
        return $this->normalizeDictionaryKey($key);
    }    
}
