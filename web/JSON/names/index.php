<?
header('Content-Type: application/json');
$conn = mysqli_init();
mysqli_real_connect_caesar($conn);
if (!_CS::load_caesar_settings()->isMysqlAvailable()) { echo json_encode(array("success"=>false,"error"=>"Failed to connect to database"));die();}
$sql = "SELECT `Name` FROM `Egyenleg`";
$result = mysqli_query($conn, $sql);
if(!$result){echo json_encode(array("success"=>false,"error"=>$sql . "<br>" . $conn->error));die();}
$rows = array("names" => array());
if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[names][] = $row["Name"];
    }
}
echo json_encode($rows);
$conn->close();
?>