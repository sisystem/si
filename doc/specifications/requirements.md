REQUIREMENTS
=======

PHP Framework
-------
This Software is Framework for PHP Applications.

Project and directories structure
-------
- simple
- modular
- hierarchical
- separate realms (frontend/backend)

Application directories/files of developers concern:
- src/App/Application.php
- src/SITES,MODULES

PSR-7 Request/Response
-------
zend-diactoros
- PSR-7 compliant
- easy create server-side request from PHP global variables (mod_php)
- easy create server-side custom request (fastcgi)
response body format: Modules, Routing, Project structure, Response body format

PSR-15 Middleware on PSR-7
-------
equip/dispatch      OTHER: oscarotero/middleland, idealo/php-middleware-stack
- PSR-15: compliant
- light implementation

PSR-11 containers compatible
-------
woohoolabs/zen - di container
- PSR-11 compliant (even if prosposed draft)
- light
- autowiring, configured by code, optionally by config files

### IoC container
1. DI container (pattern) - depend on interfaces, not implementations
    - constr inj
    - setter inj
    - interface inj: class implements interf, provides method to inject and container uses this method to inject
2. Service locator (AppContext?) - singleton registry
    - simple: static methods to return services
    - role interfaces: locator implements few interfaces (client decides which use), static is only meth to get locator instance
        service is taken from locator instance
    - dynamic: locator uses map ("name":service)
    - Dependency container: container is injected and then creates deps
        - def. constructor uses SL to retrieve deps
        - def. constructor passes deps to "real" constructor
3. Both:
    - inject locator
    - use it to get services

### PSR-11
Container interoperability:
->has() ->get() + exceptions
ev. delegates (lookup in related containers)

### Ctx
service locator implemented as static class
should be used sparingly, only for special services (config, debug, autoloader)

Configuration
-------
- config kinds:
    - application{-dev} (same on all app instances, ex. app file paths, class names, external urls)
    - application{-dev}.local (dep. on app instance environment, ex. db, system file paths, local urls)
    - module{-dev}, module{-dev}.local (as above)
    - user{-dev} (defaults for usser settings)
- *inheritance must be possible (including other config files plus overwrite some settings)
- ROUTING RELATED: all kinds must be merged but only for current module (no other modules config in current module)
- *all outside public directory
- available to dependencies (ex. doctrine or zen must be able to use it in own configs)

### Concepts
1. Kinds:
    - general - in git
    - local (user specific) - outside git
2. Formats:
    - ini - php has native funs to parse
        - more readable
        - understandable by non-programmer
        - more portable
        - no performance impact at all
    - php array - $conf = include "config.php" { return (object) array [ ... ]      ;send to client: $http.get('config.php')
        - light
        - easy to include in code

Environments: production, development
-------
1. separate front files for each environment: index.php, index-dev/test.php
    - efficient
    - clear separation
2. additional config files for other then prod env (inheriting prod settings)
    - convenient overwrite of settings
3. current env name accessible in application code via global context (Ctx)
    - covenient, accessible everywhere (for debugging etc.)

### How others do it
Yii2: constants YII_DEBUG=true/false, YII_ENV=prod/dev/test set in prod/dev/index.php
Zend2: httpd variable APP_ENV, retrieved in application.config.php and used in conditions, config:global/local-dev.php
Symfony2: prod/dev passed to kernel in index/dev.php,  config: config_dev/prod/test.yml

Project environment
-------
framework env:      composer package, doxygen, quality tools, build
application env:    rkt, build+deploy
1. composer package - for now it is avail. from bitbucket repo, later we will make proper packagist package
2. Documentation - Doxygen
    - developer docs:               generate documentation from sources
    - client programmer api doc:    generate documentation from sources
    - client programmer manual:     generate documentation from markdown files
3. Quality tools    TODO: tweak
4. Build/Deploy - Deployer, TODO:git hooks
    - fw: should run: quality checks, composer dump-autoload, doxygen
    - app: should run: composer dump-autoload, zen build, TODO:deploy, TODO:rkt
      (it does not and shouldn't run fw build!)

Templates
-------
- twig is default engine        TODO:install C extension
- TODO:Html create - common html structures
    - form/data manager
    - table/list/data manager (tables/list can post data like forms)
    - menu/gateway keeper
- TODO:Modals/PopUps/FlashMessages

### Twig
`composer require "twig/twig:~2.0"`
```
$loader = new Twig_Loader_Array(array(          /// locate template
    'index' => 'Hello {{ name }}!',
));
$twig = new Twig_Environment($loader);          /// store config
echo $twig->render('index', ['name'=>'text']);  /// load and render tpl
/// ----------
$loader = new Twig_Loader_Filesystem(['/tpl/DIR', 'DIR2'], getcwd().'/..');     /// def. getcwd() for relative paths
$loader->add/prependPath('DIR3', 'namespace');
$twig = new Twig_Environment($loader, [
    'cache' => '/cache/DIR',    /// def. false
    'debug' => true,            /// tpl has __tosString() to display generated nodes, includes 'auto_reload'
    'strict_variables' => true, /// def. false, undef. var: Exception (not NULL)
]);
echo ($tpl = $twig->load('index.html'))->render([VARS]);
echo $twig->render('@namespace/index.html', ['name'=>'text']);
echo $tpl->renderBlock('BLOCK_NAME', [VARS]);   /// render individual block
/// ----------
$loader = new Twig_Loader_Chain(array($loader1, $loader2));     /// use other loaders in turn
$loader->addLoader($loader3);
```

Errors, debugging
-------
- Logging, Dumping
- Errors handling: phperror.net, Whoops (register onyl for debug)
    - handle errors in one place
- TODO (when routing in place): 404/500 error handling
- TODO: User messages handling (info msg scrrens/popups, error screens/popups)
- TODO: Benchmarks - Ubench

### general errors handling
- production:
    - run-time unexpected errors (fopen(), db connect) - custom handler: throw Exception (however in PHP 7 most errors are thrown as Error Exception)
    - run-time exceptions (thrown by programmer) - catch globally + show error page (don't display details) + rethrow to standard handler (which will behave according to ini directives)
- development:
    - run-time unexpected errors (fopen(), db connect) - custom handler: throw Exception (however in PHP 7 most errors are thrown as Error Exception)
    - run-time exceptions (thrown by programmer) - catch globally + rethrow to Whoops

### dumping - Kint
`composer require-dev raveren/kint`
```
require '/kint/Kint.class.php';
Kint::enabled(false);       - put this in prod env, accidental dumps will not show
Kint::enabled(Kint::MODE_WHITESPACE/PLAIN/RICH/CLI);
Kint::$theme = 'original/solarized/solarized-dark/aante-light';
d($var);    ===     Kint::dump($var, $var2);
ddd($var);  - dump and die()
d(1);       ===     Kint::trace();
s($var);    - basic js-free display
sd($var);   - basic js-free display and die()
~+!-d($var);- plain text, otuput everything (deep), expanded output, ob_clean prev output (inside html page to see debug output)
D - trawerse with arrows/Tab, expand with space/Enter
Kint::dump(microtime());
ddd(microtime(), "final call after XXX");
$out = d($var);
```
### debug bar - debugbar
`composer require-dev maximebf/debugbar`
$debugbar = new \DebugBar\StandardDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer();
$debugbarRenderer->setBaseUrl("debugbar_resources");
$debugbar["messages"]->addMessage("hello world!");
<head> <?php echo $debugbarRenderer->renderHead() ?> </head>
<body> <?php echo $debugbarRenderer->render() ?> </body>

### Logging
https://www.loggly.com/ultimate-guide/php-logging-basics/
- php engine errors
- custom errors (application triggered, ex. wrong user input): error_log(), trigger_error(), Exception
- custom logging (activity in app)
php.ini:
    error_reporting         - level of error reporting
    display_errors          - send errors with normal output    ;false for production!
    display_startup_errors  - startup errors display
    error_log               - path to http server writable file ;used by error_log()
    log_errors              - log errors?
ini_set('directive', val);

### Whoops
`composer require-dev filp/whoops`
```
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
if (\Whoops\Util\Misc::isAjaxRequest()) {
    $jsonHandler = new \Whoops\Handler\JsonResponseHandler();
    // $jsonHandler->addTraceToOutput(true);    /// give full stack trace
    // $jsonHandler->setJsonApi(true);          /// return json:api compliant result
    $whoops->pushHandler($jsonHandler);
}
    $handler->setPageTitle("It's broken!"); // Set the page's title
    $handler->setEditor("sublime");         // Set the editor used for the "Open" 
    $handler->addDataTable("Extra Info", array(     // additional data
        "stuff"     => 123,
        "foo"       => "bar",
        "useful-id" => "baloney"
    ));
$whoops->register();
```

Modules, Project structure, Routing, Middleware
-------

### Project structure
config/         --- project configuration
src/backend/    --- backend sources
    BACKEND MODULES
src/frontend/   --- frontend sources
    FRONTEND MODULES

### Backend Modules
SiteNAME/PageNAME/      Controllers/LobbyController.php
        /config/
PartNAME/ElNAME/        Controllers/PanelController.php,DropdownController.php
                        views/
ApiNAMEV1/DataNAME/     Controllers/EmployerController.php    ::get(),save()...
ToolsNAME/UtilNAME/     Services/ Utils/ Validators/

### Frontend Modules
PartNAME/ElNAME/
SiteNAME/PageNAME/      Controllers/LobbyCtrl.ts
                        Models/
                        views/index.twig,viewA.twig
                        styles/
PartNAME/ElNAME/

### Routing
/                                   => SiteNAME/SiteCtrl::index() - conf
/site                               => SiteNAME/SiteCtrl::index()
/site/page                          => SiteNAME/PageNAME/PageCtrl::index()
/site/page/ctrl                     => SiteNAME/PageNAME/Controllers/CtrlCtrl::indexAction()
/site/page/ctrl/action              => SiteNAME/PageNAME/Controllers/CtrlCtrl::actionAction()
/site/page/ctrl/action/PAR1/PAR2                                             ::actionAction(PAR1,PAR2)
/site/page/ctrl/action/(PAR1),(PAR2)/PAR3,PAR4                               ::actionAction([PAR1,PAR2],[PAR3,PAR4])

/api/apiname/dataname/ctrl/action/PARS  => ApiNAME/DataNAME/Controllers/CtrlCtrl::actionAction()
/v1/...     => ApiNAME/...
/v2/...     => ApiNAMEV2/...

ROUTING ARRAY:
    // first URL component - default (if any other not found)
    '*' => [
        "/" =>                              ["\App\SiteMain\SiteController", "indexAction"],
        "/site" =>                          ["\App\Site{site}\SiteController", "indexAction"],
        "/site/page" =>                     ["\App\Site{site}\Page{page}\PageController", "indexAction"],
        "/site/page/ctrl" =>                ["\App\Site{site}\Page{page}\Controllers\{ctrl}Controller", "indexAction"],
        "/site/page/ctrl/action" =>         ["\App\Site{site}\Page{page}\Controllers\{ctrl}Controller", "{action}Action"],
    ],
    // first URL component - 'api'
    'api' => [
        "/group/entity/operation" =>        ["\App\ApiPrime\Data{group}\Controllers\{entity}Controller", "{operation}Action"],
    ],
    'v1' => [
        "/group/entity/operation" =>        ["\App\ApiPrime\Data{group}\Controllers\{entity}Controller", "{operation}Action"],
    ],
    'v2' => [
        "/group/entity/operation" =>        ["\App\ApiPrimeV2\Data{group}\Controllers\{entity}Controller", "{operation}Action"],
    ],

name-site/name-page/name-ctrl/name-action   => SiteNameSite/PageNamePage/Controllers/NameCtrlCtrl::nameActionAction()

**Parameter groups:**
(PAR1),(PAR2)/PAR3,PAR4  => ::action([PAR1,PAR2],[PAR3,PAR4])
Controller can return multiple responses

**Reroute to other controller:**
return $this->reroute("\App\ApiPrime\DataHuman\Controllers\BossController", "someAction", [$id]);

### Middleware
**Application**
```
$this->set([
    new \App\Sites\TestMiddleware("before"),
    new \Si\App\Router(),
]);
$this->setAfter([
    new \App\Sites\TestMiddleware("after1"),
    new \App\Sites\LastMiddleware("after2"),
]);
```
**Controller**
```
__invoke():
$this->setGeneral([
    new \App\Sites\TestMiddleware("ctrl ALL 1"),
    new \App\Sites\ResponseMiddleware("ctrl Action 1 response"),
]);
someAction():
$this->setSpecific([
    new \App\Sites\TestMiddleware("ctrl Action 2"),
]);
$response = $this->dispatch();
return $response;
```
