SCOPE
=======

CONCEPTS
-------
Request     - http request made by client
Response    - http response from server to client
Action      - single or multiple in Request


MAIN FEATURES
-------

**PSR-15 Middleware on PSR-7**
- uses piped middleware between req/resp (req -> midd1 -> midd2 -> resp)

**PSR-11 containers compatible**
- Inversion of control:
    - Service locator (AppContext?)
    - Dependency container

**Modularity, Hierarchical structure**
- modular application design
- structured UI-backend modules:
    - kinds:
        - Http-Site: Site layout { Page layout { content + viewports
        - Http-API:  Engine api { Facility api { appliance               ;REST?
        - Tool: Tool { Utils             ;transformers,validators,Html
        - Connector: Tool { Utils           ;db,fs,logs,ws
        - Domain: Boundary { Aggregates     ;entities, value objs, use cases
    - routing based along modules structure
    - SPA (single page application) utilized; related to modules structure
- structured UI-frontend modules:
    - kinds:
        - Site: same as PHP - site/page specific (visual effects, ui support etc.)
        - Logic: Tool { Utils
        - Conveyer: Tool { Utils
    - CSS, JS, HTML/TPL files are part of modules
- structured frontend logic modules:        module { ev.submodules
- structured frontend data(ajax) modules
- structured logic modules
- structured data modules
- structured other modules

**Concurrency, Multioperations**
- concurrency: workers in memory handle concurrent requests
    - One-shot app - standard run-die execution (invokation by Http Server)
    - run on php-pm (reactPHP based)
        - OR: FastCGI Server - implements FastCGI protocol, runs as FastCGI server
        - HTTP Server - runs as HTTP server (behinf nginx/other balancer): ReactPHP, Ratchet, Icicle
- multiple operations with single request, concurrent or sequential (X/V)
- allows concurrent functionalities execution within operation

**Other**
- optional WebSocket communication
- html creators: forms, lists, tables, menus, pagination

**Separation of realms**
- client and server are treated as separate application
    - on first request server just sends JS application to WB (it may attach additonal data to display first page
    - on next requests client interact with user and server (even when first page requested again - possible?)
- project-level separation of frontend (client) and backend (server) parts of application
    - communication/conversion channel between server and client part
    - objects load/save and presentation
- optional old style frontend sources within backend part

**Separation of domains**
- DDD - Domain Driven Design
  - Bound Contexts - above modules
- data organized as entities with relations
    - entity repositories
    - entity helpers/managers/mappers(to DB)
    - entity lens/facades (for narrowing interface or combining multiple entities)
    - validation
- use cases

**Convention vs customization**
Where must convention over customization:
    - modules layout
    - modules structure, routing
    - data model (entities)
Where can customization over convention:
    - configuration structure
    - choose template engines
    - choose database systems and libraries
    - choose frontend libraries (Pure/Bootstrap, AngularJS/ReactJS? Vue.js(components)! ) ;http://engineering.paiza.io/entry/2015/03/12/145216
        - requirejs! (modules,deps,encapsulation)


INTERNAL FUNCTIONALITIES
-------

**Project environment**
- Documentation (Doxygen)
- Make Si a composer package (and move psr-4 from app composer.json to si composer.json)
- Build/deploy tools (one build script for: composer autoload, container compile, etc.)
    - JS: npm!
- Quality tools
- containers (rkt)
- other tools

**Errors, debugging**
- Logging, Dumping
- Errors handling: phperror.net, Whoops (register onyl for debug)
- 404/500 error handling
- User messages handling (info msg scrrens/popups, error screens/popups)
- Benchmarks - Ubench

**Base**
- Autoloading - optimize composer, get rid of dbg libs from autoload table
- http requests/response - PSR7: Guzzle, EasyRequest, Zend-diactoros, Slim, Symfony            (http clients: Buzz, Unirest)
- Validating - Validation, Filterus
- Authentication

**Framework**
- debug/prod env
- separate Config credentials outside Git
- Session + Cookies - proper session management
    - gen ses_id when security lvl changes (loggingin), even at every request
    - set session timeout
    - check $_SERVER['HTTP_USER_AGENT'] ?
- Assets
    - manage: Assetic?  (js/css from)
    - images management - ImageWorkshop, Imagine (powerfull)
- utils - Underscore
- cache: OpCache + APCu + Varnish?
- Framework Events?

**Connectivity**
- DB communication
    - ORM - Idiorm
    - DB - medoo
- FS communication
- uploaded files validate and save - Upload

**Templates**
- twig
- html create - form/data editor, table/list/data presenter, menu/gateway
- Modals/PopUps/FlashMessages

**Data, fixtures**
- SimpleTest
- fixtures + fake data - Faker

**Content management**
- Content managemnt (articles, descriptions): entities vs plain database entries
