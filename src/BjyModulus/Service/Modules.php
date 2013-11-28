<?php

namespace BjyModulus\Service;

use BjyModulus\Module;

/**
 * Class Modules
 * @package BjyModulus\Service
 */
class Modules
{
    const STATUS_GIT_BRANCH_DIVERGED = 'Your branch has diverged; %d ahead, %d behind';
    const STATUS_GIT_BRANCH_AHEAD    = 'Your branch is %d ahead';
    const STATUS_GIT_BRANCH_BEHIND   = 'Your branch is %d behind';
    const STATUS_GIT_UP_TO_DATE      = 'Up to date';
    const STATUS_GIT_NOT_TRACKED     = 'Module not tracked by git';

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleLoaded($moduleName)
    {
        if (in_array((string) $moduleName, Module::getLoadedModules())) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name Module name
     */
    public function getModuleInfo($name)
    {
        $modules = Module::getLoadedModules();
        $module = $modules[$name];
        $moduleInfo = array(
            'localHash'  => 'N/A',
            'remoteHash' => 'N/A',
            'version'    => 'N/A',
            'status'     => self::STATUS_GIT_NOT_TRACKED,
        );;

        $class = new \ReflectionClass($module);
        $path = dirname($class->getFileName());
        $pathArg = escapeshellarg($path);

        $localHash = exec("cd $pathArg; git rev-parse HEAD");
        if ($localHash != '') {
            $remotes = exec("cd $pathArg; git remote");
            $remotes = explode("\n", $remotes);

            if (array_search("upstream", $remotes) !== false) {
                $remote = $this->getRemoteRepoInfo($pathArg, 'upstream');
            } else if (array_search("origin", $remotes) !== false) {
                $remote = $this->getRemoteRepoInfo($pathArg, 'origin');
            } else {
                $remote = 'n/a';
            }

            $moduleInfo = array(
                'localHash'    => $localHash,
                'remoteHash'   => is_array($remote) ? $remote['remoteHash'] : $remote,
                'version'      => is_array($remote) ? $remote['version'] : $remote,
                'status'       => is_array($remote) ? $remote['status'] : $remote,
            );
        }

        return $moduleInfo;
    }

    /**
     * @param $pathArg
     * @param $remote
     * @return array
     */
    protected function getRemoteRepoInfo($pathArg, $remote)
    {
        exec("cd $pathArg; git fetch $remote");
        $ahead = $behind = array();
        exec("cd $pathArg; git rev-list $remote/master..HEAD", $ahead);
        exec("cd $pathArg; git rev-list HEAD..$remote/master", $behind);

        $ahead = count($ahead);
        $behind = count($behind);

        $remoteHash = exec("cd $pathArg; git rev-parse $remote/master");
        $version = exec("cd $pathArg; git describe --long --tags --always");

        if ($ahead != 0 && $behind != 0) {
            $status = sprintf(self::STATUS_GIT_BRANCH_DIVERGED, $ahead, $behind);
        } else if ($ahead == 0 && $behind != 0) {
            $status = sprintf(self::STATUS_GIT_BRANCH_BEHIND, $behind);
        } else if ($ahead != 0 && $behind == 0) {
            $status = sprintf(self::STATUS_GIT_BRANCH_AHEAD, $ahead);
        } else {
            $status = self::STATUS_GIT_UP_TO_DATE;
        }

        return array(
            'remoteHash' => $remoteHash,
            'version'    => $version,
            'status'     => $status,
        );
    }
}