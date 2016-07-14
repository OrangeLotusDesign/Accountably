<?php
    //database configuration
    $dbHost = '127.0.0.1';
    $dbUsername = 'alex';
    $dbPassword = 'hesse';
    $dbName = 'accountably';
    
    //connect with the database
    $db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);
    
    //get search term
    $searchTerm = $_GET['term'];
    
    //get matched data from skills table
    $query = $db->query("SELECT industry FROM wp_accountably_user WHERE industry LIKE '%".$searchTerm."%' ORDER BY industry ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['industry'];
    }
    
    //return json data
    echo json_encode($data);
?>