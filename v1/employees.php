<?php
include("../connection.php");

$db             = new dbObj();
$connection     = $db->getConnstring();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            get_employees($id);
        } else {
            get_employees();
        }
        break;
    
    case 'POST':
        insert_employee();
        break;
    
    case 'PUT':
        $id = intval($_GET["id"]);
        update_employee($id);
        break;
    
    case 'DELETE':
        $id = intval($_GET["id"]);
        delete_employees($id);
        break;

    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

function auth_token()
{
    global $connection;
    $token = '';

    if (isset($_SERVER["HTTP_TOKEN"])) {
        $token = $_SERVER["HTTP_TOKEN"];
    }

    $id = base64_decode($token);
    $query = "SELECT * FROM tb_employee WHERE id=" . $id . " LIMIT 1";
    $result = mysqli_query($connection, $query);
  
    if ($result) {
        return true;
    } else{
        return false;
    }
}

function auth_failed()
{
    $response = array(
        'status' => 0,
        'status_message' => 'You do not have access'
    );
    header('Content-Type: application/json');
    echo json_encode($response);
}

function get_employees($id = 0)
{
    global $connection;
   
    if(auth_token()){
        $query = "SELECT * FROM tb_employee";
        if ($id != 0) {
            $query .= " WHERE id=" . $id . " LIMIT 1";
        }
        $response = array();
        $result   = mysqli_query($connection, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }else{
        auth_failed();
    }
}

function insert_employee()
{
    global $connection;

    if(auth_token()){
        $data            = json_decode(file_get_contents('php://input'), true);
        $employee_name   = $data["employee_name"];
        $employee_salary = $data["employee_salary"];
        $employee_age    = $data["employee_age"];
        
        echo $query = "INSERT INTO tb_employee SET
        employee_name='" . $employee_name . "',
        employee_salary='" . $employee_salary . "',
        employee_age='" . $employee_age . "'";
        
        if (mysqli_query($connection, $query)) {
            $response = array(
                'status' => 1,
                'status_message' => 'Employee Added Successfully.'
            );
        } else {
            $response = array(
                'status' => 0,
                'status_message' => 'Employee Addition Failed.'
            );
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }else{
        auth_failed();
    }
}

function update_employee($id)
{
    global $connection;

    if(auth_token()){
        $post_vars       = json_decode(file_get_contents("php://input"), true);
        $employee_name   = $post_vars["employee_name"];
        $employee_salary = $post_vars["employee_salary"];
        $employee_age    = $post_vars["employee_age"];
        $query           = "UPDATE tb_employee SET employee_name='" . $employee_name . "',
            employee_salary='" . $employee_salary . "',
            employee_age='" . $employee_age . "' WHERE id=" . $id;
        if (mysqli_query($connection, $query)) {
            $response = array(
                'status' => 1,
                'status_message' => 'Employee Updated Successfully.'
            );
        } else {
            $response = array(
                'status' => 0,
                'status_message' => 'Employee Updation Failed.'
            );
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }else{
        auth_failed();
    }
}

function delete_employees($id = 0)
{
    global $connection;
    
    if(auth_token()){
        $query = "DELETE FROM tb_employee WHERE id=" . $id;
        if (mysqli_query($connection, $query)) {
        $response = array(
            'status' => 1,
            'status_message' => 'Employee Deleted Successfully.'
        );
        } else {
        $response = array(
            'status' => 0,
            'status_message' => 'Employee Delete Failed.'
        );
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }else{
        auth_failed();
    }
}
?>
