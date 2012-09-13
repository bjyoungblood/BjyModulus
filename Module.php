<?php

namespace BjyModulus;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;

class Module implements AutoloaderProviderInterface
{
    protected static $loadedModules = array();

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'bjymodulus_listener' => 'BjyModulus\ModuleListener',
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach('loadModules.post', array($this, 'modulesLoaded'));
    }

    public function modulesLoaded(ModuleEvent $e)
    {
        self::$loadedModules = $e->getTarget()->getLoadedModules();
    }

    public static function getLoadedModules()
    {
        return self::$loadedModules;
    }
}
