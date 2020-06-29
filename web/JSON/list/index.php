<?
header('Content-Type: application/json');
$requestType = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);
switch($requestType) {
    case 'GET':
        returnTable();
    break;
    case 'POST':
        switch ($data['type']){
            case "request":
                if(!isset($data["user"],$data["name"],$data["quantity"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $user = $data["user"];
                $name =  $data["name"];
                $quantity = $data["quantity"];

                request($user, $name, $quantity);

                returnTable();
            break;
            case "fulfill":
                if(!isset($data["id"], $data["fulfilled_by"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $id = $data["id"];
                $fulfilled_by = $data["fulfilled_by"];

                fulfill($id, $fulfilled_by);
                
                returnTable();
            break;
	    case "delete":
		    if(!isset($data["id"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                delete($data["id"]);
		        returnTable();
	        break;
        default:
            echo json_encode(array("success"=>false,"error"=>"unrecognized type: '".$data["type"]."'"));die();
            break;
        }
    break;
    default:
        echo json_encode(array("success"=>false,"error"=>"Not a POST or a GET request!"));die();
    break;
}

function returnTable(){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "SELECT * FROM `List`";
    $result = mysqli_query($conn, $sql);
    if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
    $rows = array();
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row["Id"] = intval($row["Id"]);
	    $row["Fulfilled"] = intval($row["Fulfilled"]);
            $rows[] = $row;
        }
    }
    echo json_encode($rows);
    $conn->close();
}

function request($user, $name, $quantity){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "INSERT INTO `List`(`User`, `Name`, `Quantity`, `Date` ) VALUES ('".$user."','".$name."','".$quantity."', now() )";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();

}
function fulfill($id, $fulfilled_by){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql= "UPDATE `List` SET `Fulfilled` = 1, `Fulfilled_by` = '".$fulfilled_by."'  WHERE `Id` = '".$id."'";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();
}
function delete($id){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql= "DELETE FROM `List` WHERE `Id` = '".$id."'";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();
}

?>
