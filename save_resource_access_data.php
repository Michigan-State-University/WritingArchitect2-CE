<?php
// File where the teacher data will be stored
$file = 'resource_access_data.csv';

// Check if the form data is sent via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get teacher data from the POST request
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Open the CSV file in append mode
    $handle = fopen($file, 'a');

    if ($handle) {
        // Create a CSV row with the data
        $data = array($first_name, $last_name, $email, $role);

        // Write the row to the file
        fputcsv($handle, $data);

        // Close the file
        fclose($handle);

        // Respond to indicate success
        echo "Data saved successfully!";
    } else {
        echo "Unable to open file!";
    }
}
?>