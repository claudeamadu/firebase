<?php
/**
 * Firebase PHP Library
 *
 * This PHP library provides an interface to interact with the Paystack API,
 * enabling developers to handle payment transactions, transfers, verification,
 * and other financial operations easily.
 *
 * @version 1.0
 * @license GNU
 * @author Claude Amadu
 * @link https://github.com/claudeamadu/firebase
 * @link https://firebase.google.com/docs
 */






// Helper functions
/**
 * Convert Firestore JSON to PHP array
 *
 * @param array $firestoreData
 * @return array
 */
function convertFirestoreJSON($firestoreData)
{
    if (isset($firestoreData['documents'])) {
        // If it's a collection
        $documents = array_map(function ($doc) {
            $nameParts = explode('/', $doc['name']);
            $docId = end($nameParts); // Extract document ID
            $fields = convertFieldsToJSON($doc['fields']); // Convert fields to JSON
            $fields['id'] = $docId;
            $fields['createTime'] = $doc['createTime'];
            $fields['updateTime'] = $doc['updateTime'];
            return $fields;
        }, $firestoreData['documents']);

        return ['documents' => $documents];
    } elseif (isset($firestoreData['name']) && isset($firestoreData['fields'])) {
        // If it's a single document
        $nameParts = explode('/', $firestoreData['name']);
        $docId = end($nameParts); // Extract document ID
        $fields = convertFieldsToJSON($firestoreData['fields']); // Convert fields to JSON
        $fields['id'] = $docId;
        $fields['createTime'] = $firestoreData['createTime'];
        $fields['updateTime'] = $firestoreData['updateTime'];
        return $fields;
    } elseif (is_array($firestoreData)) {
        $documents = array_map(function ($element) {
            $doc = $element['document'];
            $nameParts = explode('/', $doc['name']);
            $docId = end($nameParts); // Extract document ID
            $fields = convertFieldsToJSON($doc['fields']); // Convert fields to JSON
            $fields['id'] = $docId;
            $fields['createTime'] = $doc['createTime'];
            $fields['updateTime'] = $doc['updateTime'];
            return $fields;
        }, $firestoreData);

        return ['documents' => $documents];
    }

    return null;
}
/**
 * Convert Firestore fields to PHP array
 *
 * @param array $fields
 * @return array
 */
function convertFieldsToJSON($fields)
{
    $result = [];
    foreach ($fields as $key => $value) {
        if (isset($value['stringValue'])) {
            $result[$key] = $value['stringValue'];
        } elseif (isset($value['integerValue'])) {
            $result[$key] = intval($value['integerValue']);
        } elseif (isset($value['mapValue']['fields'])) {
            $result[$key] = convertFieldsToJSON($value['mapValue']['fields']);
        } elseif (isset($value['arrayValue']['values'])) {
            $result[$key] = array_map(function ($value) {
                if (isset ($value['stringValue'])) {
                    return $value['stringValue'];
                } elseif (isset ($value['integerValue'])) {
                    return intval($value['integerValue']);
                }
                return null; // Handle other value types as needed
            }, $value['arrayValue']['values']);
        }
    }
    return $result;
}
/**
 * Convert PHP array to Firestore JSON
 *
 * @param array $data
 * @return array
 */
function convertToFirestoreValue($value)
{
    if (is_string($value)) {
        return ['stringValue' => $value];
    } elseif (is_numeric($value)) {
        return ['integerValue' => strval($value)];
    } elseif (is_array($value)) {
        $values = array_map('convertToFirestoreValue', $value);
        return ['arrayValue' => ['values' => $values]];
    } elseif (is_object($value)) {
        $mapValue = [];
        foreach ($value as $key => $innerValue) {
            $mapValue[$key] = convertToFirestoreValue($innerValue);
        }
        return ['mapValue' => ['fields' => $mapValue]];
    }
    // Handle other data types or undefined/null values as needed
    return null;
}

/**
 * Convert PHP array to Firestore JSON
 *
 * @param array $data
 * @return array
 */
function convertFieldsToFirestoreJSON($fields)
{
    $result = [];
    foreach ($fields as $key => $value) {
        $result[$key] = convertToFirestoreValue($value);
    }
    return $result;
}