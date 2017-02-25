<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

/**
 *  Application context.
 *  Service locator for special services only (ex. config, debug, autoloader)
 *  that can be accessed everywhere without dependency injection. Every
 *  property can be set only once and cannot be modified afterwards. They are
 *  usually set at framework initialization, effectively being read-only in
 *  application code.
 *
 *  @note Fully static class, accessible everywhere in the code. Use sparingly
 *  and with care.
 */
class Ctx
{
    protected static $loader = null;        /// autoloader
    protected static $tpl_loader = null;    /// templates loader
    protected static $tpl_engine = null;    /// templates environment
    protected static $config = null;        /// application config
    protected static $config_user = null;   /// user settings
    protected static $registers = null;     /// custom registers
    protected static $env = null;           /// <string> application environment name
    protected static $app_dir = null;       /// <string> top application directory
    protected static $conf_dir = null;      /// <string> configuration subdirectory
    protected static $logger = null;        /// logger

    //# setters - one time #//
    public static function setLoader($loader): void
    {
        if (self::$loader === null) {
            self::$loader = $loader;
        }
    }

    public static function setTplLoader($tpl_loader): void
    {
        if (self::$tpl_loader === null) {
            self::$tpl_loader = $tpl_loader;
        }
    }

    public static function setTplEngine($tpl_engine): void
    {
        if (self::$tpl_engine === null) {
            self::$tpl_engine = $tpl_engine;
        }
    }

    public static function setConfig(array $config): void
    {
        if (self::$config === null) {
            self::$config = $config;
        }
    }

    public static function setConfigUser(array $config_user): void
    {
        if (self::$config_user === null) {
            self::$config_user = $config_user;
        }
    }

    public static function setRegisters(array $registers): void
    {
        if (self::$registers === null) {
            self::$registers = $registers;
        }
    }

    public static function setEnv(string $env): void
    {
        if (self::$env === null) {
            self::$env = $env;
        }
    }

    public static function setAppDir(string $app_dir): void
    {
        if (self::$app_dir === null) {
            self::$app_dir = $app_dir;
        }
    }

    public static function setConfDir(string $conf_dir): void
    {
        if (self::$conf_dir === null) {
            self::$conf_dir = $conf_dir;
        }
    }

    public static function setLogger($logger): void
    {
        if (self::$logger === null) {
            self::$logger = $logger;
        }
    }

    //# getters #//
    public static function loader()
    {
        return self::$loader;
    }

    public static function tpl_loader()
    {
        return self::$tpl_loader;
    }

    public static function tpl_engine()
    {
        return self::$tpl_engine;
    }

    public static function config(): array
    {
        return self::$config;
    }

    public static function config_user(): array
    {
        return self::$config_user;
    }

    public static function registers(): array
    {
        return self::$registers;
    }

    public static function register(string $key)
    {
        //if ( ! isset(self::$registers[$key])) {
            //throw new \Exception("register {$key} not set");
        //}

        return self::$registers[$key];
    }

    public static function env(): string
    {
        return self::$env;
    }

    public static function app_dir(): string
    {
        return self::$app_dir;
    }

    public static function conf_dir(): string
    {
        return self::$conf_dir;
    }

    public static function logger()
    {
        return self::$logger;
    }
}
