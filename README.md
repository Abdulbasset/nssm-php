# NSSM PHP

A lightweight PHP wrapper for [NSSM](https://nssm.cc/) (the Non-Sucking Service Manager). This package allows you to manage Windows services using PHP by providing a fluent interface to interact with the NSSM binary.

## Installation

You can install the package via composer:

```bash
composer require abdulbasset/nssm-php
```

## Requirements

- PHP 8.2 or higher
- [NSSM](https://nssm.cc/download) binary must be available in your system's PATH, or you can specify the path to the binary manually.

## Usage

### Basic Service Management

```php
use Abdulbasset\NssmPhp\Nssm;

$nssm = new Nssm('MyService');

// Start the service
$nssm->start();

// Stop the service
$nssm->stop();

// Restart the service
$nssm->restart();
```

### Installing and Removing Services

```php
use Abdulbasset\NssmPhp\Nssm;

$nssm = new Nssm('MyService');

// Install service (with application path and arguments)
// The bin() method sets the path to the executable (e.g., php.exe)
$nssm->bin('C:\path\to\your\app.exe')->install('arg1', 'arg2');

// Remove service
$nssm->remove();
```

### Configuring Service Parameters

You can set various service parameters using the `set` method. It provides a fluent interface to set multiple parameters at once:

```php
use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\Startup;
use Abdulbasset\NssmPhp\NssmSet;

$nssm = new Nssm('MyService');

$nssm->set(function (NssmSet $set) {
    $set->displayName('My Custom Service Name')
        ->description('This is a custom service managed by PHP')
        ->startup(Startup::Automatic)
        ->appDirectory('C:\path\to\app')
        ->output('C:\path\to\logs\stdout.log')
        ->error('C:\path\to\logs\stderr.log');
});
```

### Get Service Status

The `status()` method returns an instance of the `Abdulbasset\NssmPhp\Status` enum, or `null` if the status is unknown.

```php
use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\Status;

$nssm = new Nssm('MyService');
$status = $nssm->status();

if ($status === Status::Running) {
    echo "Service is running!";
}

// Convenient helper methods:
if ($status?->running()) { ... }
if ($status?->pending()) { ... } // Returns true for StartPending or StopPending
if ($status?->exists()) { ... }  // Returns true if the service exists (not NotFound)
```

### Custom NSSM Binary Path

If `nssm` is not in your system's PATH, you can specify its location during instantiation or using the `nssm()` method:

```php
use Abdulbasset\NssmPhp\Nssm;

// During instantiation
$nssm = new Nssm('MyService', 'C:\path\to\nssm.exe');

// Or using the nssm() method
$nssm->nssm('C:\path\to\nssm.exe');
```

## Testing

The package uses [Pest](https://pestphp.com/) for testing.

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
