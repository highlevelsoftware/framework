<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait RelatableTrait {

    /**
     * The relationship from child to parent.
     *
     * @var string
     */
    private $relationToParent;

    /**
     * Array of properties to share from the parent model onto our result to save extracting them again
     *
     * @var array
     */
    private $relationsToShareFromParent;

    /**
     * The relationship from child to parent via a collection.
     *
     * @var string
     */
    private $relationViaCollection;

    /**
     * Define the relationship of child to parent.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function relate($relationship)
    {
        $this->relationToParent = $relationship;

        return $this;
    }

    /**
     * Define the relationship of child to parent.
     *
     * @param  string  $relationship
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function relateWithin($relationship)
    {
        $this->relationViaCollection = $relationship;

        return $this;
    }

    /**
     * Associate relationships to extract from the parent.
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function share($relations)
    {
        if (is_string($relations)) $relations = func_get_args();

        $this->relationsToShareFromParent = $relations;

        return $this;
    }

    /**
     * Returns array of sharable properties.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return array
     */
    protected function getSharedRelations(Model $parent = null)
    {
        $relations = [];
        $parent = $parent ?: $this->parent;

        if ( ! empty($this->relationToParent))
        {
            $relations[$this->relationToParent] = $parent;
        }

        if ( ! empty($this->relationsToShareFromParent))
        {
            foreach($this->relationsToShareFromParent as $parentRelation => $childRelation)
            {
                if (is_numeric($parentRelation))
                {
                    $parentRelation = $childRelation;
                }

                if (isset($parent->$parentRelation))
                {
                    $relations[$childRelation] = $parent->$parentRelation;
                }
            }
        }

        return compact('parent', 'relations');
    }

    /**
     * Initialize the parent relationship on a set of models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function initRelationsOnCollection(Collection $models, Model $parent = null)
    {
        if (! $models->isEmpty())
        {
            $relations = $this->getSharedRelations($parent);

            if (isset($this->relationName) && !empty($this->relationName))
            {
                foreach($relations['parent']->getCollectionRelations($this->relationName) as $related)
                {
                    foreach($models as $index => $model)
                    {
                        if ($model->getKey() == $related->getKey())
                        {
                            $models[$index] = $related;

                            if (!array_key_exists('pivot', $related->getRelations()) && array_key_exists('pivot', $model->getRelations()))
                            {
                                $models[$index]->setRelation('pivot', $model->getRelation('pivot'));
                            }
                        }
                    }
                }
            }

            foreach ($models as $model)
            {
                $this->setRelationsOnModel($model, $relations);
            }
        }

        return $models;
    }

    /**
     * Initialize the relationships on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @param  \Illuminate\Database\Eloquent\Model|null  $parent
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function initRelationsOnModel(Model $model = null, Model $parent = null)
    {
        if ($model)
        {
            $this->setRelationsOnModel($model, $this->getSharedRelations($parent));
        }

        return $model;
    }

    /**
     * Initialize the relationships on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $shared
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function setRelationsOnModel(Model $model, array $shared)
    {
        foreach($shared['relations'] as $relation => $value)
        {
            $model->setInverseRelation($relation, $value);
        }

        if ( ! empty($this->relationViaCollection))
        {
            $model->setCollectionRelation($this->relationViaCollection, $shared['parent']);
        }

        return $model;
    }

    /**/

}