<?php
require '../vendor/autoload.php';
require __DIR__.'/class.php';

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$app->get('/', function() {
        echo "Contact Book Demo Project";
});


$c = $app->getContainer();
$app->add(function($req,$res,$next) use($c){

   $header = $req->getHeader('Authorization');
   if($header[0] != "plivo123")
   return $c['response']->withStatus(401)->write('Unauthorized');

  return  $next($req,$res);

});

$app->get('/allKeyValues', function($req,$res) {
        $retriverObj = new DataRetriver();
        $count = (int)($retriverObj->getAllKeyValuesCount('contact'));

        $returnJson = array();
        $returnJson['total'] = 0;
        $returnJson['data'] = [];

        if($count){
                $returnJson['total'] = $count;
                $result = $retriverObj->getAllKeyValues('contact');
                $returnJson['data'] = $result;
        }

        echo json_encode($returnJson);
});

$app->get('/allKeyValuesRange', function($req,$res) {
        $retriverObj = new DataRetriver();
        $params = $req->getQueryParams();
        $offset = $params['offset'];
        $returnJson = [];
        $result = $retriverObj->getAllKeyValuesRange('contact',$offset);
        $returnJson['data'] = $result;
        echo json_encode($returnJson);
});

$app->get('/search', function($req,$res) {
        $retriverObj = new DataRetriver();
        $params = $req->getQueryParams();
        $searchString = $params['searchString'];
        $count = (int)($retriverObj->getAllKeyValuesSearchCount('contact',$searchString));

        $returnJson = array();
        $returnJson['total'] = 0;

        $returnJson['data'] = [];
        if($count){
                $returnJson['total'] = $count;
                $result = $retriverObj->getAllKeyValuesSearch('contact',$searchString);
                $returnJson['data'] = $result;
        }

        echo json_encode($returnJson);
});

$app->get('/searchByRange', function($req,$res) {
        $retriverObj = new DataRetriver();
        $params = $req->getQueryParams();
        $offset = $params['offset'];
        $searchString = $params['searchString'];

        $returnJson['data'] = [];

        $result = $retriverObj->getAllKeyValuesSearchRange('contact',$offset,$searchString);
        $returnJson['data'] = $result;
        echo json_encode($returnJson);
});

$app->post('/addContact', function($req, $res) use($c){
        $name = $req->getParsedBody()['name'];
        $email = $req->getParsedBody()['email'];

        $retriverObj = new  DataRetriver();
        $count = (int)($retriverObj->ifUserExists('contact',$email));
        if($count){
                return $c['response']->withStatus(402)->write(json_encode(['msg'=>'Email already exists.']));
        }else{
                $result = ($retriverObj->insertData('contact',$email,$name));
                if($result){
                        echo json_encode(['status'=>'SUCCESS']);
                } else {
                        echo json_encode(['status'=>'FAILURE']);
                }
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
        $retriverObj = new DataRetriver();
        $result = ($retriverObj->deleteData('contact',$email));

        if($result){
                echo json_encode(['status'=>'SUCCESS']);
        } else {
                echo json_encode(['status'=>'FAILURE']);
        }
});

$app->run();

