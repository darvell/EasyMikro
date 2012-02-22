EasyMikro
----------

EasyMikro is a PHP abstraction layer for the [PHP Mikrotik API] to make things even simpler. It obviously does not have too many features, this has been built for a very specific purpose but I figure someone out there might find a use for it.

You **need** the [PHP Mikrotik API] in the same folder as EasyMikro to get this to work. You should be smart enough to know that though if you're looking at this! 

Example Usage
-------------

Here we'll use the class to create a new user and check if it actually exists after creation.

```php 
require('EasyMikro.php');
$mikro = new EasyMikro("127.0.0.1","admin","password");

if($mikro->IsConnected())
{
  $mikro->AddUser("darvell","password","profile-cool");
  if($mikro->UserExists("darvell"))
    echo "User added!";
}
```

  [PHP Mikrotik API]: http://wiki.mikrotik.com/wiki/API_PHP_class