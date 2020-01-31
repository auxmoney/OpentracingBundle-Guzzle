# auxmoney OpentracingBundle - Guzzle

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/auxmoney/OpentracingBundle-Guzzle)
![Travis (.org)](https://img.shields.io/travis/auxmoney/OpentracingBundle-Guzzle)
![Coveralls github](https://img.shields.io/coveralls/github/auxmoney/OpentracingBundle-Guzzle)
![Codacy Badge](https://api.codacy.com/project/badge/Grade/aab701199e104bb6bdb247a4bdf7f5f2)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/auxmoney/OpentracingBundle-Guzzle)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/auxmoney/OpentracingBundle-Guzzle)
![GitHub](https://img.shields.io/github/license/auxmoney/OpentracingBundle-Guzzle)

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
    $bundles[] = new Auxmoney\OpentracingGuzzleBundle\OpentracingGuzzleBundle();
```

```php
    # Symfony 4: bundles.php
    Auxmoney\OpentracingGuzzleBundle\OpentracingGuzzleBundle::class => ['all' => true],
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
