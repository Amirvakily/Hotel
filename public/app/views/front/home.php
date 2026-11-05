<?php
/** @var array $sections */
foreach ($sections as $s) {
    $tpl = 'front/sections/' . preg_replace('~[^a-z0-9_]~', '', $s['skey']);
    if (is_file(APP . '/views/' . $tpl . '.php')) {
        echo View::partial($tpl, ['d' => $s['data']]);
    }
}
