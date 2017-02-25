<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use \Interop\Http\ServerMiddleware\MiddlewareInterface;
use \Interop\Http\ServerMiddleware\DelegateInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Zend\Diactoros\Response\HtmlResponse;

class Router implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $script_name = $request->getServerParams()['SCRIPT_NAME'];
        $path = $request->getUri()->getPath();

        //# Retrieve parameters from URL #//
        $app_path = "";
        $filename = "";
        $pos_ext = strpos($script_name, ".php");
        $pos = 0;

        if (false !== $pos_ext) {
            for ($i = $pos_ext; $i >= 0; --$i) {
                if ($script_name[$i] == '/') {
                    $pos = $i;
                    break;
                }
            }
            $filename = substr($script_name, $pos, $pos_ext - $pos + 4);
            $app_path = substr($script_name, 0, $pos);
            $pos = strpos($path, $filename);
        }

        if (false !== $pos) {
            $path = substr($path, $pos + strlen($filename));
        }

        $path = urldecode(str_replace($app_path, '', $path));
        $parameters = explode("/", trim($path, "/"));

        //# Find routing array #//
        $routingArray = require Ctx::app_dir() . "/" . Ctx::conf_dir() . "/routing.conf.php";
        $fst_param_el = 4;

        if (isset($routingArray[$parameters[0]])) {
            $routingArray = $routingArray[$parameters[0]];
            array_splice($parameters, 0, 1);
            $fst_param_el = 3;
        } else {
            $routingArray = $routingArray['*'];
        }
        var_dump($routingArray);

        $cnt_params = count($parameters);

        end($routingArray);
        $routePath = key($routingArray);            // path in routing array
        reset($routingArray);
        foreach ($routingArray as $rPath => $rAction) {
            $cnt_path = substr_count($rPath, "/");      // first el is "" before first "/"
            if ($cnt_path === $cnt_params) {
                $routePath = $rPath;
                break;
            }
        }
        $routePathComps = explode("/", $routePath);
        $routeCtrl = $routingArray[$routePath][0];      // controller in routing array
        $routeAction = $routingArray[$routePath][1];    // action in routing array

        //# Compute parameter kinds #//
        $len = strlen($routeCtrl);
        for ($i = 1; $i < count($routePathComps); ++$i) {
            $comp = $routePathComps[$i];
            $routeCtrl = str_replace('{'.$comp.'}', ucfirst($parameters[$i - 1]), $routeCtrl);
            $routeAction = str_replace('{'.$comp.'}', ucfirst($parameters[$i - 1]), $routeAction);
        }
        self::replaceHyphens($routeCtrl);
        self::replaceHyphens($routeAction);

        $routingParams = [];

        //# Compute parameters  #//
        for ($i = $fst_param_el; $i < count($parameters); ++$i)
        {
            $multi = [];
            $len = strlen($parameters[$i]);
            $token = "";
            for ($j = 0; $j < $len; ++$j) {
                if ($parameters[$i][$j] === '(') {
                    $token = $parameters[$i][++$j];
                    while (++$j < $len) {
                        if ($parameters[$i][$j] === ')') {
                            if (($j + 1) >= $len) {
                                break;
                            } else if ($parameters[$i][$j + 1] === ',') {
                                ++$j;
                                break;
                            }
                        }
                        $token .= $parameters[$i][$j];
                    }
                    $multi[] = $token;
                    continue;
                } else {
                    $token = $parameters[$i][$j];
                    while (++$j < $len) {
                        if ($parameters[$i][$j] === ',') {
                            break;
                        }
                        $token .= $parameters[$i][$j];
                    }
                    $multi[] = $token;
                    continue;
                }
            }
            if (count($multi) > 1) {
                $routingParams[] = $multi;
            } else if (count($multi) > 0) {
                $routingParams[] = $multi[0];
            }
        }

        $default = function (ServerRequestInterface $request) {
            return new HtmlResponse("DEFAULT"); // TODO, OR should Application define it?
        };

        var_dump($routeCtrl);
        $ctrl = new $routeCtrl($request, $default);
        $ctrl();
        $response = call_user_func_array([$ctrl, $routeAction], $routingParams);

        return $response;
    }

    /** TOREM
     */
    public static function computeRoutingParams2(string $script_name, string $path): array
    {
        //# Retrieve parameters from URL #//
        $app_path = "";
        $filename = "";
        $pos_ext = strpos($script_name, ".php");
        $pos = 0;

        if (false !== $pos_ext) {
            for ($i = $pos_ext; $i >= 0; --$i) {
                if ($script_name[$i] == '/') {
                    $pos = $i;
                    break;
                }
            }
            $filename = substr($script_name, $pos, $pos_ext - $pos + 4);
            $app_path = substr($script_name, 0, $pos);
            $pos = strpos($path, $filename);
        }

        if (false !== $pos) {
            $path = substr($path, $pos + strlen($filename));
        }

        $path = urldecode(str_replace($app_path, '', $path));
        $parameters = explode("/", trim($path, "/"));

        //# Check module type #//
        $first_prefix = "Site";
        $second_prefix = "Page";
        $first_suffix = "";

        $first_param = strtoupper($parameters[0]);
        if ($first_param === "API") {           // we got: 'api'
            array_splice($parameters, 0, 1);
            $first_prefix = "Api";
            $second_prefix = "Data";
            $first_suffix = "V1";
        }

        if ($first_param[0] === "V" && is_numeric($first_param[1])) {
            array_splice($parameters, 0, 1);
            $first_prefix = "Api";
            $second_prefix = "Data";
            $len = strlen($first_param);
            if ($first_param[1] === '0' && $len == 2) { // we got: 'v0'
                $first_suffix = "V1";
            } else { // we got: 'v2' or 'v3' etc.
                $first_suffix = "V";
                for ($i = 1; $i < $len; ++$i) {
                    $first_suffix .= $first_param[$i];
                }
            }
        }

        //# Compute parameter kinds #//
        $app_ns = Ctx::config()['project']['app_namespace'];
        $routingParams = [];
        $routingParams['parameters'] = [];
        if (count($parameters) === 1) {
            if ($parameters[0] === '') {
                //# /
                $routingParams['classname'] = $app_ns."\\".$first_prefix.ucfirst(Ctx::config()['routing']['main_site']).$first_suffix."\\".$first_prefix."Controller";
                $routingParams['function'] = "indexAction";
            } else {
                //# /site
                for ($i = 0; $i < 1; ++$i) {
                    self::replaceHyphens($parameters[$i]);
                }
                $routingParams['classname'] = $app_ns."\\".$first_prefix.ucfirst($parameters[0]).$first_suffix."\\".$first_prefix."Controller";
                $routingParams['function'] = "indexAction";
            }
        } else if (count($parameters) === 2) {
            //# /site/page
            for ($i = 0; $i < 2; ++$i) {
                self::replaceHyphens($parameters[$i]);
            }
            $routingParams['classname'] = $app_ns."\\".$first_prefix.ucfirst($parameters[0]).$first_suffix."\\".$second_prefix.ucfirst($parameters[1])."\\".$second_prefix."Controller";
            $routingParams['function'] = "indexAction";
        } else if (count($parameters) === 3) {
            //# /site/page/ctrl
            for ($i = 0; $i < 3; ++$i) {
                self::replaceHyphens($parameters[$i]);
            }
            $routingParams['classname'] = $app_ns."\\".$first_prefix.ucfirst($parameters[0]).$first_suffix."\\".$second_prefix.ucfirst($parameters[1])."\Controllers\\".ucfirst($parameters[2])."Controller";
            $routingParams['function'] = "indexAction";
        } else if (count($parameters) >= 4) {
            //# /site/page/ctrl/action[/PARAMETERS]
            for ($i = 0; $i <= 4; ++$i) {
                self::replaceHyphens($parameters[$i]);
            }
            $routingParams['classname'] = $app_ns."\\".$first_prefix.ucfirst($parameters[0]).$first_suffix."\\".$second_prefix.ucfirst($parameters[1])."\Controllers\\".ucfirst($parameters[2])."Controller";
            $routingParams['function'] = lcfirst($parameters[3])."Action";
            for ($i = 4; $i < count($parameters); ++$i)
            {
                $multi = [];
                $len = strlen($parameters[$i]);
                $token = "";
                for ($j = 0; $j < $len; ++$j) {
                    if ($parameters[$i][$j] === '(') {
                        $token = $parameters[$i][++$j];
                        while (++$j < $len) {
                            if ($parameters[$i][$j] === ')') {
                                if (($j + 1) >= $len) {
                                    break;
                                } else if ($parameters[$i][$j + 1] === ',') {
                                    ++$j;
                                    break;
                                }
                            }
                            $token .= $parameters[$i][$j];
                        }
                        $multi[] = $token;
                        continue;
                    } else {
                        $token = $parameters[$i][$j];
                        while (++$j < $len) {
                            if ($parameters[$i][$j] === ',') {
                                break;
                            }
                            $token .= $parameters[$i][$j];
                        }
                        $multi[] = $token;
                        continue;
                    }
                }
                if (count($multi) > 1) {
                    $routingParams['parameters'][] = $multi;
                } else if (count($multi) > 0) {
                    $routingParams['parameters'][] = $multi[0];
                }
            }
        }

        return $routingParams;
    }

    private static function replaceHyphens(&$str)
    {
        $idx = 0;
        while (($pos = strpos($str, "-", $idx)) !== false) {
            $idx = $pos + 1;
            $str = substr($str, 0, $pos) . ucfirst(substr($str, $pos + 1));
        }
    }
}




//                          siapp/index.php/ole                                     siapp           siapp/tutu          localhost/si/index.php  localhost/si    localhost/si/index.php/tutu localhost/si/tutu - 404
// SCRIPT_URL' =>               /index.php/ole                                      /               /tutu               -
// SCRIPT_URI' =>               http://siapp/index.php/ole                          http://siapp/   http://siapp/tutu   -
// HTTP_HOST' =>                siapp                                               siapp           siapp               localhost
// SERVER_NAME' =>              siapp                                               siapp           siapp               localhost
// DOCUMENT_ROOT' =>            /home/Storage/Projects/p/si_app/public                                                  /srv/http
// CONTEXT_DOCUMENT_ROOT' =>    /home/Storage/Projects/p/si_app/public                                                  /srv/http
// SCRIPT_FILENAME' =>          /home/Storage/Projects/p/si_app/public/index.php                                        /srv/http/si/index.php
// REQUEST_URI' =>              /index.php/ole                                      /               /tutu               /si/index.php           /si/            /si/index.php/tutu
// PHP_SELF' =>                 /index.php/ole                                      /               /tutu               /si/index.php           /si/index.php   /si/index.php/tutu
// PATH_INFO                    -                                                   -               -                   -                       -               /tutu
//
// SCRIPT_NAME' =>              /index.php/ole                                      /               /tutu               /si/index.php           /si/index.php   /si/index.php
//                  app_path    rm /index.php... => ""                              => ""           => ""               => /si                  => /si          => /si
// PAth                         /index.php/ole                                      /               /tutu               /si/index.php           /si/            /si/index.php/tutu
//                              rm ...index.php => /ole                             => /            => /tutu            => /                    => /si/         /tutu
//                              rm app_path => /ole                                 => /            => /tutu            => /                    => /            => /tutu
//                              trim /      => ole                                  => ""           => tutu             => ""                   => ""           => tutu
//
//                              => ole                                              => /            => tutu             => /                    => /            => tutu
//
