<?php

namespace BjyModulus\Service;

use BjyModulus\Module;

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
    public function getModuleCommitHashes($name)
    {
        $modules = Module::getLoadedModules();
        $module = $modules[$name];
        $moduleVersions = array();

        $class = new \ReflectionClass($module);
        $path = dirname($class->getFileName());

        $pathArg = escapeshellarg($path);

        $localHash = exec("cd $pathArg; git rev-parse HEAD");
        if ($localHash == '') {
            $moduleVersions = array(
                'local'        => 'N/A',
                'upstreamHash' => 'N/A',
                'status'       => self::STATUS_GIT_NOT_TRACKED,
            );
        } else {
            $remotes = exec("cd $pathArg; git remote");
            $remotes = explode("\n", $remotes);

            if (array_search("upstream", $remotes) !== false) {
                $upstream = $this->getUpstreamHash($pathArg, 'upstream');
            } else if (array_search("origin", $remotes) !== false) {
                $upstream = $this->getUpstreamHash($pathArg, 'origin');
            } else {
                $upstream = 'n/a';
            }

            $moduleVersions = array(
                'local'        => $localHash,
                'upstreamHash' => $upstream['upstreamHash'],
                'status'       => $upstream['status'],
            );
        }

        return $moduleVersions;
    }

    /**
     * @param $pathArg
     * @param $remote
     * @return array
     */
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
            $status = sprintf(self::STATUS_GIT_BRANCH_DIVERGED, $ahead, $behind);
        } else if ($ahead == 0 && $behind != 0) {
            $status = sprintf(self::STATUS_GIT_BRANCH_BEHIND, $behind);
        } else if ($ahead != 0 && $behind == 0) {
            $status = sprintf(self::STATUS_GIT_BRANCH_AHEAD, $ahead);
        } else {
            $status = self::STATUS_GIT_UP_TO_DATE;
        }

        return array(
            'upstreamHash' => $hash,
            'status'       => $status,
        );
    }
}