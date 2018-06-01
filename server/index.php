<?php
require '../vendor/autoload.php';

class DataRetriver{

        public $db = null;

        function getDB()
        {
            $json = json_decode(file_get_contents("../config.json"), true);
            $db = "plivo";
            $username = $json['db_user'];
            $password = $json['db_pwd'];
            $host = $json['host'];
            // $conn = mysqli_connect($host, $username, $password);
            // return $conn;
            $conn = new PDO("mysql:host=".$host.";port=3306;dbname=".$db, $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }

        
        function executeQuery($query, $arguments){
            $conn = DataRetriver::getDB();
            $stmt = $conn->prepare($query);
            $check = $stmt->execute($arguments);
            return array($check, $stmt);
        }

        
        function getAllKeyValues($tableName)
        {
            $sql = "SELECT * FROM $tableName LIMIT 10";
            list($check, $stmt) = DataRetriver::executeQuery($sql,array());
            $result = $stmt->fetchAll();
	    $size = sizeof($result);
	    $data = [];
            for($x = 0; $x < $size;$x++){
            	$row = $result[$x];
            	$obj = array('email'=>$row['email'],'name'=> $row['name']);
            	$data[] = $obj;
            }
            return $data;
        }

        function getAllKeyValuesRange($tableName,$offset)
        {

            $sql = "SELECT * FROM $tableName LIMIT 10 OFFSET ".$offset;
            list($check, $stmt) = DataRetriver::executeQuery($sql,array());
            $result = $stmt->fetchAll();
	    $size = sizeof($result);
            $data = [];
            for($x = 0; $x < $size;$x++){
                $row = $result[$x];
                $obj = array('email'=>$row['email'],'name'=> $row['name']);
                $data[] = $obj;
            }
            return $data;
        }

        function getAllKeyValuesCount($tableName)
        {
            $sql = "SELECT COUNT(*) as COUNT FROM $tableName";
            list($check, $stmt) = DataRetriver::executeQuery($sql,array());
            $count = 0;
            if($row = $stmt->fetch())
                $count=$row['COUNT'];
                
            return $count;
        }

        function getAllKeyValuesSearchCount($tableName,$searchString)
        {
            $sql = "SELECT COUNT(*) as COUNT FROM $tableName WHERE email LIKE ? OR name LIKE ? ";
	    $args = array("%$searchString%","%$searchString%");

            list($check, $stmt) = DataRetriver::executeQuery($sql,$args);
            $count = 0;
            if($row = $stmt->fetch())
                $count=$row['COUNT'];
            
            return $count;
        }

        function getAllKeyValuesSearch($tableName,$searchString)
        {
            $sql = "SELECT * FROM $tableName WHERE email LIKE ? OR name LIKE ? LIMIT 10";
            $args = array("%$searchString%","%$searchString%");

            list($check, $stmt) = DataRetriver::executeQuery($sql,$args);
            $result = $stmt->fetchAll();
	    $size = sizeof($result);
            $data = [];
            for($x = 0; $x < $size;$x++){
                $row = $result[$x];
                $obj = array('email'=>$row['email'],'name'=> $row['name']);
                $data[] = $obj;
            }
            return $data;
        }

        function getAllKeyValuesSearchRange($tableName,$offset,$searchString)
        {
            $sql = "SELECT * FROM $tableName WHERE email LIKE ? OR name LIKE ? LIMIT 10 OFFSET ".$offset;
	    $args = array("%$searchString%","%$searchString%");
            
            list($check, $stmt) = DataRetriver::executeQuery($sql,$args);
            $result = $stmt->fetchAll();
	    $size = sizeof($result);
            $data = [];
            for($x = 0; $x < $size;$x++){
                $row = $result[$x];
                $obj = array('email'=>$row['email'],'name'=> $row['name']);
                $data[] = $obj;
            }
            return $data;
        }

        function deleteData($tableName, $email)
        {
            $sql = "DELETE FROM $tableName where email = ?";
            list($check, $stmt) = DataRetriver::executeQuery($sql,array($email));
            return $check;
        }

        function insertData($tableName,$email,$name){
            $sql = "INSERT INTO $tableName (email,name) VALUES (?,?)";
            list($check, $stmt) = DataRetriver::executeQuery($sql,array($email,$name));
            return $check;
        }

        function updateData($tableName,$email,$name){
            $sql = "UPDATE $tableName SET name = ? WHERE email = ?";
            list($check, $stmt) = DataRetriver::executeQuery($sql,array($name,$email));
            return $check;
        }

        function createTestDB(){
                $result = true;

                /*$sql = "DROP DATABASE plivo";
                $result = DataRetriver::executeQuery($sql);

                $sql = "CREATE DATABASE plivo";
                $result = DataRetriver::executeQuery($sql);

                if($result){
                        $sql = " CREATE TABLE IF NOT EXISTS contact (email TEXT PRIMARY KEY, name TEXT NOT NULL)";
                        $result = DataRetriver::executeQuery($sql);
                }*/


                if($result) {
                        $sql = "INSERT INTO contact (email, name) VALUES
                        ('Abayafdhfd','Everlasting'),
                        ('Abamsdfds', 'Loathe');";
                        echo $sql;
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
		$returnJson['data'] = $result;
        }

        echo json_encode($returnJson);
});

$app->get('/allKeyValuesRange', function($req,$res) {
        $DataRetriver = new DataRetriver();
        $params = $req->getQueryParams();
        $offset = $params['offset'];
        $returnJson = [];
        $result = $DataRetriver->getAllKeyValuesRange('contact',$offset);
        $returnJson['data'] = $result;
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
		$returnJson['data'] = $result;
        }

        echo json_encode($returnJson);
});

$app->get('/searchByRange', function($req,$res) {
        $DataRetriver = new DataRetriver();
        $params = $req->getQueryParams();
        $offset = $params['offset'];
        $searchString = $params['searchString'];

	$returnJson['data'] = [];

        $result = $DataRetriver->getAllKeyValuesSearchRange('contact',$offset,$searchString);
	$returnJson['data'] = $result;
        echo json_encode($returnJson);
});

$app->post('/addContact', function($req, $res) {
        $name = $req->getParsedBody()['name'];
        $email = $req->getParsedBody()['email'];
        $result = ((new DataRetriver())->insertData('contact',$email,$name));
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
});

$app->run();

?>

