<?php
require '../vendor/autoload.php';

class DataRetriver{

	public $db = null;

	function getDB()
    {
        $json = json_decode(file_get_contents("../config.json"), true);
        $username = $json['db_user'];
        $password = $json['db_pwd'];
        $host = $json['host'];
        $conn = mysqli_connect($host, $username, $password);
        return $conn;
    }

	function executeQuery($query)
	{
		if($this->db == null){
			$this->db = DataRetriver::getDB();
		}
		$result = mysqli_query($this->db, $query);
		return $result;
	}

	function getValue($tableName, $key)
	{
		$sql = "SELECT * FROM plivo.$tableName WHERE word = '$key'";
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function getAllKeyValues($tableName)
	{
		$sql = "SELECT * FROM plivo.$tableName LIMIT 10";
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function getAllKeyValuesRange($tableName,$offset)
	{
		
		$sql = "SELECT * FROM plivo.$tableName LIMIT 10 OFFSET ".$offset;
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function getAllKeyValuesCount($tableName)
	{
		$sql = "SELECT COUNT(*) as COUNT FROM plivo.$tableName";
		$query = DataRetriver::executeQuery($sql);
		$count = 0;
		while($row = $query->fetch_assoc()){
			$count = $row['COUNT'];
        }
		return $count;
	}

	function getAllKeyValuesSearchCount($tableName,$searchString)
	{
		$sql = "SELECT COUNT(*) as COUNT FROM plivo.$tableName WHERE word LIKE '%".$searchString."%' OR meaning LIKE '%".$searchString."%'";
		$query = DataRetriver::executeQuery($sql);
		$count = 0;
		while($row = $query->fetch_assoc()){
			$count = $row['COUNT'];
        }
		return $count;
	}

	function getAllKeyValuesSearch($tableName,$searchString)
	{
		$sql = "SELECT * FROM plivo.$tableName WHERE word LIKE '%".$searchString."%' OR meaning LIKE '%".$searchString."%' LIMIT 10";
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function getAllKeyValuesSearchRange($tableName,$offset,$searchString)
	{
		$sql = "SELECT * FROM plivo.$tableName WHERE word LIKE '%".$searchString."%' OR meaning LIKE '%".$searchString."%' LIMIT 10 OFFSET ".$offset;
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function deleteData($tableName, $email)
	{
		$sql = "DELETE FROM plivo.".$tableName." where word = '".$email."'";
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function insertData($tableName,$word,$meaning){
		$sql = "INSERT INTO plivo.".$tableName." (word,meaning) VALUES ('$word','$meaning')";
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function updateData($tableName,$word,$meaning){
		$sql = "UPDATE plivo.".$tableName." SET meaning = '".$meaning."' WHERE word = '".$word."'";
		//echo $sql;
		$result = DataRetriver::executeQuery($sql);
		return $result;
	}

	function createTestDB(){
		$result = false;

		$sql = "DROP DATABASE plivo";
		$result = DataRetriver::executeQuery($sql);
		
		$sql = "CREATE DATABASE plivo";
		$result = DataRetriver::executeQuery($sql);

		if($result){
			$sql = "CREATE TABLE plivo.contact (
				id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
				word varchar(20),
				meaning varchar(20)
			)";
			$result = DataRetriver::executeQuery($sql);
		}


		if($result) {
			$sql = "INSERT INTO plivo.contact (word, meaning) VALUES 
			('Accolade', 'Praise'),
			('Abase', 'Degrade'),
			('Humiliate', 'Make humble'),
			('Abash', 'Embarrass'),
			('Abate', 'Subside'),
			('Abbreviate', 'Everlasting'),
			('Abjure', 'Everlasting'),
			('Ablution', 'Washing'),
			('Abnegation', 'Renuniciation'),
			('Abode', 'Dwelling'),
			('Abominable', 'Detestable'),
			('Pretty', 'Beautiful'),
			('Pensive', 'Deep'),
			('Abacus','Everlasting'),
			('Abatis','Everlasting'),
			('Abasia','Everlasting'),
			('Abases','Everlasting'),
			('Abated','Everlasting'),
			('Abater','Everlasting'),
			('Abator','Everlasting'),
			('Abates','Everlasting'),
			('Abased','Everlasting'),
			('Abaser','Everlasting'),
			('Abacas','Everlasting'),
			('Abanjd','Everlasting'),
			('Abamps','Everlasting'),
			('Abayas','Everlasting'),
			('Abanjh','Everlasting'),
			('Abampolkf','Everlasting'),
			('Abayafdhfd','Everlasting'),
			('Abamsdfds', 'Loathe')";
			$result = DataRetriver::executeQuery($sql);
		}

		return $result;
	}
}


$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$app->get('/', function() {
	echo "Contact Book Demo Project";
});


$app->get('/allKeyValues', function($req,$res) {
	$DataRetriver = new DataRetriver();
	$count = (int)($DataRetriver->getAllKeyValuesCount('contact'));

	$returnJson = array();
	$returnJson['total'] = 0;
	$returnJson['data'] = [];

	if($count){
		$returnJson['total'] = $count;
		$result = $DataRetriver->getAllKeyValues('contact');
		$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
		
		foreach($rows as $row){
			$returnJson['data'][] = $row;
		}
	}

	echo json_encode($returnJson);
});

$app->get('/allKeyValuesRange', function($req,$res) {
	$DataRetriver = new DataRetriver();
	$params = $req->getQueryParams();
	$offset = $params['offset'];
	$returnJson = [];
	$result = $DataRetriver->getAllKeyValuesRange('contact',$offset);
	$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($rows as $row){
		$returnJson['data'][] = $row;
	}
	echo json_encode($returnJson);
});

$app->get('/search', function($req,$res) {
	$DataRetriver = new DataRetriver();
	$params = $req->getQueryParams();
	$searchString = $params['searchString'];
	$count = (int)($DataRetriver->getAllKeyValuesSearchCount('contact',$searchString));

	$returnJson = array();
	$returnJson['total'] = 0;
	$returnJson['data'] = [];

	if($count){
		$returnJson['total'] = $count;
		$result = $DataRetriver->getAllKeyValuesSearch('contact',$searchString);
		$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
		foreach($rows as $row){
			$returnJson['data'][] = $row;
		}
	}

	echo json_encode($returnJson);
});

$app->get('/searchByRange', function($req,$res) {
	$DataRetriver = new DataRetriver();
	$params = $req->getQueryParams();
	$offset = $params['offset'];
	$searchString = $params['searchString'];

	$result = $DataRetriver->getAllKeyValuesSearchRange('contact',$offset,$searchString);
	$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($rows as $row){
		$returnJson['data'][] = $row;
	}
	echo json_encode($returnJson);
});

$app->post('/addContact', function($req, $res) {
	$name = $req->getParsedBody()['name'];
	$email = $req->getParsedBody()['email'];
	//echo json_encode(array("sdfsd"=>$name));
	$result = ((new DataRetriver())->insertData('contact',$name,$email));
	if($result){
		echo json_encode(['status'=>'SUCCESS']);
	} else {
		echo json_encode(['status'=>'FAILURE']);
	}
});

$app->post('/initializeDb', function($req, $res){
	$request = $req->getParsedBody();
	$result = ((new DataRetriver())->createTestDB());

	if($result){
		echo json_encode(['status'=>'SUCCESS']);
	} else {
		echo json_encode(['status'=>'FAILURE']);
	}
});

$app->post('/updateContact', function($req, $res) {
	$name = $req->getParsedBody()['name'];
	$email = $req->getParsedBody()['email'];
	$result = ((new DataRetriver())->updateData('contact',$email,$name));
	if($result){
		echo json_encode(['status'=>'SUCCESS']);
	} else {
		echo json_encode(['status'=>'FAILURE']);
	}
});

$app->post('/deleteContact', function($req, $res) {
	$email = $req->getParsedBody()['email'];
	$DataRetriver = new DataRetriver();
	$result = ($DataRetriver->deleteData('contact',$email));

	if($result){
		echo json_encode(['status'=>'SUCCESS']);
	} else {
		echo json_encode(['status'=>'FAILURE']);
	}

	// $returnJson = array();
	// $returnJson['total'] = 0;
	// $returnJson['data'] = [];

	// $count = (int)($DataRetriver->getAllKeyValuesCount('contact'));
	// $returnJson['total'] = $count;
	// echo json_encode($returnJson);

});

$app->run();

?>