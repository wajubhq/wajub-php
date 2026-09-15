<p align="center"><a href="https://wajub.com" target="_blank"><img src="/art/logo.svg" width="280" alt="Wajub"></a></p>

<p align="center">
<a href="https://github.com/wajubhq/wajub-php/actions/workflows/tests.yml"><img src="https://github.com/wajubhq/wajub-php/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/dt/wajub/wajub-php" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/v/wajub/wajub-php" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/l/wajub/wajub-php" alt="License"></a>
</p>

## Introduction

The Wajub PHP SDK is the server side of a [Wajub](https://wajub.com) integration. It holds your secret key, creates payments, reads their real status and verifies webhook signatures, so you can accept mobile money and card payments across Africa from any PHP 8.4+ application, with first-class support for Laravel.

```php
use Wajub\Wajub;

$wajub = new Wajub(['api_key' => config('services.wajub.secret')]);

$payment = $wajub->payments->create([
    'amount' => 25000,
    'currency' => 'XAF',
    'email' => 'amina@example.com',
    'callback' => route('checkout.complete'),
]);

return redirect()->away($payment->authorization_url);
```

## Official Documentation

Documentation for the PHP SDK can be found on the [Wajub website](https://docs.wajub.com/libraries/sdks/php).

## Contributing

Thank you for considering contributing to the Wajub PHP SDK! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Security Vulnerabilities

Please review [our security policy](https://github.com/wajubhq/wajub-php/security/policy) on how to report security vulnerabilities.

## License

The Wajub PHP SDK is open-sourced software licensed under the [MIT license](LICENSE.md).
