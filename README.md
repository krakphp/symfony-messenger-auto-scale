# Symfony Messenger Auto Scaling

The Symfony Messenger Auto Scaling package provides the ability to dynamically scale the number of workers for a given set of receivers to respond to dynamic workloads.

It's not uncommon for certain types of workloads to fluctuate throughput for lengthy periods of time that require the number of queue consumers to dynamically scale to meet demand. With this auto scaling package, that is now achievable with symfony's messenger system.

## Installation

Install with composer at `krak/symfony-messenger-auto-scale`.

If symfony's composer install doesn't automatically register the bundle, you can do so manually:

```php
<?php

return [
  //...
  Krak\SymfonyMessengerAutoScale\MessengerAutoScaleBundle::class => ['all' => true],
];
```

## Usage

### Standalone

### Within Symfony Framework

```yaml
messenger_auto_scale:
  pools:
```

## Dashboards


## Testing

You can run the test suite with: `composer test`

You'll need to start the redis docker container locally in order for the Feature test suite to pass.

Keep in mind that you will need to have the redis-ext installed on your local php cli, and will need to start up the redis instance in docker via `docker-compose`.
