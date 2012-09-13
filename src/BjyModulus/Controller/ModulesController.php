<?php

namespace BjyModulus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use BjyModulus\Module;

class ModulesController extends AbstractActionController
{
    public function indexAction()
    {
        $modules = Module::getLoadedModules();

        $moduleVersions = array();
        foreach ($modules as $name => $module) {
            $class = new \ReflectionClass($module);
            $path = dirname($class->getFileName());

            $pathArg = escapeshellarg($path);

            $git = exec("cd $pathArg; git rev-parse HEAD");
            if ($git == '') {
                $git = 'module not tracked by git';
            }

            $moduleVersions[$name] = $git;
        }

        return array('modules' => $moduleVersions);
    }
}
