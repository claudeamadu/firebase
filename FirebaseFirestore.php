<?php

class FirebaseFirestore
{
    public $Token;
    public $Database;
    public $ProjectID;
    public $baseUrl;
    public $db;

    /**
     * Initilize FirebaseFirestore
     *
     * @param string $Token
     * @param string $Database
     * @param string $ProjectID
     */
    public function __construct($Token, $Database, $ProjectID)
    {
        $this->Token = $Token;
        $this->Database = $Database;
        $this->ProjectID = $ProjectID;
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->ProjectID}/databases/{$this->Database}";
        $this->db = new FirestoreDB($this->baseUrl, $this->Token);
    }
}
