`$Mail` - is system object, that provides mail sending functionality. In fact, it is simple wrapper around [https://github.com/Synchro/PHPMailer](PHPMailer) class (Mail class extends PHPMailer class with one method), and is initialized with system settings to make usage as much simple, as it is possible. But is you need extended features, please refer to PHPMailerclass documentation, instance can be obtained in such way:
```php
<?php
$Mail	= \cs\Mail::instance();
```

### [Methods](#methods)

<a name="methods" />
###[Up](#) Methods

`$Mail` object has only one public method:
* send_to()

#### send_to($email : array|string|string[], $subject : string, $body : string, $body_text = null : string|null, $attachments = null : array|null|string, $reply_to = null : array|null|string|string[], $signature = true  : bool|string) : bool
This method allows to send message using system configuration (sender, if used - smtp settings, etc).

Example:
```php
<?php
$Mail	= \cs\Mail::instance();
$Mail->send_to('login@my.name', 'Title', 'Some message here');
```
