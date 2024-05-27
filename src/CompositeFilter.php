<?php

class CompositeFilter
{
    private array $compositeMap = [];
    /**
     * Creates a new composite filter
     *
     */
    public function __construct()
    {
        $this->compositeMap = [];
    }


    /**
     * Add a filter
     *
     * @param FieldFilter $filter
     * @param string $operator
     * @return CompositeFilter
     */
    public function filters($filter, $operator = null)
    {
        if (count($this->compositeMap) > 0) {
            echo 'CompositeFilter already set';
        } else {
            $this->compositeMap['op'] = $operator ?: 'AND';
            $this->compositeMap['filters'] = $filter->complete();
        }
        return $this;
    }

    /**
     * Get the complete filter
     *
     * @return array
     */
    public function complete()
    {
        if (count($this->compositeMap) > 0) {
            return $this->compositeMap;
        } else {
            return [];
        }
    }
}
