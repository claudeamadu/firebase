<?php

class UnaryFilter
{
    private $filter;
    public $IN;
    public $CONTAINS;
    public $IS_NOT_NAN;
    public $IS_NOT_NULL;

    /**
     * Creates a new unary filter
     *
     */
    public function __construct()
    {
        $this->filter = [];
        $this->IN = 'IN';
        $this->CONTAINS = 'CONTAINS';
        $this->IS_NOT_NAN = 'IS_NOT_NAN';
        $this->IS_NOT_NULL = 'IS_NOT_NULL';
    }

    /**
     * Set the operator
     *
     * @param string $operator
     * @return UnaryFilter
     */
    public function setOperator($operator)
    {
        $this->filter['op'] = $operator;
        return $this;
    }

    /**
     * Set the field
     *
     * @param string $field
     * @return UnaryFilter
     */
    public function setField($field)
    {
        $this->filter['field'] = ['fieldPath' => $field];
        return $this;
    }

    /**
     * Get the complete filter
     *
     * @return array
     */
    public function complete()
    {
        return $this->filter;
    }
}