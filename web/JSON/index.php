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
            case "payment":
                if(!isset($data["from_name"],$data["to_name"],$data["amount"],$data["note"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $from_name = $data["from_name"];
                $to_name =  $data["to_name"];
                $amount = $data["amount"];
                $note = $data["note"];
                $transaction_id=getTransactionId();
                $from_amount = getAmount($from_name);
                $to_amount = getAmount($to_name);

                update($from_name, $from_amount + $amount);
                update($to_name, $to_amount - $amount);
                
                saveToHistory($from_name, $to_name, $data["type"], $amount, $note, $transaction_id);

                returnTable();
            break;
            case "new_expense":
                if(!isset($data["from_name"],$data["to_names"],$data["amount"],$data["note"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $from_name = $data["from_name"];
                $totalAmount = $data["amount"];
                $amount = $totalAmount/count($data["to_names"]);
                $note = $data["note"];
                $transaction_id=getTransactionId();

                foreach ($data["to_names"] as $to_name){
                    $to_amount = getAmount($to_name);
                    update($to_name, $to_amount - $amount);
                    saveToHistory($from_name, $to_name, $data["type"], $amount, $note, $transaction_id);
                }
                $from_amount = getAmount($from_name);
                saveToHistory($from_name, implode( ",", $data["to_names"]), "add_expense", $totalAmount, $note, $transaction_id);
                update($from_name, $from_amount + $totalAmount);
                returnTable();
            break;
            case "new_user":
                if(!isset($data["name"],$data["pin"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $name = $data["name"];
                $pin = $data["pin"];
                addUser($name, $pin);
                returnTable();
            break;
            case "delete":
                if(!isset($data["Transaction_Id"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
                $transaction_id = $data["Transaction_Id"];
                delete($transaction_id);
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
    $sql = "SELECT `Name`,`Amount` FROM `Egyenleg`";
    $result = mysqli_query($conn, $sql);
    if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
    $rows = array();
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row["Amount"] = intval($row["Amount"]);
            $rows[] = $row;
        }
    }
    echo json_encode($rows);
    $conn->close();
}
function getAmount($name){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "SELECT `Amount` FROM `Egyenleg` WHERE `Name` = '" . $name . "'";
    $result = mysqli_query($conn, $sql);
    if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
    $conn->close();
    $amount = mysqli_fetch_assoc($result)["Amount"];
    if(!isset($amount)){echo json_encode(array("success"=>false,"error"=>"GetAmount() failed to return..."));die();}
    return $amount;
}
function update($name, $amount){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql= "UPDATE `Egyenleg` SET `Amount` =". $amount." WHERE `Name` = '".$name."'";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();
}
function addUser($name, $hash){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "INSERT INTO `Egyenleg`(`Name`, `Amount`, `Hash`) VALUES ('".$name."',0,'".$hash."')";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();
}

function saveToHistory($from_name, $to_name, $type, $amount, $note, $transaction_id){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "INSERT INTO `History`(`Date`, `From_User`, `To_User`, `Type`, `Amount`, `Note`, `Transaction_Id`) VALUES (now(),'".$from_name."','".$to_name."','".$type."',".$amount.",'".$note."','".$transaction_id."')";
    if ($conn->query($sql) === FALSE) {echo json_encode(array("success"=>false,"error"=> $sql . "<br>" . $conn->error));die();}
    $conn->close();
}
function delete($id){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "SELECT `From_User`, `To_User`, `Type`, `Amount` FROM `History` WHERE `Transaction_Id` = '".$id."'";
    $result = mysqli_query($conn, $sql);
    if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
    $conn->close();
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            switch ($row["Type"]){
                case "payment":
                    $to_user = $row["To_User"];
                    $to_amount = getAmount($to_user);
                    $from_user = $row["From_User"];
                    $from_amount = getAmount($from_user);
                    $amount = $row["Amount"];
                    update($to_user, $to_amount + $amount);
                    update($from_user, $from_amount - $amount);
                break;
                case "new_expense":
                    $to_user = $row["To_User"];
                    $to_amount = getAmount($to_user);
                    $amount = $row["Amount"];
                    update($to_user, $to_amount + $amount);
                break;
                case "add_expense":
                    $from_user = $row["From_User"];
                    $from_amount = getAmount($from_user);
                    $amount = $row["Amount"];
                    update($from_user, $from_amount - $amount);
                break;
            }
        }
    }
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "DELETE FROM `History` WHERE `Transaction_Id` = '".$id."'";
    if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
    $conn->close();
}
function getTransactionId(){
    $conn = mysqli_init();
    mysqli_real_connect_caesar($conn);
    if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
    $sql = "SELECT MAX(`Transaction_Id`) as `Max` FROM `History`";
    $result = mysqli_query($conn, $sql);
    if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
    $conn->close();
    $max = mysqli_fetch_assoc($result)["Max"];
    if(!isset($max)){echo json_encode(array("success"=>false,"error"=>"GetTransactionId() failed to return..."));die();}
    return $max+1;
}
?>
