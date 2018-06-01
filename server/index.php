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
        $conn = new PDO("mysql:host=".$host.";port=3306;dbname=".$db, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                $sql = "SELECT * FROM $tableName WHERE email = '$key'";
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function getAllKeyValues($tableName)
        {
                $sql = "SELECT * FROM $tableName LIMIT 10";
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function getAllKeyValuesRange($tableName,$offset)
        {

                $sql = "SELECT * FROM $tableName LIMIT 10 OFFSET ".$offset;
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function getAllKeyValuesCount($tableName)
        {
                $sql = "SELECT COUNT(*) as COUNT FROM $tableName";
                $query = DataRetriver::executeQuery($sql);
                $count = 0;
                while($row = $query->fetch_assoc()){
                        $count = $row['COUNT'];
        }
                return $count;
        }

        function getAllKeyValuesSearchCount($tableName,$searchString)
        {
                $sql = "SELECT COUNT(*) as COUNT FROM $tableName WHERE email LIKE '%".$searchString."%' OR name LIKE '%".$searchString."%'";
                $query = DataRetriver::executeQuery($sql);
                $count = 0;
                while($row = $query->fetch_assoc()){
                        $count = $row['COUNT'];
        }
                return $count;
        }

        function getAllKeyValuesSearch($tableName,$searchString)
        {
                $sql = "SELECT * FROM $tableName WHERE email LIKE '%".$searchString."%' OR name LIKE '%".$searchString."%' LIMIT 10";
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function getAllKeyValuesSearchRange($tableName,$offset,$searchString)
        {
                $sql = "SELECT * FROM $tableName WHERE email LIKE '%".$searchString."%' OR name LIKE '%".$searchString."%' LIMIT 10 OFFSET ".$offset;
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function deleteData($tableName, $email)
        {
                $sql = "DELETE FROM plivo.".$tableName." where email = '".$email."'";
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function insertData($tableName,$email,$name){
                $sql = "INSERT INTO plivo.".$tableName." (email,name) VALUES ('$email','$name')";
                $result = DataRetriver::executeQuery($sql);
                return $result;
        }

        function updateData($tableName,$email,$name){
                $sql = "UPDATE plivo.".$tableName." SET name = '".$name."' WHERE email = '".$email."'";
                //echo $sql;
                $result = DataRetriver::executeQuery($sql);
                return $result;
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
