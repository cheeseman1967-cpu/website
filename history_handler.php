<?php
// history_handler.php
// This script handles the communication between the Unity WebGL client and the server's file system.

// ----------------------------------------------------------------------------------
// CONFIGURATION
// ----------------------------------------------------------------------------------
// The file name must match the SAVE_FILE_NAME in your Unity C# script.
$filename = 'dnd_history.json'; 

// Check if the request is a POST or GET and determine the action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ----------------------------------------------------------------------------------
// ACTION: READ (Loading the conversation)
// ----------------------------------------------------------------------------------
if ($action === 'read') {
    if (file_exists($filename)) {
        // Set the content type header so Unity knows it's receiving JSON
        header('Content-Type: application/json');
        
        // Output the content of the file back to Unity
        echo file_get_contents($filename);
        exit;
    } else {
        // File doesn't exist (First run or deleted save), return 404
        http_response_code(404);
        // Return an empty JSON structure to prevent Unity's JsonUtility from crashing on null/empty response.
        echo '{"history":[]}'; 
        exit;
    }
}

// ----------------------------------------------------------------------------------
// ACTION: WRITE (Saving the conversation)
// ----------------------------------------------------------------------------------
if ($action === 'write') {
    // Get the data sent from Unity via the WWWForm POST
    $data = $_POST['data'] ?? '';
    
    // Check for necessary data and attempt to write the file
    if (!empty($data)) {
        // Use LOCK_EX to prevent multiple processes from writing at the same time
        if (file_put_contents($filename, $data, LOCK_EX) !== false) {
            http_response_code(200); // Success
            echo 'Success: History saved.';
            exit;
        } else {
            // Failed to write due to permissions or file system error
            http_response_code(500); 
            echo 'Error: Failed to write data. Check file permissions on the server.';
            exit;
        }
    } else {
        // Data field was empty
        http_response_code(400); 
        echo 'Error: No data received for writing.';
        exit;
    }
}

// ----------------------------------------------------------------------------------
// DEFAULT RESPONSE
// ----------------------------------------------------------------------------------
http_response_code(400); 
echo 'Error: No valid action provided.';
?>