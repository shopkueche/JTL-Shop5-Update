<?php declare(strict_types=1);

namespace Systemcheck;

use Systemcheck\Tests\AbstractApacheConfigTest;
use Systemcheck\Tests\AbstractTest;
use Systemcheck\Tests\PhpConfigTest;
use Systemcheck\Tests\PhpModuleTest;
use Systemcheck\Tests\ProgramTest;

/**
 * Class Environment
 * @package Systemcheck
 */
class Environment
{
    /**
     * @var bool
     */
    protected $passed = false;

    /**
     * @return bool
     */
    public function getIsPassed(): bool
    {
        return $this->passed;
    }

    /**
     * Enumerate tests for a specific test group name
     *
     * @param string $group
     * @return array
     */
    private function getTests(string $group): array
    {
        $files  = [];
        $folder = __DIR__ . \DIRECTORY_SEPARATOR . 'Tests' . \DIRECTORY_SEPARATOR . $group;
        if (\is_dir($folder) && ($dh = \opendir($folder)) !== false) {
            while (($file = \readdir($dh)) !== false) {
                // skip hidden files too! (starting with dots in 'nix-like systems),
                // and skip "_"-starting files, to make "deactivation" as simple as possible in the filesystem
                if ($file === '.' || $file === '..' || \strpos($file, '.php') === false) {
                    continue;
                }
                if (\is_dir($folder . '/' . $file)) {
                    continue;
                }
                $files[] = $file;
            }
            \closedir($dh);
        }

        foreach ($files as $key => $file) {
            $files[$key] = 'Systemcheck\Tests\\' . $group . '\\' . \rtrim($file, '.php');
        }

        return $files;
    }


    /**
     * @param string $group
     * @return array
     */
    public function executeTestGroup(string $group): array
    {
        $result         = [
            'recommendations' => [],
            'apache_config'   => [],
            'php_config'      => [],
            'php_modules'     => [],
            'programs'        => []
        ];
        $this->passed   = true;
        $completedTests = [];
        foreach ($this->getTests($group) as $test) {
            /** @var AbstractTest $instance */
            $instance = new $test();
            // check a property here, if that test is "replacable by one other".
            // if that is the case, we skip this test (and "continue;" to the next one).
            if (($replacement = $instance->getIsReplaceableBy()) !== false) {
                // prevents double execution of one Test
                if (\array_key_exists($replacement, $completedTests)) {
                    $replacementResult = $completedTests[$replacement]->getResult();
                } else {
                    /** @var AbstractTest $replacementTest */
                    $replacementTest = new $replacement();
                    $replacementTest->setResult($replacementTest->execute());
                    $replacementResult = $replacementTest->getResult();
                }
                // a Test can replaced by Another, if the Other is "optional" (and/or "recommend")
                if ($replacementResult === AbstractTest::RESULT_OK) {
                    continue; // skip the "execution" and "listing" of this current test
                }
            }
            $completedTests[\get_class($instance)] = $instance; // store "completed" to prevent double-testing above

            $instance->setResult($instance->execute());
            if ($instance->getResult() !== AbstractTest::RESULT_OK) {
                if (!$instance->getIsOptional()) {
                    $this->passed = false;
                } elseif ($instance->getIsRecommended()) {
                    $result['recommendations'][] = $instance;
                }
            }

            if ($instance instanceof AbstractApacheConfigTest) {
                $result['apache_config'][] = $instance;
            } elseif ($instance instanceof PhpConfigTest) {
                $result['php_config'][] = $instance;
            } elseif ($instance instanceof PhpModuleTest) {
                $result['php_modules'][] = $instance;
            } elseif ($instance instanceof ProgramTest) {
                $result['programs'][] = $instance;
            }
        }

        return $result;
    }
}
