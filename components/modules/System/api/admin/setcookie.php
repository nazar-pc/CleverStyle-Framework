<?php
global $Page;
$data = $_POST['data'];
$Page->content((int)_setcookie($data['name'], $data['value'], $data['expire'], $data['httponly'], true));