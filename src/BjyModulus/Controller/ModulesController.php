<?php

namespace BjyModulus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use BjyModulus\Module;

class ModulesController extends AbstractActionController
{
    public function indexAction()
    {
        $modulesService = $this->getServiceLocator()->get('bjymodulus_modules_service');
        $modules = Module::getLoadedModules();

        $moduleVersions = array();
        foreach ($modules as $name => $module) {
            $moduleVersions[$name] = $modulesService->getModuleInfo($name);
        }

        return array('modules' => $moduleVersions);
    }
}
