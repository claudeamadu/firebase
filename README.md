# firebase
Simplified Firebase Classes for your projects(PHP & JS)
Straight forward codes to easily use in your projects without hassle

### Installation
```
composer require claudeamadu/firebase
```

### List Classes
```php
FirebaseAuth
FirebaseUser
FirebaseFirestore
FirestoreDB
Query
FieldFilter
CompositeFilter
UnaryFilter
PathBuilder
FirebaseCloudMessaging
```

###### Autoload
```php
require 'vendor/autoload.php';
```

### Firebase Auth

###### Sign in anonymously
```php
$apiKey = 'AIzaSyxxxxxxxxxxxxxxxxxxxx';
$auth = new FirebaseAuth($apiKey);
$token = null;
if ($auth->signInAnonymously()) {
    $token = $auth->getAccessToken()
}
```

###### Sign in with email and password
```php
$apiKey = 'AIzaSyxxxxxxxxxxxxxxxxxxxx';
$auth = new FirebaseAuth($apiKey);
$token = null;
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];
if ($auth->signIn($email, $password)) {
    $token = $auth->getAccessToken()
}
```

###### Sign up with email and password
```php
$apiKey = 'AIzaSyxxxxxxxxxxxxxxxxxxxx';
$auth = new FirebaseAuth($apiKey);
$token = null;
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];
if ($auth->signUp($email, $password)) {
    $token = $auth->getAccessToken()
}
```

###### Firebase user
```php
$user = $auth->currentUser();
```

### Firestore

###### Setting up 
```php
$Database = '(default)';
$ProjectID = 'xxxxxxxxxxx';
// Create an instance of FirebaseFirestore
$firestore = new FirebaseFirestore($token, $Database, $ProjectID);
$db = $firestore->db;
```

###### Getting a document
```php
$documentPath = 'users/xxxxxxx@ttu.edu.gh'; 
$document = $db->getDocument('users', $documentPath);
if ($document !== null) {
    echo $document;
}
```

###### Updating a document
```php
$data = [
    "user_name" => "WOW 2",    
    "user_id" => "22195",    
];
$db->updateDocument('users','22195',$data);
```

###### Fetch a collection
```php
$collectionPath = 'users'; // Replace with your collection path
$collection = $db->getCollection($collectionPath);
if ($collection !== null) {
    echo $collection;
}
```

###### Run a query
```php
$fieldFilter = new FieldFilter();
$compositeFilter = new CompositeFilter();

$fieldFilter->equalTo('user_name', 'WOW 2');

$query = $db->query;
$query->from('users')->where2($fieldFilter);
echo $query->run();
```

### Cloud Messaging
```php
<?php
// Usage:
$credentialsPath = 'serviceAccountCredentials.json';
$topics = 'ios_general';
$title = 'Title of Notification';
$body = 'Body of Notification';
$fcm = new FirebaseCloudMessaging($credentialsPath);

$accessToken = $fcm->getAccessToken();
//echo $accessToken;
$response = $fcm->sendFCMNotificationToTopic($accessToken, $topics, $title, $body);
echo $response;
```