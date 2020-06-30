<?
header('Content-Type: application/json');
$requestType = $_SERVER['REQUEST_METHOD'];
$conn = mysqli_init();
mysqli_real_connect_caesar($conn);
if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
switch($requestType) {
    case 'GET':
    break;
    case 'POST':
        $name = json_decode(file_get_contents('php://input'), true)["name"];
        if(!isset($name)){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
        $sql = "SELECT `Date`,`To_User`,`From_User`,`Type`,`Amount`,`Note`,`Transaction_Id` FROM `History` WHERE ((`To_User` = '".$name."' AND `Type` != 'add_expense') OR (`From_User` = '".$name."' AND (`type` = 'add_expense' OR `type` = 'payment')))";
        $result = mysqli_query($conn, $sql);
        if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
        $rows = array("history" => array());
        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row["Amount"] = intval($row["Amount"]);
                $row["Transaction_Id"] = intval($row["Transaction_Id"]);
                $rows["history"][] = $row;
            }
        }
        echo json_encode($rows);
    break;
}
$conn->close();
?>