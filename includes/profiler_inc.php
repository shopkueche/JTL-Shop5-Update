<?php

use JTL\Profiler;

Profiler::savePluginProfile();
Profiler::saveSQLProfile();
Profiler::output();
if (Profiler::getIsStarted() === true) {
    Profiler::finish();
    $data = Profiler::getData();
    echo $data['html'];
}
