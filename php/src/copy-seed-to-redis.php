<?php

require_once __DIR__.'/vendor/autoload.php';

const IP_BAN_THRESHOLD = 10;
const REDIS_KEY_IPS = "ips";

function exportIpFailures(PDO $db): array
{
    $stmt = $db->prepare('
    select
      log.ip,
      ifnull(last.last_succeeded_id, 0) as last_succeeded_id
    from login_log as log
    left join (select ip, max(id) as last_succeeded_id from login_log where succeeded = 1 group by ip) as last on last.ip = log.ip
    group by ip
    ');
    $stmt->execute();
    $ipAndLastSucceededIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $failuresCountEachIp = [];

    foreach ($ipAndLastSucceededIds as $r) {
        $ip = $r['ip'];
        assert(is_string($ip));
        $lastSucceededId = $r['last_succeeded_id'];
        assert(is_numeric($lastSucceededId));
        $stmt = $db->prepare('
    select count(*) as failure_count
    from login_log
    where
        ip = ?
        and id > ?
        and succeeded = 0
    ');
        $stmt->execute([$ip, $lastSucceededId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $failuresCountEachIp[$ip] = (int) $r['failure_count'];
    }

    return $failuresCountEachIp;
}

function importIpFailures(array $ips, Predis\Client $redis)
{
    foreach ($ips as $ip => $count) {
        $redis->hset(REDIS_KEY_IPS, $ip, $count);
    }
}

function main()
{
    $host = getenv('ISU4_DB_HOST') ?: 'localhost';
    $port = getenv('ISU4_DB_PORT') ?: 3306;
    $dbname = getenv('ISU4_DB_NAME') ?: 'isu4_qualifier';
    $username = getenv('ISU4_DB_USER') ?: 'root';
    $password = getenv('ISU4_DB_PASSWORD');
    $db = new PDO(
        'mysql:host=' . $host . ';port=' . $port. ';dbname=' . $dbname,
        $username,
        $password,
        [ PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET `utf8`',
        ]
    );

    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => getenv('ISU4_REDIS_HOST'),
        'port'   => 6379,
    ]);

    $ips = exportIpFailures($db);
    importIpFailures($ips, $redis);
}

main();
