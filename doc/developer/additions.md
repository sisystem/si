Additions
=======

rkt (Rocket)
-------
containers manager, for providing environment for framework
`install with OS package manager`
[getting started](https://coreos.com/blog/getting-started-with-rkt-1-0.html)
rkt fetch --insecure-options=image docker://nginx
rkt run --net=host --volume=data,kind=host,source=$PWD/public --mount volume=data,target=/usr/share/nginx/html docker://nginx

Project environment
-------
### composer package
Si is composer package, it is now available from bitbucket repository
### Doxygen
code documentation system
`install with OS package manager`
Doxyfile        - config
$ doxygen       - generate documentation (to doc/html/)
### Quality tools
`composer require "squizlabs/php_codesniffer"`
`composer require "sebastian/phpcpd=*"`
`composer require 'phploc/phploc=*'`
`composer require "pdepend/pdepend=*"`
`composer require "phpmd/phpmd=*"`
    metrics descr: https://pdepend.org/documentation/software-metrics/index.html
`composer require 'phpmetrics/phpmetrics'`
`composer require phpmetrics/phpmetrics phpmetrics/composer-extension`
    metrics descr: www.phpmetrics.org/documentation/index.html
$ bin/quality_tools/check       - run all checks
### Deployer
deployment/build tool
`composer require deployer/deployer`
$ bin/dep build

Configuration
-------
uses native parse_ini_file() to read and parse ini files to associative arrays
uses array_replace_recursive() to merge settings into one array (later settings overwrite former)
accessible from Ctx static methods

Environments
-------
separate front files, separate (inheriting) config files
Ctx::env() holds environment name

Modules, Project structure, Routing, Middleware
-------
### Modules, Project Structure
Backend: Site, Part, Api, Tools
see Requirements
### Routing
config/routing.conf.php, route parameters as action parameters, rerouting to other controller
see Requirements
### Middleware
in Application and in Controller
see Requirements

Errors, debugging
-------
### general errors handling
all errors converted to exceptions and catched globally
on production: exceptions rethrowed to standard PHP exception handling (will behave accoridng to PHP settings)
on development: exceptions rethrowed to Whoops (for pretty display)
### Kint
`composer require-dev raveren/kint`
### Logger
custom logger: implemets PSR-3
uses PHP's standard logger, log file is defined with 'error_log' setting
Ctx::logger()->info('message to log');
### Whoops!
`composer require-dev filp/whoops`
### debugbar
`composer require-dev maximebf/debugbar`
profiling data about execution of app

Zend-Diactoros
-------
implementation of PSR-7 HTTP message interfaces
`composer require zendframework/zend-diactoros`
[usage](https://zendframework.github.io/zend-diactoros/usage/)

equip/dispatch
-------
implementation of PSR-15 middleware
one-pass type: process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
PSR-1/2/4/7/15 compliant
`composer require equip/dispatch`
[usage](https://github.com/equip/dispatch)
[middleware interface](https://github.com/http-interop/http-middleware/blob/master/src/MiddlewareInterface.php)

Zen (woohoolabs)
-------
implementation of PSR-11 containers
dependency injection container + ev. service locator
`composer require woohoolabs/zen`
[usage](https://github.com/woohoolabs/zen)
$ ./vendor/bin/di_container/compile.php     #compile container
general.ini [di_container]      - compile settings
di_container.conf.php           - configuration

### Ctx
static class
si/src/backend/Base/App/Ctx.php

Templates - Twig
-------
`composer require "twig/twig:~2.0"`
$twig = \Si\Base\App\Ctx::tpl_engine();
$html = $twig->render('lobby.html.twig', []);   /// taken from current page 'views'
{% extends "SiteMain/PageHall/layout.html.twig" %}
{% extends "SiteMain/layout.html.twig" %}
