<?php
class FirestoreDB
{
    public $baseUrl;
    public $token;
    public $query;

    /**
     * Initilize FirestoreDB
     *
     * @param string $baseUrl
     * @param string $token
     */
    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        $this->query = new Query($baseUrl, $token);
    }
    /**
     * Execute request
     *
     * @param string $url
     * @param array $options
     * @return string
     */
    private function executeRequest($url, $options)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $options['method'],
            CURLOPT_HTTPHEADER => $options['headers'],
        ]);

        if (isset($options['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        return json_encode(convertFirestoreJSON(json_decode($response, true)));
    }

    /**
     * Get document
     *
     * @param string $collectionPath
     * @param string $documentPath
     * @return string
     */
    public function getDocument($collectionPath, $documentPath)
    {
        $url = "{$this->baseUrl}/documents/{$collectionPath}/{$documentPath}";
        $requestOptions = [
            'method' => 'GET',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
            ],
        ];

        try {
            $response = $this->executeRequest($url, $requestOptions);
            return $response;
        } catch (Exception $error) {
            echo 'Error getting document: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Get collection
     *
     * @param string $collectionPath
     * @return string
     */
    public function getCollection($collectionPath)
    {
        $url = "{$this->baseUrl}/documents/{$collectionPath}";
        $requestOptions = [
            'method' => 'GET',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
            ],
        ];

        try {
            $response = $this->executeRequest($url, $requestOptions);
            return $response;
        } catch (Exception $error) {
            echo 'Error getting collection: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Get collection with limit
     *
     * @param string $collection
     * @param integer $limit
     * @return string
     */
    public function getCollection2($collection, $limit = 100)
    {
        $map = [];
        $requestOptions = [
            'method' => 'GET',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
            ],
        ];
        $url = "{$this->baseUrl}/documents/{$collection}?pageSize={$limit}";

        try {
            $response = $this->executeRequest($url, $requestOptions);
            return $response;
        } catch (Exception $error) {
            echo 'Error getting collection: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Create document
     *
     * @param string $collection
     * @param string $document
     * @param array $data
     * @return string
     */
    public function createDocument($collection, $document, $data)
    {
        $url = "{$this->baseUrl}/documents/{$collection}/{$document}";
        $requestOptions = [
            'method' => 'POST',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            'body' => json_encode(['fields' => convertFieldsToFirestoreJSON($data)]),
        ];

        try {
            return $this->executeRequest($url, $requestOptions);
        } catch (Exception $error) {
            echo 'Error creating document: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Update document
     *
     * @param string $collection
     * @param string $document
     * @param array $fieldsToUpdate
     * @return string
     */
    public function updateDocument($collection, $document, $fieldsToUpdate)
    {
        $fieldPaths = array_map(function ($field) {
            return 'updateMask.fieldPaths=' . $field;
        }, array_keys($fieldsToUpdate));

        $fieldPathsQuery = implode('&', $fieldPaths);
        $url = "{$this->baseUrl}/documents/{$collection}/{$document}?currentDocument.exists=true&{$fieldPathsQuery}&alt=json";

        $requestOptions = [
            'method' => 'PATCH',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            'body' => json_encode(['fields' => convertFieldsToFirestoreJSON($fieldsToUpdate)]),
        ];

        try {
            $response = $this->executeRequest($url, $requestOptions);
            return $response;
        } catch (Exception $error) {
            echo 'Error updating document: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Delete document
     *
     * @param string $collection
     * @param string $document
     * @return string
     */
    public function deleteDocument($collection, $document)
    {
        $url = "{$this->baseUrl}/documents/{$collection}/{$document}";
        $requestOptions = [
            'method' => 'DELETE',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
            ],
        ];

        try {
            return $this->executeRequest($url, $requestOptions);
        } catch (Exception $error) {
            echo 'Error deleting document: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Delete collection
     *
     * @param string $collection
     * @return string
     */
    public function deleteCollection($collection)
    {
        $url = "{$this->baseUrl}/documents/{$collection}";
        $requestOptions = [
            'method' => 'DELETE',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
            ],
        ];

        try {
            return $this->executeRequest($url, $requestOptions);
        } catch (Exception $error) {
            echo 'Error deleting collection: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Delete document fields
     *
     * @param string $collection
     * @param string $document
     * @param array $fieldsToDelete
     * @return string
     */
    public function deleteDocumentFields($collection, $document, $fieldsToDelete)
    {
        $url = "{$this->baseUrl}/documents/{$collection}/{$document}";
        $requestOptions = [
            'method' => 'PATCH',
            'headers' => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            'body' => json_encode(['fields' => convertFieldsToFirestoreJSON($fieldsToDelete)]),
        ];

        try {
            return $this->executeRequest($url, $requestOptions);
        } catch (Exception $error) {
            echo 'Error deleting document fields: ' . $error->getMessage();
            throw $error;
        }
    }
}