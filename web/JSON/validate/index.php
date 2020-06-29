<?
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$name = $data["name"];
$pin = $data["pin"];
$type = $data["type"];
if(!isset($name,$pin,$type)){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}

$conn = mysqli_init();
mysqli_real_connect_caesar($conn);
if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
$sql = "SELECT `Hash` FROM `Egyenleg` WHERE `Name` = '" . $name . "'";
$result = mysqli_query($conn, $sql);
if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
$hash = mysqli_fetch_assoc($result)["Hash"];
if(!isset($hash)){echo json_encode(array("success"=>false,"error"=>"Failed to retrieve Hash..."));die();}
switch($type) {
  case 'validate':
    if($pin==$hash){
      echo json_encode(array("valid"=>true));
      }else{echo json_encode(array("valid"=>false));}
  break;
  case 'change':
    if(!isset($data["new_pin"])){echo json_encode(array("success"=>false,"error"=>"At least one variable is missing in the request"));die();}
    $new_hash = $data["new_pin"];
    if($pin==$hash){
      $sql= "UPDATE `Egyenleg` SET `Hash` ='". $new_hash."' WHERE `Name` = '".$name."'";
      if ($conn->query($sql) === FALSE) {die("Error: " . $sql . "<br>" . $conn->error);}
      echo json_encode(array("valid"=>true));
    }
    else{
      echo json_encode(array("valid"=>false));
    }
  break;
}
$conn->close();
?>