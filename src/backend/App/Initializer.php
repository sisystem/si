<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

require __DIR__ . '/Ctx.php';

class Initializer
{
    const ENV_PROD = "prod";
    const ENV_DEV = "dev";
    const ENV_CL = "command-line";
    const ENV_CL_DEV = "command-line-dev";

    /**
     *  Initialize framework for production environment.
     */
    public static function initFramework(string $app_dir, string $conf_dir): void
    {
        $dir = $app_dir . "/" . $conf_dir;
        Ctx::setEnv(self::ENV_PROD);
        self::readConfig($dir);
        self::init($app_dir, $conf_dir, false);
        self::start();
    }

    /**
     *  Initialize framework for development environment.
     */
    public static function initFrameworkDev(string $app_dir, string $conf_dir): void
    {
        $dir = $app_dir . "/" . $conf_dir;
        Ctx::setEnv(self::ENV_DEV);
        self::readConfigDev($dir);
        self::validateConfig($dir);
        self::init($app_dir, $conf_dir, true);
        self::initKint();       // only in dev env, composer should not install it on production (dumps left in code will cause error)
        self::registerWhoops(); // same as above
        $debugbar = new \DebugBar\StandardDebugBar();
        Ctx::setRegisters([
            'debugbar' => $debugbar,
        ]);
        self::start();
    }

    /**
     *  Initialize framework for command line environment.
     *  After initialization it is possible to execute command line programs
     *  and use application environment (config, classes etc.).
     */
    public static function initFrameworkCommand(string $app_dir, string $conf_dir): void
    {
        $dir = $app_dir . "/" . $conf_dir;
        Ctx::setEnv(self::ENV_CL);
        self::readConfigDev($dir);
        self::validateConfig($dir);
        self::init($app_dir, $conf_dir, false);
        // we do not start application - some other code/commands will be
        // executed instead of app
    }

    /**
     *  Initialize framework.
     */
    private static function init(string $app_dir, string $conf_dir, bool $debug): void
    {
        Ctx::setLoader(require $app_dir . "/" . Ctx::config()['project']['autoload_file']);
        Ctx::setTplLoader(new \Twig_Loader_Filesystem(['src/backend'], $app_dir));
        Ctx::setTplEngine(new \Twig_Environment(Ctx::tpl_loader(), [
            'cache' => $app_dir."/".Ctx::config()['project']['templates_cache'],
            'debug' => $debug,
            'strict_variables' => true,
        ]));
        Ctx::setAppDir($app_dir);
        Ctx::setConfDir($conf_dir);
        self::setupLogger();
    }

    /**
     *  Initialize Kint variables dumper.
     */
    private static function initKint()
    {
        if (Ctx::env() === self::ENV_DEV) {
            if (Ctx::config()['debug']['dump_output_simple']) {
                \Kint::enabled(\Kint::MODE_PLAIN);      /// pretty (without +)
            } else {
                \Kint::enabled(\Kint::MODE_RICH);       /// pretty and +
                \Kint::$theme = 'original';
            }
        } else if(Ctx::env() === self::ENV_CL_DEV) {
            if (Ctx::config()['debug']['dump_output_simple']) {
                \Kint::enabled(\Kint::MODE_WHITESPACE); /// cli (no colors)
            } else {
                \Kint::enabled(\Kint::MODE_CLI);        /// cli and colors
            }
        } else {
            \Kint::enabled(false);  // initKint() should not be called in prod env, but we disable dumping just in case
        }
    }

    /**
     *  Setup logger.
     */
    private static function setupLogger()
    {
        $logger = new \Si\Ess\Logger();   // log to PHP's logger, threshold DEBUG (all logged), def. options
        $appname = Ctx::config()['app_name'];
        $logger->setEntryFormat("[{$appname}] [{level}] {message}");    // date is set by server/php logger so we ommit it
        Ctx::setLogger($logger);
    }

    /**
     *  Use Whoops to handle errors.
     */
    private static function registerWhoops()
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        if (\Whoops\Util\Misc::isAjaxRequest()) {
            $jsonHandler = new \Whoops\Handler\JsonResponseHandler();
            $jsonHandler->addTraceToOutput(true);    /// give full stack trace
            $jsonHandler->setJsonApi(true);          /// return json:api compliant result
            $whoops->pushHandler($jsonHandler);
        }
        $whoops->register();
    }

    /**
     *  Start application.
     */
    private static function start(): void
    {
        $app_class = Ctx::config()['project']['app_class'];
        $app = new $app_class();
        $oneServer = new \Si\App\OneShotRunner($app);
        $oneServer->run();  /// run = create request, pass req to App, get response from App, send resp
    }

    public static function test()
    {
        echo "test";
    }

    /**
     *  Read config files.
     *  General config settings have local settings appended to them. Local
     *  config overwrites general config.
     */
    private static function readConfig(string $dir): void
    {
        Ctx::setConfig(array_replace_recursive(
            parse_ini_file($dir . "/general.ini", true, INI_SCANNER_TYPED),
            parse_ini_file($dir . "/local.ini", true, INI_SCANNER_TYPED)
        ));
        Ctx::setConfigUser(
            parse_ini_file($dir . "/user_settings.ini", true, INI_SCANNER_TYPED)
        );
    }

    /**
     *  Read config files and config files for development environment.
     *  Main config settings are read and then development settings are appended,
     *  overwriting main settings.
     */
    private static function readConfigDev(string $dir): void
    {
        Ctx::setConfig(array_replace_recursive(
            parse_ini_file($dir . "/general.ini", true, INI_SCANNER_TYPED),
            parse_ini_file($dir . "/local.ini", true, INI_SCANNER_TYPED),
            parse_ini_file($dir . "/dev-general.ini", true, INI_SCANNER_TYPED),
            parse_ini_file($dir . "/dev-local.ini", true, INI_SCANNER_TYPED)
        ));
        Ctx::setConfigUser(
            parse_ini_file($dir . "/user_settings.ini", true, INI_SCANNER_TYPED),
            parse_ini_file($dir . "/dev-user_settings.ini", true, INI_SCANNER_TYPED)
        );
    }

    /**
     *  Validate config files.
     *  Application config files are validated against reference files.
     */
    private static function validateConfig(string $dir): void
    {
        $ref_config_general = parse_ini_file($dir . "/reference_files/general.ini", true, INI_SCANNER_TYPED);
        $ref_config_local = parse_ini_file($dir . "/reference_files/local.ini", true, INI_SCANNER_TYPED);
        $ref_config_user = parse_ini_file($dir . "/reference_files/user_settings.ini", true, INI_SCANNER_TYPED);

        $config = Ctx::config();
        $config_user = Ctx::config_user();

        $errors = "";

        $fun_pattern_config_error = function($config_name, $section, $setting = null)
        {
            if ($setting !== null) {
                return "({$config_name}:[{$section}]:{$setting})";
            } else {
                return "({$config_name}:{$section})";
            }
        };

        foreach ($ref_config_general as $section => $section_val) {
            if (is_array($section_val)) {
                foreach ($section_val as $setting => $setting_val) {
                    if (! isset($config[$section]) || ! isset($config[$section][$setting])) {
                        $errors .= $fun_pattern_config_error("general config", $section, $setting) . " \n ";
                    }
                }
            } else {
                if (! isset($config[$section])) {
                    $errors .= $fun_pattern_config_error("general config", $section) . " \n ";
                }
            }
        }

        foreach ($ref_config_local as $section => $section_val) {
            if (is_array($section_val)) {
                foreach ($section_val as $setting => $setting_val) {
                    if (! isset($config[$section]) || ! isset($config[$section][$setting])) {
                        $errors .= $fun_pattern_config_error("local config", $section, $setting) . " \n ";
                    }
                }
            } else {
                if (! isset($config[$section])) {
                    $errors .= $fun_pattern_config_error("local config", $section) . " \n ";
                }
            }
        }

        foreach ($ref_config_user as $section => $section_val) {
            if (is_array($section_val)) {
                foreach ($section_val as $setting => $setting_val) {
                    if (! isset($config_user[$section]) || ! isset($config_user[$section][$setting])) {
                        $errors .= $fun_pattern_config_error("user config", $section, $setting) . " \n ";
                    }
                }
            } else {
                if (! isset($config_user[$section])) {
                    $errors .= $fun_pattern_config_error("user config", $section) . " \n ";
                }
            }
        }

        if (! empty($errors)) {
            echo($errors);
            throw new \Exception("Configuration miss some settings: " . $errors);
        }
    }

    public static function handleException(\Throwable $e)
    {
        var_dump("TODO: nice error page");
        throw $e;   // rethrow to standard handler which will behave according to php settings
    }
}
