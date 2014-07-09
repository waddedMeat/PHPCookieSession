
# Cookie Session Handler #


### Requires ###

 * PHP >= 5.3


### Composer Install ###

```
"minimum-stability": "dev",
"require": {
    "locosoftworks/php-cookie-session": "dev-master"
}
```


### Usage ###

```
$handler = new Loco\Session\SaveHandler\ClientSession();
```

It is recommended that you encrypt the session.  Create a class that implements `Loco\Crypt\CipherInterface` and inject it into the session handler


```
$handler->setCipher($myEncryptionClass);
```

Set the session save handler using `session_set_save_handler()` (see [php documentation](http://www.php.net/manual/en/function.session-set-save-handler.php))

```
session_set_save_handler(
    array($handler, 'open'),
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
);

session_start();
```

You **MUST** call `session_write_close` **BEFORE** returning any output.  [Output Buffering](http://www.php.net/manual/en/book.outcontrol.php) is recommended.




