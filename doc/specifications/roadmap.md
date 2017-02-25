ROADMAP
=======

MILESTONES
-------

[DONE]
12. dirs structure
   git setup
6. Request/Response (PSR7 zend-diactoros)
14. PSR7+middleware
7. Hierarchical PHP modules: MVC, routing, modular data/logic
   Routing: uses dirs structure, routing params as action params, call other ctrl/action from ctrl

[DONE-PARTIALY]
2. Core:
       Inversion of ctrl: DI container/service locator (AppContext)
        - move compiler classes to Si
        - config in app configs
       env debug/prod
       ROUTING RELATED: configuration system (all config in 1 place + modules config merged)
13. Project environment: TODO:rkt, quality tools,TODO:tweak, Doxygen, Composer setup, TODO:git hooks
        TODO:make executables dep/call graph, review
3. Templates: twig
   TODO:Assets: embed and concatenate CSS/JS files
5. Logging, dumping data
   Errors handling/presenting/config, exceptions system
   TODO:User messages

[IN PROGRESS]
15. SPA, twig + js templates (server side and client side compiled)
   Hierarchical JS modules (CSS+JS+TPL)

[TODO]
8. async request handling
   async actions handling
9. DDD - as Domain modules
10. Connectivity
11. Authentication

[TO DISPATCH]
Validating
Benchmarks
Assets: image management
cache
html create
fixtures
content management

[SUSPENDED]
1. app execution modes: (concurrency) -  one-shot(DONE), php-pm     - due to instability of React na php-pm

[SHOULD BE APP-RELATED]
4. utils - Underscore
   SimpleTest



CRIB
-------

1.
-------
srcs/views/index.php
    <?php
    header('Content-Type: text/html; charset=utf-8');
    printf('Var is %s', htmlspecialchars(isset($_GET['name']) ? $_GET['name'] : 'World', ENT_QUOTES, 'UTF-8'));
    ?>
> php -S 127.0.0.1:5678     http://localhost:5678/index.php?var=Val

// tests/index.php
class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testHello()
    {
        $_GET['name'] = 'Fabien';

        ob_start();
        include 'index.php';
        $content = ob_get_clean();

        $this->assertEquals('Hello Fabien', $content);
    }
}

2.
-------
// public/index.php
// Turn on error reporting on dev
defined('YII_ENV') or define('YII_ENV', 'dev');
if (YII_ENV == 'dev') {
    error_reporting(E_ALL);
}
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/srcs/framework.php';
require_once CONFIG
RUN APP
> php -S 127.0.0.1:5678 -t public/ public/front.php     http://localhost:5678/front.php?var=Val
> SERV -> public

// srcs/framework.php
ob_start();
require 'index.php';
echo ob_get_clean();

3.
-------

6.
-------
composer require xxx/yyy

7.
-------
composer require xxx/yyy - covention dirs
