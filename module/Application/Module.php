<?php

namespace Application;

use InvalidArgumentException,
    Zend\Config\Config,
    Zend\Di\Locator,
    Zend\Dojo\View\HelperLoader as DojoLoader,
    Zend\EventManager\EventCollection,
    Zend\EventManager\StaticEventCollection,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    protected $appListeners    = array();
    protected $staticListeners = array();
    protected $view;

    public function init()
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'cacheRules'));
        $events->attach('bootstrap', 'bootstrap', array($this, 'initView'));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function initView($e)
    {
        $app     = $e->getParam('application');
        $config  = $e->getParam('config');
        $locator = $app->getLocator();
        $router  = $app->getRouter();

        $view    = $locator->get('Zend\View\Renderer\PhpRenderer');
        $url     = $view->plugin('url');
        $url->setRouter($router);

        $persistent = $view->placeholder('layout');
        foreach ($config->view as $var => $value) {
            if ($value instanceof Config) {
                $value = new Config($value->toArray(), true);
            }
            $persistent->{$var} = $value;
        }

        $view->doctype('HTML5');
        $view->getBroker()->getClassLoader()->registerPlugins(new DojoLoader());
        $view->headTitle()->setSeparator(' :: ')
                          ->setAutoEscape(false)
                          ->append('phly, boy, phly');
        $view->headLink(array(
            'rel'  => 'shortcut icon',
            'type' => 'image/vnd.microsoft.icon',
            'href' => '/images/Application/favicon.ico',
        ));
        $dojo = $view->plugin('dojo');
        $dojo->setCdnVersion('1.6')
             ->setDjConfig(array(
                 'isDebug'     => true,
                 'parseOnLoad' => true,
             ));
        $this->view = $view;
    }

    public function cacheRules($e)
    {
        if (!class_exists('Cache\Module', false)) {
            return;
        }

        $app      = $e->getParam('application');
        $locator  = $app->getLocator();
        $cacheListener = $locator->get('Cache\Listener');
        $cacheListener->addRule(function($e) {
            if (!$e instanceof \Zend\Mvc\MvcEvent) {
                return;
            }

            $routeMatch = $e->getRouteMatch();
            if (in_array($routeMatch->getMatchedRouteName(), array('default', 'comics'))) {
                // Do not cache 404 requests or the comics page
                return true;
            }
            return false;
        });
    }

    public function getProvides()
    {
        return array(
            'name'    => 'Application',
            'version' => '0.1.0',
        );
    }

    public function getDependencies()
    {
        return array(
            'php' => array(
                'required' => true,
                'version'  => '>=5.3.1',
            ),
            'ext/mongo' => array(
                'required' => true,
                'version'  => '>=1.2.0',
            ),
            'Blog' => array(
                'required' => true,
                'version'  => '>=0.1.0',
            )
        );
    }
}
