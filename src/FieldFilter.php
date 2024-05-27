<?php

class FieldFilter
{
    private array $filters = [];
    // Operators
    private string $LESS_THAN = 'LESS_THAN';
    private string $LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
    private string $GREATER_THAN = 'GREATER_THAN';
    private string $GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    private string $EQUAL = 'EQUAL';
    private string $NOT_EQUAL = 'NOT_EQUAL';
    private string $ARRAY_CONTAINS = 'ARRAY_CONTAINS';
    private string $ARRAY_CONTAINS_ANY = 'ARRAY_CONTAINS_ANY';
    private string $NOT_IN = 'NOT_IN';
    private string $IN = 'IN';

    /**
     * Field is included in the query
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function isIn($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->IN,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is not included in the query
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function notIn($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->NOT_IN,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field contains any of the values
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function arrayContainsAny($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->ARRAY_CONTAINS_ANY,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field contains all of the values
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function arrayContains($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->ARRAY_CONTAINS,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is not equal to the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function notEqual($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->NOT_EQUAL,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is equal to the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function equalTo($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->EQUAL,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is less than the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function lessThan($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->LESS_THAN,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is greater than the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function greaterThan($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->GREATER_THAN,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is less than or equal to the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function greaterThanOrEqualTo($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->GREATER_THAN_OR_EQUAL,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Field is greater than or equal to the value
     *
     * @param string $field
     * @param mixed $value
     * @return FieldFilter
     */
    public function lessThanOrEqualTo($field, $value)
    {
        $filter = [
            'field' => ['fieldPath' => $field],
            'op' => $this->LESS_THAN_OR_EQUAL,
            'value' => convertToFirestoreValue($value)
        ];
        $this->filters[] = ['fieldFilter' => $filter];
        return $this;
    }

    /**
     * Get the complete filter
     *\
     * @return array
     */
    public function complete()
    {
        if (count($this->filters) >= 2) {
            return $this->filters;
        } else {
            return $this->filters[0];
        }
    }
}