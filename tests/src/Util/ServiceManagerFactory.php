<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace SlmQueueTest\Util;

use Laminas\ServiceManager\ServiceManager;
use SlmQueue\ConfigProvider;

/**
 * Utility used to retrieve a freshly bootstrapped application's service manager
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ServiceManagerFactory
{
    /**
     * @var array
     */
    protected static $config;

    /**
     * @param array $config
     */
    public static function setConfig(array $config): void
    {
        static::$config = $config;
    }

    /**
     * Builds a new service manager without relying on Laminas MVC ServiceManagerConfig
     */
    public static function getServiceManager(): ServiceManager
    {
        // Base configuration from the library's ConfigProvider (PSR-11 style)
        $provider = new ConfigProvider();
        $baseConfig = $provider(); // returns ['dependencies' => ..., 'slm_queue' => ..., 'laminas-cli' => ...]

        // Merge testing overrides from the provided TestConfiguration (testing.config.php)
        $testingConfig = [];
        if (isset(static::$config['module_listener_options']['config_glob_paths'][0])) {
            $path = static::$config['module_listener_options']['config_glob_paths'][0];
            if (is_file($path)) {
                $testingConfig = include $path;
            }
        }

        // Compose final config array available under 'config' service
        $finalConfig = $baseConfig;
        foreach (['slm_queue', 'laminas-cli', 'dependencies'] as $key) {
            if (isset($testingConfig[$key])) {
                if (! isset($finalConfig[$key])) {
                    $finalConfig[$key] = [];
                }
                $finalConfig[$key] = array_replace_recursive($finalConfig[$key], $testingConfig[$key]);
            }
        }

        // Build the ServiceManager using only the dependencies section
        $dependencies = $finalConfig['dependencies'] ?? [];
        $serviceManager = new ServiceManager($dependencies);

        // Expose the complete configuration as 'config' for factories that need it
        $serviceManager->setService('config', $finalConfig);

        return $serviceManager;
    }
}
