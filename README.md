# NSSM PHP

A lightweight PHP wrapper for [NSSM](https://nssm.cc/) (the Non-Sucking Service Manager). This package allows you to manage Windows services using PHP by providing a fluent interface to interact with the NSSM binary.

## Installation

You can install the package via composer:

```bash
composer require abdulbasset/nssm-php
```

## Requirements

- PHP 8.3 or higher
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

### Log Rotation

You can configure log rotation for stdout and stderr files. By default, enabling rotation will rotate files when the service starts or restarts.

```php
use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\NssmSet;
use Abdulbasset\NssmPhp\NssmRotation;

$nssm = new Nssm('MyService');

$nssm->set(function (NssmSet $set) {
    // Basic rotation (enables AppRotateFiles)
    $set->rotation();

    // Advanced rotation configuration
    $set->rotation(function (NssmRotation $rotation) {
        // Rotate every 1 hour (accepts seconds or DateInterval)
        $rotation->everySeconds(new \DateInterval('PT1H'));

        // Rotate when file size exceeds 1 MB (in bytes)
        $rotation->everyBytes(1024 * 1024);

        // Enable rotation while the service is running (online rotation)
        $rotation->online();
    });
});

// To disable rotation
$nssm->set(fn(NssmSet $set) => $set->rotation(false));
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

### Full Example

Here's a comprehensive example of how to install, configure, and manage a Windows service (Running Octane Server for example):

```php
use Abdulbasset\NssmPhp\Nssm;
use Abdulbasset\NssmPhp\NssmSet;
use Abdulbasset\NssmPhp\NssmRotation;
use Abdulbasset\NssmPhp\Startup;
use Abdulbasset\NssmPhp\Status;

$nssm = new Nssm('MyAppOctaneServer');

$nssm->bin(PHP_BINARY)
    ->install(
        base_path('artisan'),
        'octane:start',
        '--quiet',
        '--port:8000'
    )
    ->set(fn(NssmSet $set) => $set
        ->displayName(config('app.name') . ' Octane Server')
        ->description('High-performance HTTP server for Laravel using ' . config('octane.driver') . '.')
        ->startup(Startup::Delayed)
        ->appDirectory(base_path())
        ->error(storage_path('logs/octane-err.log'))
        ->output(storage_path('logs/octane-out.log'))
        ->rotation()// enable it on service start/restart
        ->rotation(false) // disable rotation
        ->rotation(function (NssmRotation $rotation) {
            $rotation
                ->online()
                ->everySeconds(\Carbon\CarbonInterval::minutes(2))
                ->everyBytes(64 * 1024);
        })
    );
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
