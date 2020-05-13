# `superoffice-webapi`

[![Latest Stable Version](https://poser.pugx.org/roydejong/superoffice-webapi/version)](https://packagist.org/packages/roydejong/superoffice-webapi)
[![Travis CI Build](https://travis-ci.org/roydejong/superoffice-webapi-php-sdk.svg?branch=master)](https://travis-ci.org/github/roydejong/superoffice-webapi-php-sdk)

***PHP SDK for SuperOffice Web API***

This library provides an unofficial PHP SDK for the SuperOffice [REST WebAPI](https://community.superoffice.com/documentation/sdk/SO.NetServer.Web.Services/html/Reference-WebAPI-REST-REST.htm).

⚠ **Note: This library is a work in progress, and currently only targets CRM Online (SuperOffice Cloud).**

## Installation
The recommended way to install this library is with [Composer](http://getcomposer.org/), by adding the [package](https://packagist.org/packages/roydejong/superoffice-webapi) as a dependency to your project:

    composer require roydejong/superoffice-webapi
    
## Configuration

You will need to be registered as a SuperOffice developer, and you must have a registered app to receive the necessary client credentials.

### Initializing

When initializing the client, you must pass a `Config` object:

```php
<?php

use roydejong\SoWebApi\Client;
use roydejong\SoWebApi\Config;

$config = new Config();
$config->environment = "sod";
$config->tenantId = "Cust12345";
// ...

$client = new Client($config);
```

You can also set the configuration values by array:

```php
new Config([
    'environment' => "sod",
    'tenantId' => "Cust12345"
    // ...
]);
```

### Options
All of the following options must be set:

|Key|Type|Required?|Description|
|---|----|--------|-----------|
|`environment`|`string`|☑|SuperOffice environment (`sod`, `stage` or `online`).|
|`tenantId`|`string`|☑|Customer / Context ID, usually in the format `Cust12345`.|
|`clientId`|`string`|☑|Client ID (Application ID).|
|`clientSecret`|`string`|☑|Client secret (Application Token).|
|`privateKey`|`string`| |Private key for system user token signing (`<RSAKeyValue>` block).|
