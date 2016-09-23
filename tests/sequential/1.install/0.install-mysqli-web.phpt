--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi driver');
}
?>
--INI--
phar.readonly = Off
--FILE--
<?php
include __DIR__.'/_install_prepare.php';
// TODO: xdebug causes everything to crash if code coverage is used in following test, likely caused by https://bugs.xdebug.org/view.php?id=1322
system('SKIP_COVERAGE=1 '.PHP_BINARY.' -d variables_order=EGPCS -d xdebug.default_enable=0 '.__DIR__.'/0.install-mysqli-web-0.php');
system(PHP_BINARY.' -d variables_order=EGPCS -d xdebug.default_enable=0 '.__DIR__.'/0.install-mysqli-web-1.php');
?>
--CLEAN--
<?php
include __DIR__.'/../_clean.php';
?>
--EXPECTF--
<!doctype html>
<title>CleverStyle Framework %s Installation</title>
<meta charset="utf-8">
<style>*{font-family:sans-serif;font-size:16px}body{background-color:#b9b9b9;margin:0;padding:0}header{background-color:#d7d7d7;border-bottom:5px #666 solid;padding:10px}header svg{display:inline;height:128px;width:auto}header h1{display:inline-block;font-size:72px;font-weight:100;line-height:128px;margin:0 -10px 0 0;vertical-align:top}section{background-color:#d7d7d7;border:1px #666 solid;margin:100px 0 0 50%;padding:15px 10px 45px 15px;position:relative;width:520px}section nav label{background-color:#666;color:#aaa;cursor:pointer;display:inline-block;height:30px;line-height:30px;position:relative;padding:0 30px}section nav label:nth-of-type(1){margin-right:-10px}section nav label:nth-of-type(2)::before{background-color:#d7d7d7;content:"";height:30px;position:absolute;left:0;transform:skewX(-20deg);width:5px}section nav :checked+span{color:#ddd}section nav input{display:none}section table{margin-top:10px}section table input,section table select{border:none;font-size:14px;padding:3px;width:300px}section table select{width:306px}section table pre{margin:0}section button{background-color:#666;border:none;bottom:15px;color:#ddd;cursor:pointer;padding:4px 20px;position:absolute}section button.license{left:15px}section button[type=submit]{right:15px}section .expert{display:none}footer{padding:20px;text-align:center}
</style>
<header>
	<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewbox="0 0 300 300"><path d="M174.893 250h-50m0 0s-100 0-100-100 100-100 100-100h150m-150 100h100" fill="none" stroke="#000" stroke-width="30" stroke-linecap="round"/></svg>	<h1>Installation</h1>
</header>
<section><form method="post">
	<nav>
		<label is="cs-label-button">
			<input checked name="mode" onclick="var items = document.querySelectorAll(&apos;.expert&apos;), i; for (i = 0; i &lt; items.length; i++) items[i].style.display = this.value == &apos;0&apos; ? &apos;table-row&apos; : &apos;&apos;;" type="radio" value="1"> <span>Regular user</span>
		</label>
		<label is="cs-label-button">
			<input name="mode" onclick="var items = document.querySelectorAll(&apos;.expert&apos;), i; for (i = 0; i &lt; items.length; i++) items[i].style.display = this.value == &apos;0&apos; ? &apos;table-row&apos; : &apos;&apos;;" type="radio" value="0"> <span>Expert</span>
		</label>
	</nav>
	<table>
		<tr>
			<td>Site name:</td>
			<td>
				<input name="site_name" type="text">
			</td>
		</tr>
		<tr class="expert">
			<td>Database driver:</td>
			<td>
				<select name="db_driver" size="3">
					<option selected value="MySQLi">MySQLi</option>
					<option value="PostgreSQL">PostgreSQL</option>
					<option value="SQLite">SQLite</option>
				</select>
			</td>
		</tr>
		<tr class="expert">
			<td>Database host:</td>
			<td>
				<input name="db_host" placeholder="Relative or absolute path to DB for SQLite" type="text" value="localhost">
			</td>
		</tr>
		<tr>
			<td>Database name:</td>
			<td>
				<input name="db_name" type="text">
			</td>
		</tr>
		<tr>
			<td>Database user:</td>
			<td>
				<input name="db_user" type="text">
			</td>
		</tr>
		<tr>
			<td>Database user password:</td>
			<td>
				<input name="db_password" type="password">
			</td>
		</tr>
		<tr class="expert">
			<td>Database tables prefix:</td>
			<td>
				<input name="db_prefix" type="text" value="%s_">
			</td>
		</tr>
		<tr>
			<td>Timezone:</td>
			<td>
				<select name="timezone" size="7">
					<option value="Pacific/Midway">Pacific/Midway (-11:00)</option>
					%a
					<option value="Pacific/Kiritimati">Pacific/Kiritimati (+14:00)</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Language:</td>
			<td>
				<select name="language" size="3">
					<option selected value="English">English</option>
					<option value="Russian">Russian</option>
					<option value="Ukrainian">Ukrainian</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Email of administrator:</td>
			<td>
				<input name="admin_email" type="email">
			</td>
		</tr>
		<tr>
			<td>Administrator password:</td>
			<td>
				<input name="admin_password" type="password">
			</td>
		</tr>
	</table>
	<button class="license" onclick="window.open(&apos;license.txt&apos;, &apos;license&apos;, &apos;location=no&apos;)" type="button">License</button>
	<button type="submit">Install</button>
</form>
</section>
<footer>Copyright (c) 2011-%d, Nazar Mokrynskyi</footer>
<!doctype html>
<title>CleverStyle Framework %s Installation</title>
<meta charset="utf-8">
<style>*{font-family:sans-serif;font-size:16px}body{background-color:#b9b9b9;margin:0;padding:0}header{background-color:#d7d7d7;border-bottom:5px #666 solid;padding:10px}header svg{display:inline;height:128px;width:auto}header h1{display:inline-block;font-size:72px;font-weight:100;line-height:128px;margin:0 -10px 0 0;vertical-align:top}section{background-color:#d7d7d7;border:1px #666 solid;margin:100px 0 0 50%;padding:15px 10px 45px 15px;position:relative;width:520px}section nav label{background-color:#666;color:#aaa;cursor:pointer;display:inline-block;height:30px;line-height:30px;position:relative;padding:0 30px}section nav label:nth-of-type(1){margin-right:-10px}section nav label:nth-of-type(2)::before{background-color:#d7d7d7;content:"";height:30px;position:absolute;left:0;transform:skewX(-20deg);width:5px}section nav :checked+span{color:#ddd}section nav input{display:none}section table{margin-top:10px}section table input,section table select{border:none;font-size:14px;padding:3px;width:300px}section table select{width:306px}section table pre{margin:0}section button{background-color:#666;border:none;bottom:15px;color:#ddd;cursor:pointer;padding:4px 20px;position:absolute}section button.license{left:15px}section button[type=submit]{right:15px}section .expert{display:none}footer{padding:20px;text-align:center}
</style>
<header>
	<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewbox="0 0 300 300"><path d="M174.893 250h-50m0 0s-100 0-100-100 100-100 100-100h150m-150 100h100" fill="none" stroke="#000" stroke-width="30" stroke-linecap="round"/></svg>	<h1>Installation</h1>
</header>
<section><h3>Congratulations! CleverStyle Framework has been installed successfully!</h3>
<table>
	<tr>
		<td colspan="2">Your sign in information:</td>
	</tr>
	<tr>
		<td>Login:</td>
		<td><pre>admin</pre></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><pre>1111</pre></td>
	</tr>
	<p style="color: red"></p>
	<button onclick="location.href = '/';">Go to website</button>
</table></section>
<footer>Copyright (c) 2011-%d, Nazar Mokrynskyi</footer>
