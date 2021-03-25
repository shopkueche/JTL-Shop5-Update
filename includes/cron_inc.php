<?php

use JTL\Cron\Checker;
use JTL\Cron\JobFactory;
use JTL\Cron\Queue;
use JTL\Shop;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

defined('JTLCRON') || define('JTLCRON', true);
if (!defined('PFAD_LOGFILES')) {
    require __DIR__ . '/globalinclude.php';
}
if (SAFE_MODE === true) {
    return;
}
if (PHP_SAPI === 'cli') {
    $handler = new StreamHandler('php://stdout', Logger::DEBUG);
    $handler->setFormatter(new LineFormatter("[%datetime%] %message% %context%\n", null, false, true));
    $logger = new Logger('cron', [$handler]);
} else {
    $logger = Shop::Container()->getLogService();
    if (isset($_POST['runCron'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ignore_user_abort(true);
        header('Connection: close');
        ob_start();
        echo 'Starting cron';
        $size = ob_get_length();
        header('Content-Length: ' . $size);
        ob_end_flush();
        flush();
    }
}
$db     = Shop::Container()->getDB();
$cache  = Shop::Container()->getCache();
$runner = new Queue($db, $logger, new JobFactory($db, $logger, $cache));
$runner->run(new Checker($db, $logger));
