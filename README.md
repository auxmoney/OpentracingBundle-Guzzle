# auxmoney OpentracingBundle - Guzzle

This bundle adds automatic header injection for Guzzle clients to the [OpentracingBundle](https://github.com/auxmoney/OpentracingBundle-core).

## Installation

### Prerequisites

This bundle is only an additional plugin and should not be installed independently. See
[its documentation](https://github.com/auxmoney/OpentracingBundle-core#installation) for more information on installing the OpentracingBundle first.

### Require dependencies

After you have installed the OpentracingBundle:

* require the dependencies:

```bash
    composer req auxmoney/opentracing-bundle-guzzle:^0.3
```

### Enable the bundle

If you are using [Symfony Flex](https://github.com/symfony/flex), you are all set!

If you are not using it, you need to manually enable the bundle:

* add bundle to your application:

```php
    # Symfony 3: AppKernel.php
    $bundles[] = new Auxmoney\OpentracingBundleGuzzle\OpentracingGuzzleBundle();
```

```php
    # Symfony 4: bundles.php
    Auxmoney\OpentracingBundleGuzzle\OpentracingGuzzleBundle::class => ['all' => true],
```

## Configuration

No configuration is necessary, the provided compiler pass will try to enhance existing `Client`s by adding a middleware to their `HandlerStack`s.

## Usage

When sending a request to other systems, the tracing headers are automatically injected into the requests, thus enabling the full power of distributed tracing.

## Development

Be sure to run

```bash
    composer run-script quality
```

every time before you push code changes. The tools run by this script are also run in the CI pipeline.
