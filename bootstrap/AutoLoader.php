<?php
/**
 * AutoLoader
 *
 * @author Shisha <shisha225@gmail.com>, 20160323
 */

class AutoLoader
{
	/**
	 * @var string
	 */
	protected $base = '';

	/**
	 * @var array
	 */
	protected $classMap = array();

	/**
	* @var array
	*/
	protected $classAliasStack = array();

	/**
	 * 自定義函式
	 *
	 * @var \Closure
	 */
	protected $userFunction = null;

	/**
	 * 設置基本路徑
	 *
	 * @param string
	 * @return void
	 */
	public function __construct($base = '')
	{
		$this->base = $base;
	}

	/**
	 * 註冊 autoload 函式
	 *
	 * @param string
	 * @param mixed
	 * @return \AutoLoader
	 */
	public static function register($base = '', $callable = null)
	{
		$loader = new static($base);

		if ($callable != null) {
			$loader->setUserFunction($callable);
		}

		spl_autoload_register(array($loader, 'loadClass'), true, true);

		return $loader;
	}

	/**
	 * 反註冊 autoload 函式
	 *
	 * @param \AutoLoader
	 * @return void
	 */
	public static function unregister(AutoLoader $loader)
	{
		spl_autoload_unregister(array($loader, 'loadClass'));
	}

	/**
	 * 增加 class map
	 *
	 * @param array
	 * @return void
	 */
	public function addClassMap(array $classMap)
	{
		$this->classMap = array_merge($this->classMap, $classMap);
	}

	/**
	 * 設置使用者自訂函式
	 *
	 * @param mixed
	 * @return void
	 * @throws \RuntimeException
	 */
	public function setUserFunction($callable)
	{
		if ( ! is_callable($callable)) {
			throw new \RuntimeException("Arg1 must be callable.");
		}

		$this->userFunction = $callable;
	}

	/**
	 * 加載類別並設置別名
	 *
	 * @param string
	 * @return boolean
	 */
	public function loadClass($class)
	{
		if ($path = $this->resolveClassFile($class)) {
			requireFile($path);

			$this->setupClassAliases();

			return true;
		}
	}

	/**
	 * 設置類別別名
	 *
	 * @return void
	 */
	protected function setupClassAliases()
	{
		for ($i = 0; $i < count($this->classAliasStack); $i++) {
			list($original, $alias) = array_shift($this->classAliasStack);

			if ( ! class_exists($original, false)) {
				array_push($this->classAliasStack, array($original, $alias));
				continue;
			}

			class_alias($original, $alias, false);
		}
	}

	/**
	 * 增加類別別名
	 *
	 * @param string
	 * @param string
	 * @return void
	 */
	public function addClassAlias($original, $alias)
	{
		array_push($this->classAliasStack, func_get_args());
	}

	/**
	 * 解析路徑
	 *
	 * @param string
	 * @return mixed
	 */
	private function resolveClassFile($class)
	{
		// 解析 class map
		if (isset($this->classMap[$class])) {
			return $this->parsePath($this->classMap[$class]);
		}

		// 解析路徑
		$path = array(
			$this->base,
			$this->getClassNamespace($class),
			$this->getClassBasename($class)
		);

		$path = $this->parsePath($path);
		if ($this->isFileExists($path)) {
			return $path;
		}

		// 呼叫自定義函式
		if ($this->userFunction !== null) {
			if ($resolved = call_user_func($this->userFunction, $class, $this)) {
				return $this->parsePath($resolved);
			}
		}
	}

	/**
	 * 處理檔案路徑
	 *
	 * @param string|array
	 * @return string
	 */
	public function parsePath($path)
	{
		if (is_array($path)) {
			$path = array_diff($path, array(''));
			$path = join(DIRECTORY_SEPARATOR, $path);
		}

		$path = strtr($path, '\\', DIRECTORY_SEPARATOR);

		if (strpos($path, DIRECTORY_SEPARATOR) !== 0) {
			$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
		}

		if (($pos = strrpos($path, '.php')) != (strlen($path) - 4)) {
			$path = $path . '.php';
		}

		return $path;
	}

	/**
	 * 判斷檔案是否存在
	 *
	 * @param string
	 * @return boolean
	 */
	public function isFileExists($file)
	{
		return (file_exists($file) AND is_readable($file));
	}

	/**
	 * 取得命名空間
	 *
	 * @param string
	 * @return string
	 */
	public function getClassNamespace($className)
	{
		return substr($className, 0, strrpos($className, '\\'));
	}

	/**
	 * 取得類別名稱(去掉命名空間)
	 *
	 * @param string
	 * @return string
	 */
	public function getClassBasename($className)
	{
		return substr($className, strrpos($className, '\\') + 1);
	}
}

/**
 * 引入檔案
 */
function requireFile($file)
{
	require $file;
}
