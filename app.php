<?php
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://r-gs5df9f4c032e3a4.redis.singapore.rds.aliyuncs.com:6379?auth=Foobar123');

//table store
/*
require_once __DIR__ . '/aliyun-tablestore-php-sdk-master/vendor/autoload.php';
use Aliyun\OTS\OTSClient as OTSClient;
$otsClient = new OTSClient([
    'EndPoint' => 'http://10.1.0.5:80/',
    'AccessKeyID' => 'AKg8ujhqB6VDeBJU',
    'AccessKeySecret' => 'vcpZiDqX0fJv2FrhIXyQXWAakt62E5',
    'InstanceName' => 'vasi-test',
    'ErrorLogHandler' => null,
    'DebugLogHandler' => ''
]);
*/

//rds
try {
    $dbh = new \PDO('mysql:host=rm-gs5a5z7p7bply58h0.mysql.singapore.rds.aliyuncs.com;dbname=sg_main', 'webuser', '69Jenova');
} catch (\PDOException $e) {
    die("Could not connect to rds.");
}

//redis
try {
	$redis = new \Redis();
	$redis->connect('r-gs5df9f4c032e3a4.redis.singapore.rds.aliyuncs.com', 6379);
	$redis->auth('Foobar123');
} catch (\Exception $e) {
    die("Could not connect to redis.");
}

session_start();

if (!isset($_SESSION['drinks'])){
    $_SESSION['drinks'] = [];
}
if (!isset($_SESSION['messages'])){
    $_SESSION['messages'] = [];
}

$menu = [];
$last_orders = [];
$debug = [];
$msg = '';
$ip = getenv('HTTP_CLIENT_IP')?:
    getenv('HTTP_X_FORWARDED_FOR')?:
    getenv('HTTP_X_FORWARDED')?:
    getenv('HTTP_FORWARDED_FOR')?:
    getenv('HTTP_FORWARDED')?:
    getenv('REMOTE_ADDR');
$ip = is_array($ip) ? array_pop($ip) : $ip;

//get the menu
$stmt = $dbh->prepare("SELECT * FROM booze");
$stmt->execute();
$menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

//get the last_orders
$startPK = ['uid' => ['type' => 'INF_MIN']];
$endPK = ['uid' => ['type' => 'INF_MAX']];
$request = [
    'table_name' => 'footable',
    'direction' => 'BACKWARD',
    'inclusive_start_primary_key' => $endPK,
    'exclusive_end_primary_key' => $startPK,
    'limit' => 5,
    'columns_to_get' => ['ip', 'name', 'ts'],
];
try{
    $last_orders = $otsClient->getRange($request);
    $last_orders = $last_orders['rows'];
}catch (\Exception $e){
    $debug['tablestore_last_orders'] = $e->__toString();
}

//get ranking
$ranking = $redis->zRevRange('top-drinkers', 0, -1, true);
$ranking_drinks = $redis->zRevRange('top-drinks', 0, -1, true);

if (!isset($_GET['drink']) || empty($_GET['drink'])){
	$msg = 'What can i get you?';
}else{
    //query for the booze item
	$stmt = $dbh->prepare("SELECT * FROM booze WHERE id = :bid");
	$stmt->execute([':bid' => (int) $_GET['drink']]);
	$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	if (empty($data)){
		$msg = 'Sorry, but i don\'t have that item.';
		$data = '';
	}else{
	    //booze exists
		$msg = 'One ' . $data[0]['name'] . ' coming right up!';
		$_SESSION['drinks'][$data[0]['name']] = isset($_SESSION['drinks'][$data[0]['name']]) ?
        $_SESSION['drinks'][$data[0]['name']] + 1 : 1;

        //log to tablestore
        $row = $data[0];
        $row['ts'] = microtime(true);
        $row['ip'] = $ip;
        $request = [
            'table_name' => 'footable',
            'condition' => 'IGNORE',
            'primary_key' => [
                'uid' => $redis->incr('uid')
            ],
            'attribute_columns' => $row
        ];
        try{
            $debug['tablestore'] = $otsClient->putRow($request);
        }catch (\Exception $e){
            $debug['tablestore'] = $e->__toString();
        }

        //tweak redis
        $redis->zIncrBy("top-drinkers", 1, $ip);
        $redis->zIncrBy("top-drinks", 1, $data[0]['name']);
    }
}

if (isset($_GET['sober'])){
    $msg = 'clear as gla$$!';
    $_SESSION['drinks'] = [];
    $_SESSION['messages'] = [];
}

$data = [];
$data['menu'] = $menu;
$data['drunk'] = $_SESSION['drinks'];
$data['my-messages'] = $_SESSION['messages'];
$data['last'] = $last_orders;
$data['top-drinkers'] = $ranking;
$data['top-drinks'] = $ranking_drinks;
$data = json_encode($data);
$n = count($last_orders);

$pic = mt_rand(0, 2) + 1;
$host = $_SERVER['HOSTNAME'];

echo <<<PAGE
<!DOCTYPE html>
<html>
<style>
    body{
        text-align:center;
        color:darkblue;
    }
    h2 { color:coral }
    pre { border: grey inset; background-color: lightgrey;}
    .box {
        text-align: left;
        display:inline-block;
        width: 14em;
        vertical-align:top
    }
    .row {
    }
</style>
<head>
<title>AliBabar</title>
</head>
<body>
<h1>Welcome to AliBabar!</h1>
<p><img src="//vasi-test.oss-ap-southeast-1.aliyuncs.com/main/$pic.jpg" width="200"></p>
<h2>Bartender ($host): $msg</h2>
<div class="row">
    <div class="box"><h3>menu:</h3><pre id="menu-json"></pre></div>
    <div class="box"><h3>items drunk:</h3><pre id="drunk-json"></pre></div>
    <div class="box"><h3>my messages:</h3><pre id="my-messages"></pre></div>
</div>
<div style="clear:both"></div>
<div class="row">
    <div class="box"><h3>last $n orders:</h3><pre id="last-json"></pre></div>
    <div class="box"><h3>top drinks:</h3><pre id="top-drinks"></pre></div>
    <div class="box"><h3>top drinkers</h3><pre id="top-drinkers-json"></pre></div>
</div>
</body>
<script type="text/javascript">
    var data = $data;
    document.getElementById("menu-json").innerHTML = JSON.stringify(data['menu'], false, 2);
    document.getElementById("drunk-json").innerHTML = JSON.stringify(data['drunk'], false, 2);
    document.getElementById("my-messages").innerHTML = JSON.stringify(data['my-messages'], false, 2);
    document.getElementById("last-json").innerHTML = JSON.stringify(data['last'], false, 2);
    document.getElementById("top-drinkers-json").innerHTML = JSON.stringify(data['top-drinkers'], false, 2);
    document.getElementById("top-drinks").innerHTML = JSON.stringify(data['top-drinks'], false, 2);
</script>
</html>
PAGE;
