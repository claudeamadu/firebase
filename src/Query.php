<?php

class Query
{
    private $baseUrl;
    private $token;
    private $queryMap;
    private $fieldsList;
    private $orderList;
    private $subCollection;

    /**
     * @param string $baseUrl
     * @param string $token
     */
    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        $this->queryMap = [];
        $this->fieldsList = [];
        $this->orderList = [];
        $this->subCollection = null;
    }

    /**
     * Run query
     *
     * @return array
     */
    public function run()
    {
        $url = "{$this->baseUrl}/documents/:runQuery";
        $requestOptions = [
            'method' => 'POST',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            'body' => json_encode($this->complete()),
        ];

        // Initialize cURL session
        $curl = curl_init($url);

        // Set cURL options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestOptions['headers']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestOptions['body']);

        // Execute cURL request
        $response = curl_exec($curl);

        // Check for errors and handle response
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('Error running query: ' . $error);
        }

        // Close cURL session
        curl_close($curl);

        // Handle the response data accordingly        
        return json_encode(convertFirestoreJSON(json_decode($response, true)));
    }

    /**
     * Add single field
     *
     * @param string $field
     * @return array
     */
    public function addField($field)
    {
        $this->fieldsList[] = ['fieldPath' => $field];
        return $this;
    }

    /**
     * Add multiple fields
     *
     * @param array $fields
     * @return Query
     */
    public function addFields($fields)
    {
        foreach ($fields as $field) {
            $this->fieldsList[] = ['fieldPath' => $field];
        }
        return $this;
    }

    /**
     * Select fields
     *
     * @return Query
     */
    public function selectFields()
    {
        $selectMap = [];
        if (count($this->fieldsList) > 0) {
            $selectMap['fields'] = $this->fieldsList;
        }
        if (count($this->orderList) > 0) {
            $this->queryMap['orderBy'] = $this->orderList;
        }
        if (!empty($selectMap)) {
            $this->queryMap['select'] = $selectMap;
        }
        return $this;
    }

    /**
     * Order by
     *
     * @param string $field
     * @param string $direction
     * @return Query
     */
    public function orderBy($field, $direction)
    {
        $this->orderList[] = [
            'field' => ['fieldPath' => $field],
            'direction' => $direction
        ];
        return $this;
    }

    /**
     * From
     *
     * @param string $collectionPath
     * @return Query
     */
    public function from($collectionPath)
    {
        $this->selectFields();
        $fromList = [['collectionId' => $collectionPath]];
        $this->queryMap['from'] = $fromList;
        return $this;
    }

    /**
     * Start at
     *
     * @param array $values
     * @return Query
     */
    public function startAt($values)
    {
        $start = [
            'values' => array_map([$this, 'convertToFirestoreValue'], $values)
        ];
        $this->queryMap['startAt'] = $start;
        return $this;
    }

    /**
     * End at
     *
     * @param array $values
     * @return Query
     */
    public function endAt($values)
    {
        $end = [
            'values' => array_map([$this, 'convertToFirestoreValue'], $values)
        ];
        $this->queryMap['endAt'] = $end;
        return $this;
    }

    /**
     * Offset
     *
     * @param int $position
     * @return Query
     */
    public function offset($position)
    {
        $this->queryMap['offset'] = $position;
        return $this;
    }

    /**
     * Limit data
     *
     * @param int $limit
     * @return Query
     */
    public function limit($limitBy)
    {
        $this->queryMap['limit'] = $limitBy;
        return $this;
    }

    /**
     * Adds a filter
     *
     * @param CompositeFilter $compositeFilter
     * @return Query
     */
    public function where(CompositeFilter $compositeFilter): Query
    {
        $this->queryMap['where'] = ['compositeFilter' => $compositeFilter->complete()];
        return $this;
    }

    /**
     * Adds a filter
     *
     * @param FieldFilter $fieldFilter
     * @return Query
     */
    public function where2(FieldFilter $fieldFilter): Query
    {
        $this->queryMap['where'] = $fieldFilter->complete();
        return $this;
    }

    /**
     * Adds a filter
     *
     * @param UnaryFilter $unaryFilter
     * @return Query
     */
    public function where3(UnaryFilter $unaryFilter): Query
    {
        $this->queryMap['where'] = ['unaryFilter' => $unaryFilter->complete()];
        return $this;
    }

    /**
     * Returns query
     *
     * @return array
     */
    public function complete()
    {
        if (!empty($this->queryMap)) {
            return ['structuredQuery' => $this->queryMap];
        } else {
            return [];
        }
    }
}