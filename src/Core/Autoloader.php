<?php

namespace MageArab\MegaFramework\Core;

use SplFileInfo;
use MageArab\MegaFramework\Utils;
use MageArab\MegaFramework\Traits;
use MageArab\MegaFramework\Exceptions;

if (!defined('ABSPATH') || !defined('WPINC')) {
    die;
}

/**
 * Autoloader is responsible for loading classes and interfaces.
 *
 * <code>
 * $loader = new MageArab\MegaFramework\Core\Autoloader;
 * $loader->addDirectory('app');
 * $loader->excludeDirectory('app/exclude');
 * $loader->setTempDirectory('temp');
 * $loader->register();
 * </code>
 */
class Autoloader
{
    use Traits\SmartObject;
    use Traits\Singleton;

    const RETRY_LIMIT = 3;
    protected static $_instance;
    /** @var array  comma separated wildcards */
    public $ignoreDirs = ['.*', '*.old', '*.bak', '*.tmp', 'temp'];
    /** @var array  comma separated wildcards */
    public $acceptFiles = ['*.php'];
    /** @var bool */
    private $autoRebuild = true;
    /** @var bool */
    private $reportParseErrors = true;
    /** @var array */
    private $scanPaths = [];
    /** @var array */
    private $excludeDirs = [];
    /** @var array of class => [file, time] */
    private $classes = [];
    /** @var bool */
    private $refreshed = false;
    /** @var array of missing classes */
    private $missing = [];
    /** @var string|null */
    private $tempDirectory;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if (!extension_loaded('tokenizer')) {
            throw new Exceptions\NotSupportedException('PHP extension Tokenizer is not loaded.');
        }
    }

    /**
     * Register autoloader.
     * @param  bool $prepend
     * @return static
     */
    public static function register($prepend = false)
    {
        Autoloader::instance()->loadCache();
        try {
            spl_autoload_register([Autoloader::instance(), 'tryLoad'], true, $prepend);
        } catch (\Exception $e) {
        }
        return Autoloader::instance();
    }

    /**
     * Handles autoloading of classes, interfaces or traits.
     * @param  string $type
     * @return void
     * @throws \ReflectionException
     */
    public function tryLoad($type)
    {
        $type = ltrim($type, '\\'); // PHP namespace bug #49143
        $info = isset($this->classes[$type]) ? $this->classes[$type] : null;
        if ($this->autoRebuild) {
            if (!$info || !is_file($info['file'])) {
                $missing = &$this->missing[$type];
                $missing++;
                if (!$this->refreshed && $missing <= self::RETRY_LIMIT) {
                    $this->refresh();
                    $this->saveCache();
                } elseif ($info) {
                    unset($this->classes[$type]);
                    $this->saveCache();
                }
            } elseif (!$this->refreshed && filemtime($info['file']) !== $info['time']) {
                $this->updateFile($info['file']);
                if (empty($this->classes[$type])) {
                    $this->missing[$type] = 0;
                }
                $this->saveCache();
            }
            $info = isset($this->classes[$type]) ? $this->classes[$type] : null;
        }
        if ($info) {
            call_user_func(function ($file) { require $file; }, $info['file']);
        }
    }
    /**
     * Add path or paths to list.
     * @param  string|string[]  $path  absolute path
     * @return static
     */
    public static function addDirectory($path)
    {
        Autoloader::instance()->scanPaths = array_merge(Autoloader::instance()->scanPaths, (array) $path);
        return Autoloader::instance();
    }

    /**
     * @param bool $on
     * @return static
     */
    public function reportParseErrors($on = true)
    {
        $this->reportParseErrors = (bool) $on;
        return $this;
    }
    /**
     * Excludes path or paths from list.
     * @param  string|string[]  $path  absolute path
     * @return static
     */
    public function excludeDirectory($path)
    {
        $this->excludeDirs = array_merge($this->excludeDirs, (array) $path);
        return $this;
    }
    /**
     * @return array of class => filename
     */
    public function getIndexedClasses()
    {
        $res = [];
        foreach ($this->classes as $class => $info) {
            $res[$class] = $info['file'];
        }
        return $res;
    }
    /**
     * Rebuilds class list cache.
     * @return void
     */
    public function rebuild()
    {
        $this->refresh();
        if ($this->tempDirectory) {
            $this->saveCache();
        }
    }

    /**
     * Refreshes class list.
     * @return void
     */
    private function refresh()
    {
        $this->refreshed = true; // prevents calling refresh() or updateFile() in tryLoad()
        $files = [];
        foreach ($this->classes as $class => $info) {
            $files[$info['file']]['time'] = $info['time'];
            $files[$info['file']]['classes'][] = $class;
        }
        $this->classes = [];
        foreach ($this->scanPaths as $path) {
            foreach (is_file($path) ? [new SplFileInfo($path)] : $this->createFileIterator($path) as $file) {
                $file = $file->getPathname();
                if (isset($files[$file]) && $files[$file]['time'] == filemtime($file)) {
                    $classes = $files[$file]['classes'];
                } else {
                    try {
                        $classes = $this->scanPhp($file);
                    } catch (\ReflectionException $e) {
                    }
                }
                $files[$file] = ['classes' => [], 'time' => filemtime($file)];
                foreach ($classes as $class) {
                    $info = &$this->classes[$class];
                    if (isset($info['file'])) {
                        throw new Exceptions\InvalidStateException("Ambiguous class $class resolution; defined in {$info['file']} and in $file.");
                    }
                    $info = ['file' => $file, 'time' => filemtime($file)];
                    unset($this->missing[$class]);
                }
            }
        }
    }

    /**
     * Creates an iterator scanning directory for PHP files, subdirectories and '*.txt' files.
     * @param $dir
     * @return Utils\Finder
     */
    private function createFileIterator($dir)
    {
        if (!is_dir($dir)) {
            throw new Exceptions\IOException("File or directory '$dir' not found.");
        }
        $ignoreDirs = is_array($this->ignoreDirs) ? $this->ignoreDirs : preg_split('#[,\s]+#', $this->ignoreDirs);
        $disallow = [];
        foreach (array_merge($ignoreDirs, $this->excludeDirs) as $item) {
            if ($item = realpath($item)) {
                $disallow[str_replace('\\', '/', $item)] = true;
            }
        }
        $iterator = Utils\Finder::findFiles(is_array($this->acceptFiles) ? $this->acceptFiles : preg_split('#[,\s]+#', $this->acceptFiles))
            ->filter(function (SplFileInfo $file) use (&$disallow) {
                return !isset($disallow[str_replace('\\', '/', $file->getRealPath())]);
            })
            ->from($dir)
            ->exclude($ignoreDirs)
            ->filter($filter = function (SplFileInfo $dir) use (&$disallow) {
                $path = str_replace('\\', '/', $dir->getRealPath());
                if (is_file("$path/netterobots.txt")) {
                    foreach (file("$path/netterobots.txt") as $s) {
                        if (preg_match('#^(?:disallow\\s*:)?\\s*(\\S+)#i', $s, $matches)) {
                            $disallow[$path . rtrim('/' . ltrim($matches[1], '/'), '/')] = true;
                        }
                    }
                }
                return !isset($disallow[$path]);
            });
        $filter(new SplFileInfo($dir));
        return $iterator;
    }

    /**
     * @param $file
     * @return void
     * @throws \ReflectionException
     */
    private function updateFile($file)
    {
        foreach ($this->classes as $class => $info) {
            if (isset($info['file']) && $info['file'] === $file) {
                unset($this->classes[$class]);
            }
        }
        $classes = is_file($file) ? $this->scanPhp($file) : [];
        foreach ($classes as $class) {
            $info = &$this->classes[$class];
            if (isset($info['file']) && @filemtime($info['file']) !== $info['time']) { // @ file may not exists
                $this->updateFile($info['file']);
                $info = &$this->classes[$class];
            }
            if (isset($info['file'])) {
                throw new Exceptions\InvalidStateException("Ambiguous class $class resolution; defined in {$info['file']} and in $file.");
            }
            $info = ['file' => $file, 'time' => filemtime($file)];
        }
    }

    /**
     * Searches classes, interfaces and traits in PHP file.
     * @param  string $file
     * @return string[]
     * @throws \ReflectionException
     */
    private function scanPhp($file)
    {
        $code = file_get_contents($file);
        $expected = false;
        $namespace = '';
        $level = $minLevel = 0;
        $classes = [];
        if (preg_match('#//nette' . 'loader=(\S*)#', $code, $matches)) {
            foreach (explode(',', $matches[1]) as $name) {
                $classes[] = $name;
            }
            return $classes;
        }
        try {
            $tokens = PHP_VERSION_ID >= 70000
                ? token_get_all($code, TOKEN_PARSE)
                : @token_get_all($code); // @ can be corrupted or can use newer syntax
        } catch (\ParseError $e) {
            if ($this->reportParseErrors) {
                $rp = new \ReflectionProperty($e, 'file');
                $rp->setAccessible(true);
                $rp->setValue($e, $file);
                throw $e;
            }
            $tokens = [];
        }
        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                    case T_WHITESPACE:
                        continue 2;
                    case T_NS_SEPARATOR:
                    case T_STRING:
                        if ($expected) {
                            $name .= $token[1];
                        }
                        continue 2;
                    case T_NAMESPACE:
                    case T_CLASS:
                    case T_INTERFACE:
                    case T_TRAIT:
                        $expected = $token[0];
                        $name = '';
                        continue 2;
                    case T_CURLY_OPEN:
                    case T_DOLLAR_OPEN_CURLY_BRACES:
                        $level++;
                }
            }
            if ($expected) {
                switch ($expected) {
                    case T_CLASS:
                    case T_INTERFACE:
                    case T_TRAIT:
                        if ($name && $level === $minLevel) {
                            $classes[] = $namespace . $name;
                        }
                        break;
                    case T_NAMESPACE:
                        $namespace = $name ? $name . '\\' : '';
                        $minLevel = $token === '{' ? 1 : 0;
                }
                $expected = null;
            }
            if ($token === '{') {
                $level++;
            } elseif ($token === '}') {
                $level--;
            }
        }
        return $classes;
    }
    /********************* caching ****************d*g**/
    /**
     * Sets auto-refresh mode.
     * @param bool $on
     * @return static
     */
    public function setAutoRefresh($on = true)
    {
        $this->autoRebuild = (bool) $on;
        return $this;
    }

    /**
     * Sets path to temporary directory.
     * @param $dir
     * @return static
     */
    public static function setTempDirectory($dir)
    {
        Utils\FileSystem::createDir($dir);
        Autoloader::instance()->tempDirectory = $dir;
        return Autoloader::instance();
    }
    /**
     * Loads class list from cache.
     * @return void
     */
    private function loadCache()
    {
        $file = $this->getCacheFile();
        list($this->classes, $this->missing) = @include $file; // @ file may not exist
        if (is_array($this->classes)) {
            return;
        }
        $handle = fopen("$file.lock", 'c+');
        if (!$handle || !flock($handle, LOCK_EX)) {
            throw new \RuntimeException("Unable to create or acquire exclusive lock on file '$file.lock'.");
        }
        list($this->classes, $this->missing) = @include $file; // @ file may not exist
        if (!is_array($this->classes)) {
            $this->classes = [];
            $this->refresh();
            $this->saveCache();
        }
        flock($handle, LOCK_UN);
        fclose($handle);
        @unlink("$file.lock"); // @ file may become locked on Windows
    }
    /**
     * Writes class list to cache.
     * @return void
     */
    private function saveCache()
    {
        $file = $this->getCacheFile();
        $tempFile = $file . uniqid('', true) . '.tmp';
        $code = "<?php\nreturn " . var_export([$this->classes, $this->missing], true) . ";\n";
        if (file_put_contents($tempFile, $code) !== strlen($code) || !rename($tempFile, $file)) {
            @unlink($tempFile); // @ - file may not exist
            throw new \RuntimeException("Unable to create '$file'.");
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true); // @ can be restricted
        }
    }
    /**
     * @return string
     */
    private function getCacheFile()
    {
        if (!$this->tempDirectory) {
            throw new \LogicException('Set path to temporary directory using setTempDirectory().');
        }
        return $this->tempDirectory . '/' . md5(serialize($this->getCacheKey())) . '.php';
    }
    /**
     * @return array
     */
    protected function getCacheKey()
    {
        return [$this->ignoreDirs, $this->acceptFiles, $this->scanPaths, $this->excludeDirs];
    }
}