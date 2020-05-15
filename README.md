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
<?php

new Config([
    'environment' => "sod",
    'tenantId' => "Cust12345"
    // ...
]);
```

### Options
Available configuration options:

|Key|Type|Required?|Description|
|---|----|--------|-----------|
|`environment`|`string`|☑|SuperOffice environment (`sod`, `stage` or `online`).|
|`tenantId`|`string`|☑|Customer / Context ID, usually in the format `Cust12345`.|
|`clientId`|`string`|For OAuth|Client ID (Application ID).|
|`clientSecret`|`string`|For OAuth|Client secret (Application Token).|
|`redirectUri`|`string`|For OAuth|OAuth callback URL. Must exactly match a redirect URI registered with SuperOffice.|
|`privateKey`|`string`|?|Private key for system user token signing (`<RSAKeyValue>` block).|

## Usage

### Authentication (OAuth / SuperId)
If you are targeting Online CRM, you must use OAuth to aquire a `BEARER` access token for the web api.

Local must use `BASIC` / `SOTICKET` authentication methods are currently not supported

#### 1. Redirect user to authorization screen
After setting your configuration, you can ask the client to generate the OAuth authorization URL:

```php
<?php 

use roydejong\SoWebApi\Client;

$client = new Client(/* $config */);
$redirectUrl = $client->getOAuthAuthorizationUrl("optional_state");
````

This will generate a redirect URL like `https://env-name.superoffice.com/login/common/oauth/authorize?client_id=...`.

When you redirect the user to this URL, they will be asked to authorize your application and grant access to their account.

#### 2. Request access token 
Once the user authorizes your app, you will receive a callback request on your configured `requestUri`.

You can can exchange the `code` parameter in the request for an access token:

```php
$tokenResponse = $client->requestOAuthAccessToken($_GET['code']);
```

The `TokenResponse` object contains the following keys:

|Key|Type|Description|
|---|----|-----------|
|`token_type`|`string`|Should be set to `Bearer`.|
|`access_token`|`string`|The actual access token.|
|`expires_in`|`int`|The lifetime in seconds of the access token.|
|`refresh_token`|`string`|Can be used to generate access tokens, as long as the user hasn't revoked application access.|
|`id_token`|`string`|JSON Web Token (JWT), can be used to verify that the tokens came from the real SuperId server.|

Your application is responsible for storing these tokens.

