<?php
global $User, $Index;
$Index->stop = !$User->is('admin');