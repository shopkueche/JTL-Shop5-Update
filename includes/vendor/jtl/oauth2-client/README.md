# JTL-Software GmbH Provider for OAuth 2.0 Client

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides JTL-Software GmbH OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Usage

Usage is the same as The League's OAuth client, using `\Jtl\OAuth2\Client\Provider\Jtl` as the provider.

### Authorization Code Flow

```php
$provider = new Jtl\OAuth2\Client\Provider\Jtl([
    'clientId'          => '{jtl-client-id}',
    'clientSecret'      => '{jtl-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);
```

For further usage of this package please refer to the [core package documentation on "Authorization Code Grant"](https://github.com/thephpleague/oauth2-client#usage).

#### Managing scopes with your authorization request

```php
$options = [
    'scope' => ['profile', 'email', 'phone', 'address']
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```

### Refreshing a Token

```php
$provider = new Jtl\OAuth2\Client\Provider\Jtl([
    'clientId'          => '{jtl-client-id}',
    'clientSecret'      => '{jtl-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

$existingAccessToken = getAccessTokenFromYourDataStore();

if ($existingAccessToken->hasExpired()) {
    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $existingAccessToken->getRefreshToken()
    ]);

    // Purge old access token and store new access token to your data store.
}
```

For further usage of this package please refer to the [core package documentation on "Refreshing a Token"](https://github.com/thephpleague/oauth2-client#refreshing-a-token).


## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
