<?php

include("../connection.php");
$db             = new dbObj();
$connection     = $db->getConnstring();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        $token = $_GET["token"];
        auth_employee($token);
        break;
    
    case 'POST':
        login_employee();
        break;
  
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

function auth_employee($token = 0)
{
    global $connection;
    $id = base64_decode($token);
    $query = "SELECT * FROM tb_employee WHERE id=" . $id . " LIMIT 1";
    $result = mysqli_query($connection, $query);
  
    $response = array(
        'status' => 0,
        'status_message' => 'Employee Not Found'
    );

    if ($result) {
        $row = mysqli_num_rows($result);
        if($row == 1)
        {
            $fetch = mysqli_fetch_assoc($result);
            $response = array(
                'status' => 1,
                'status_message' => 'Employee Authenticated',
                'user_data' => $fetch
            );
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function login_employee()
{
    global $connection;
    $data            = json_decode(file_get_contents('php://input'), true);
    $employee_name   = $data["employee_name"];
    $employee_password = md5($data["employee_password"]);
    
    $query = "SELECT * FROM tb_employee WHERE
       employee_name='" . $employee_name . "' AND
       employee_password='" . $employee_password . "'";
    
    $result = mysqli_query($connection, $query);
    $row = mysqli_num_rows($result);
    $fetch = mysqli_fetch_assoc($result);

    if ($row == 1) {
        $response = array(
            'status' => 1,
            'status_message' => 'Successfully Login',
            'token' => base64_encode($fetch['id']),
            'user_data' => $fetch
        );
    } else {
        $response = array(
            'status' => 0,
            'status_message' => 'Failed Login.'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
