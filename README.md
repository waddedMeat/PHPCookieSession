
# Cookie Session Handler #


### Requires ###

 * PHP >= 5.3


### Composer Install ###

Add the following to `composer.json` and execute `composer update`

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

It is recommended that you encrypt your session, so you will need to create an encryption class that implements `Loco\Crypt\CipherInterface`


```
$handler->setCipher($myEncryptionClass);
```

Then you need to set the save handler (note: Despite having all of the necessary methods, ClientSession does not implement \SessionHandlerInterface for BC purposes)

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

You **MUST** call `session_write_close` **BEFORE** returning any output or you will lose all session data from the request.




