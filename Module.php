<?php

namespace BjyModulus;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;

class Module implements AutoloaderProviderInterface
{
    protected static $loadedModules = array();

    /**
     * @return array
     */
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

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @param \Zend\ModuleManager\ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'onModulesLoaded'));
    }

    /**
     * @param \Zend\ModuleManager\ModuleEvent $e
     */
    public function onModulesLoaded(ModuleEvent $e)
    {
        self::$loadedModules = $e->getTarget()->getLoadedModules();
    }

    /**
     * @return array
     */
    public static function getLoadedModules()
    {
        return self::$loadedModules;
    }
}
