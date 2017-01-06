<?php
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
*/

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

//get ranking
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
