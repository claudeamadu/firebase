<?php

/**
 * Firebase Cloud Messaging
 */
class FirebaseCloudMessaging
{
    private $credentialsPath;
    private $projectId;

    /**
     * Initialize Firebase Cloud Messaging
     *
     * @param string $credentialsPath
     */
    public function __construct($credentialsPath = 'serviceAccountCredentials.json')
    {
        $this->credentialsPath = $credentialsPath;
    }
    /**
     * Create a JWT
     *
     * @param array $header
     * @param array $payload
     * @param string $privateKey
     * @return string
     */
    private function createJWT($header, $payload, $privateKey)
    {
        $headerEncoded = base64_encode(json_encode($header));
        $payloadEncoded = base64_encode(json_encode($payload));

        $dataToSign = $headerEncoded . '.' . $payloadEncoded;

        // Load the private key
        $privateKey = openssl_pkey_get_private($privateKey);

        // Create the signature using the private key and RS256 algorithm
        openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        $tokenFile = 'token.json';

        if (file_exists($tokenFile)) {
            $tokenData = json_decode(file_get_contents($tokenFile), true);
            $this->projectId = $tokenData['project_id'];
            // Check if the token exists and is not expired
            if ($tokenData && isset($tokenData['access_token']) && isset($tokenData['expires_at']) && $tokenData['expires_at'] > time()) {
                return $tokenData['access_token']; // Return the existing token
            }
        }

        // Load the service account credentials JSON file
        $serviceAccount = json_decode(file_get_contents($this->credentialsPath), true);

        // Get the OAuth 2.0 client ID and private key from the service account credentials
        $clientEmail = $serviceAccount['client_email'];
        $privateKey = $serviceAccount['private_key'];

        // Create the JWT payload
        $jwtPayload = [
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => time(),
            'exp' => time() + 3600,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform'
        ];

        // Create the JWT token
        $header = [
            'alg' => 'RS256', // Algorithm used for hashing (you can use 'RS256' for RSA)
            'typ' => 'JWT'    // Type of token
        ];

        $jwt = $this->createJWT($header, $jwtPayload, $privateKey);

        // Make a POST request to the Google OAuth 2.0 token endpoint to exchange the JWT for an access token
        $tokenEndpoint = 'https://oauth2.googleapis.com/token';
        $postData = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);

        if ($response === false) {
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);

        $accessTokenData = json_decode($response, true);

        // Save the access token to token.json
        $accessTokenData['expires_at'] = time() + $accessTokenData['expires_in']; // Calculate token expiration time
        $accessTokenData['project_id'] = $serviceAccount['project_id'];
        file_put_contents($tokenFile, json_encode($accessTokenData));

        $this->projectId = $accessTokenData['project_id'];
        return $accessTokenData['access_token'];
    }


    /**
     * Send notification
     *
     * @param string $accessToken
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    public function sendNotificationToTopic($accessToken, $topics, $title, $body, $data = null)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $message = [
            'message' => [
                'topic' => $topics,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ]
                ],
                'apns' => [
                    'headers' => ['apns-priority' => '5'],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $body
                            ]
                        ]
                    ]
                ],
                'webpush' => [
                    'headers' => ['TTL' => '86400']
                ]
            ]
        ];

        if ($data !== null && !empty($data)) {
            $message['message']['data'] = $data;
            $message['message']['data']['title'] = $title;
            $message['message']['data']['body'] = $body;
        } else {
            $message['message']['data'] = [
                'title' => $title,
                'body' => $body
            ];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Should be set to true in production
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);

        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Send notification to Android
     *
     * @param string $accessToken
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    public function sendNotificationToAndroid($accessToken, $topics, $title, $body, $data = null)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $message = [
            'message' => [
                'topic' => $topics,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ]
                ]
            ]
        ];

        if ($data !== null && !empty($data)) {
            $message['message']['data'] = $data;
            $message['message']['data']['title'] = $title;
            $message['message']['data']['body'] = $body;
        } else {
            $message['message']['data'] = [
                'title' => $title,
                'body' => $body
            ];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Should be set to true in production
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);

        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Send notification to iOS
     *
     * @param string $accessToken
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    public function sendFCMNotificationToIOS($accessToken, $topic, $title, $body, $data = null)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        /*$message = [
            'message' => [
                'topic' => 'ios_' . $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'apns' => [
                    'headers' => ['apns-priority' => '5'],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $body
                            ]
                        ]
                    ]
                ]
            ]
        ];*/

        $message = [
            'message' => [
                'topic' => 'ios_' . $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '5'
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 0
                        ]
                    ]
                ]
            ]
        ];


        /*if ($data !== null && !empty($data)) {
            $message['message']['data'] = $data;
            $message['message']['data']['title'] = $title;
            $message['message']['data']['body'] = $body;
        } else {
            $message['message']['data'] = [
                'title' => $title,
                'body' => $body
            ];
        }*/

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Should be set to true in production
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);

        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }
}
