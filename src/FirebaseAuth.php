<?php
/**
 * Firebase Authentication
 */
class FirebaseAuth
{
    private $apiKey;
    private $baseUrl;
    private $sessionKeys;

    /**
     * Initialize Firebase Authentication
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = 'https://identitytoolkit.googleapis.com/v1/accounts';
        $this->sessionKeys = [
            'accessToken' => 'firebase_access_token',
            'expiresIn' => 'firebase_token_expires',
            'refreshToken' => 'firebase_refresh_token',
            'signedIn' => 'firebase_signed_in',
            'user' => 'firebase_user',
        ];
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set session item
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function setSessionItem(string $key, string $value): void
    {
        $_SESSION[$this->sessionKeys[$key]] = $value;
    }

    private function getSessionItem($key)
    {
        return $_SESSION[$this->sessionKeys[$key]] ?? null;
    }

    /**
     * Refresh token
     *
     * @param string $refreshToken
     * @return mixed
     */
    public function refreshToken(string $refreshToken): mixed
    {
        $url = "https://securetoken.googleapis.com/v1/token?key={$this->apiKey}";
        $requestData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if ($data) {
                $this->setSessionItem('accessToken', $data['access_token']);
                $this->setSessionItem('expiresIn', $data['expires_in']);
                $this->setSessionItem('refreshToken', $data['refresh_token']);
                return $data;
            } else {
                throw new Exception('Error refreshing token');
            }
        } catch (Exception $error) {
            echo 'Error refreshing token: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Check if token is expired
     *
     * @return boolean
     */
    public function isTokenExpired()
    {
        $token = $this->getSessionItem('expiresIn');
        if (!$token) {
            return true; // Token or expiration time not provided, consider it expired
        }

        $currentTime = time(); // Current time in seconds
        return $token < $currentTime;
    }

    /**
     * Check if user is signed in
     *
     * @return boolean
     */
    public function isUserSignedIn()
    {
        if ($this->isTokenExpired()) {
            return false;
        }
        return $this->getSessionItem('signedIn');
    }
    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->getSessionItem('accessToken');
    }

    /**
     * Retrieves the refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->getSessionItem('refreshToken');
    }

    /**
     * Retrieves the current user from the session and returns a new FirebaseUser object.
     *
     * @return FirebaseUser
     */
    public function currentUser()
    {
        $user = json_decode($this->getSessionItem('user'), true);
        return new FirebaseUser($user); // You need to define FirebaseUser class accordingly
    }


    /**
     * Sign up
     *
     * @param string $email
     * @param string $password
     * @return mixed
     */
    public function signUp($email, $password)
    {
        $signUpUrl = "{$this->baseUrl}:signUp?key={$this->apiKey}";
        $requestData = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true,
        ];

        try {
            $ch = curl_init($signUpUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error signing up: ' . $error->getMessage();
            throw $error;
        }
    }


    /**
     * Sign in
     *
     * @param datatype $email
     * @param datatype $password
     * @return mixed
     */
    public function signIn($email, $password)
    {
        $signInUrl = "{$this->baseUrl}:signInWithPassword?key={$this->apiKey}";
        $requestData = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true,
        ];

        try {
            $ch = curl_init($signInUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if ($data) {
                $this->setSessionItem('signedIn', true);
                $this->setSessionItem('accessToken', $data['idToken']);
                $this->setSessionItem('refreshToken', $data['refreshToken']);
                $user = [
                    'displayName' => $data['displayName'],
                    'profilePicture' => $data['profilePicture'],
                    'email' => $data['email']
                ];
                $this->setSessionItem('user', json_encode($user));
                return $data;
            } else {
                throw new Exception('Error signing in');
            }
        } catch (Exception $error) {
            echo 'Error signing in: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Sign out
     *
     * @return void
     */
    public function signOut()
    {
        unset($_SESSION[$this->sessionKeys['signedIn']]);
        unset($_SESSION[$this->sessionKeys['accessToken']]);
        unset($_SESSION[$this->sessionKeys['refreshToken']]);
        unset($_SESSION[$this->sessionKeys['expiresIn']]);
        echo 'Signed Out: true';
    }
    /**
     * Send email verification
     *
     * @param string $idToken
     * @return void
     */
    public function sendEmailVerification($idToken)
    {
        $url = "{$this->baseUrl}:sendOobCode?key={$this->apiKey}";
        $requestData = [
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $idToken,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error sending email verification: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Sign in anonymously
     *
     * @return void
     */
    public function signInAnonymously()
    {
        $url = "{$this->baseUrl}:signUp?key={$this->apiKey}";
        $requestData = [
            'returnSecureToken' => true,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if ($data) {
                $this->setSessionItem('signedIn', true);
                $this->setSessionItem('accessToken', $data['idToken']);
                $this->setSessionItem('expiresIn', $data['expiresIn']);
                $this->setSessionItem('refreshToken', $data['refreshToken']);
                return true;
            } else {
                throw new Exception('Error signing in anonymously');
            }
        } catch (Exception $error) {
            echo 'Error signing in anonymously: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Send password reset email
     *
     * @param string $email
     * @return void
     */
    public function sendPasswordResetEmail($email)
    {
        $url = "{$this->baseUrl}:sendOobCode?key={$this->apiKey}";
        $requestData = [
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error sending password reset email: ' . $error->getMessage();
            throw $error;
        }
    }
    /**
     * Confirm password reset
     *
     * @param string $oobCode 
     * @param string $newPassword 
     * @return array
     */
    public function confirmPasswordReset($oobCode, $newPassword)
    {
        $url = "{$this->baseUrl}:resetPassword?key={$this->apiKey}";
        $requestData = [
            'oobCode' => $oobCode,
            'newPassword' => $newPassword,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error confirming password reset: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Change email
     *
     * @param string $idToken
     * @param string $newEmail
     * @return void
     */
    public function changeEmail(string $idToken, string $newEmail)
    {
        $url = "{$this->baseUrl}:update?key={$this->apiKey}";
        $requestData = [
            'idToken' => $idToken,
            'email' => $newEmail,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error changing email: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Change password
     *
     * @param string $idToken
     * @param string $newPassword
     * @return void
     */
    public function changePassword($idToken, $newPassword)
    {
        $url = "{$this->baseUrl}:update?key={$this->apiKey}";
        $requestData = [
            'idToken' => $idToken,
            'password' => $newPassword,
            'returnSecureToken' => true,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error changing password: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Confirm email verification
     *
     * @param string $oobCode
     * @return void
     */
    public function confirmEmailVerification($oobCode)
    {
        $url = "{$this->baseUrl}:resetPassword?key={$this->apiKey}";
        $requestData = [
            'oobCode' => $oobCode,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error confirming email verification: ' . $error->getMessage();
            throw $error;
        }
    }

    /**
     * Delete account
     *
     * @param string $idToken
     * @return void
     */
    public function deleteAccount($idToken)
    {
        $url = "{$this->baseUrl}:delete?key={$this->apiKey}";
        $requestData = [
            'idToken' => $idToken,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data;
        } catch (Exception $error) {
            echo 'Error deleting account: ' . $error->getMessage();
            throw $error;
        }
    }
}
