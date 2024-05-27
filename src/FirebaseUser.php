<?php

/**
 * FirebaseUser
 */
class FirebaseUser
{
    public $user;
    public $displayName;
    public $email;
    public $profilePicture;

    /**
     * Initilize FirebaseUser
     *
     * @param array $user
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->displayName = $user['displayName'] ?? null;
        $this->email = $user['email'] ?? null;
        $this->profilePicture = $user['profilePicture'] ?? null;
    }
}
