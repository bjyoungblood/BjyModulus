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

            $localHash = exec("cd $pathArg; git rev-parse HEAD");
            if ($localHash == '') {
                $localHash = 'module not tracked by git';
            }

            $remotes = exec("cd $pathArg; git remote");
            $remotes = explode("\n", $remotes);

            if (array_search("upstream", $remotes) !== false) {
                $upstream = $this->getUpstreamHash($pathArg, 'upstream');
            } else if (array_search("origin", $remotes) !== false) {
                $upstream = $this->getUpstreamHash($pathArg, 'origin');
            } else {
                $upstream = 'n/a';
            }

            $moduleVersions[$name] = array(
                'local'        => $localHash,
                'upstreamHash' => $upstream['upstreamHash'],
                'status'       => $upstream['status'],
            );
        }

        return array('modules' => $moduleVersions);
    }

    protected function getUpstreamHash($pathArg, $remote)
    {
        exec("cd $pathArg; git fetch $remote");
        $ahead = $behind = array();
        exec("cd $pathArg; git rev-list $remote/master..HEAD", $ahead);
        exec("cd $pathArg; git rev-list HEAD..$remote/master", $behind);

        $ahead = count($ahead);
        $behind = count($behind);

        $hash = exec("cd $pathArg; git rev-parse $remote/master");

        if ($ahead != 0 && $behind != 0) {
            $status = "Your branch has diverged; $ahead ahead, $behind behind";
        } else if ($ahead == 0 && $behind != 0) {
            $status = "Your branch is $behind behind";
        } else if ($ahead != 0 && $behind == 0) {
            $status = "Your branch is $ahead ahead";
        } else {
            $status = "Up to date.";
        }

        return array(
            'upstreamHash' => $hash,
            'status'       => $status,
        );
    }
}
