<?php
/**
 *
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace Http;

class Uri
{
	/**
	 * @var array
	 */
	protected $server;

	/**
	 * @var string
	 */
	protected $httpHost;

	/**
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * @var string
	 */
	protected $pathInfo;

	/**
	 * @var string
	 */
	protected $queryString;

	/**
	 * 設置 server 參數
	 *
	 * @param array $server
	 */
	public function __construct(array $server)
	{
		$this->server = $server;
	}

	/**
	 * @return string
	 */
	public function getScheme()
	{
		$protocol = strtolower($this->server['SERVER_PROTOCOL']);
		$protocol = substr($protocol, 0, strpos($protocol, '/'));

		return $protocol;
	}

	/**
	 * @return string
	 */
	public function getHttpHost()
	{
		if (empty($this->httpHost)) {
			$this->httpHost = $this->prepareHttpHost();
		}

		return $this->httpHost;
	}

	/**
	 * @return string
	 */
	protected function prepareHttpHost()
	{
		$scheme = $this->getScheme();
		$host = $this->getHost();
		$port = $this->getPort();

		if (($scheme == 'http' && $port == 80) || ($scheme == 'https' && $port == 443)) {
            return $host;
        }

		return "{$host}:{$port}";
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->server['SERVER_NAME'];
	}

	/**
	 * @return integer
	 */
	public function getPort()
	{
		return $this->server['SERVER_PORT'];
	}

	/**
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->server['REQUEST_URI'];
	}

	/**
	 * @return string
	 */
	public function getPathInfo()
	{
		if (empty($this->pathInfo)) {
			$this->pathInfo = $this->preparePathInfo();
		}

		return $this->pathInfo;
	}

	/**
	 * @return string
	 */
	protected function preparePathInfo()
	{
		if (isset($this->server['PATH_INFO'])) {
			return $this->server['PATH_INFO'];
		}

		if (strpos($this->server['REQUEST_URI'], $this->server['SCRIPT_NAME']) == 0) {
			return substr($this->server['REQUEST_URI'], strlen($this->server['SCRIPT_NAME']));
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getQueryString()
	{
		if (empty($this->queryString)) {
			$this->queryString = $this->prepareQueryString();
		}

		return $this->queryString;
	}

	/**
	 * @return string
	 */
	protected function prepareQueryString()
	{
		if (isset($this->server['QUERY_STRING'])) {
			return $this->server['QUERY_STRING'];
		}

		$requestUri = $this->getRequestUri();
		if (0 <= ($pos = strrpos($requestUri, '?'))) {
			return substr($requestUri, $pos + 1);
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		if (empty($this->baseUrl)) {
			$this->baseUrl = $this->prepareBaseUrl();
		}

		return $this->baseUrl;
	}

	/**
	 * @return string
	 */
	protected function prepareBaseUrl()
	{
		$filename = basename($this->server['SCRIPT_FILENAME']);

        if ($filename === basename($this->server['SCRIPT_NAME'])) {
        	return $this->server['SCRIPT_NAME'];
        }

		if ($filename === basename($this->server['PHP_SELF'])) {
            return $this->server['PHP_SELF'];
        }
	}

	/**
	 * @return string
	 */
	public function getBasePath()
	{
		if (empty($this->basePath)) {
			$this->basePath = $this->prepareBasePath();
		}

		return $this->basePath;
	}

	/**
	 * @return string
	 */
	protected function prepareBasePath()
	{
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

		$filename = basename($this->server['SCRIPT_FILENAME']);
        if (basename($baseUrl) === $filename) {
			return $this->parsePath(dirname($baseUrl));
        }

		return $this->parsePath($baseUrl);
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function parsePath($path)
	{
		if ('\\' === DIRECTORY_SEPARATOR) {
			$path = str_replace('\\', '/', $path);
		}

		$path = rtrim($path, '/');

		if ($pos = strpos($path, '..')) {
			$parts = explode('/', $path);
			$path = array();
			foreach ($parts as $part) {
				if (($part !== '..') || ( ! array_pop($path))) {
					array_push($path, $part);
				}
			}

			return implode('/', $path);
		}

		return $path;
	}

	/**
	 * 生成或取得當前 url
	 *
	 * @param string
	 * @param string
	 * @return string
	 */
	public function url($uri = null, $after = null)
	{
		if (null === $uri && null === $after) {
			return $this->getUrl();
		} else {
			return $this->makeUrl($uri, $after);
		}
	}

	/**
	 * 獲得當前 url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return "{$this->getScheme()}://{$this->getHttpHost()}{$this->getBaseUrl()}";
	}

	/**
	 * 以當前環境生成 url
	 *
	 * * $this->makeUrl()					 => http://localhost/dir/index.php
	 * * $this->makeUrl('post.php')  		 => http://localhost/dir/post.php
	 * * $this->makeUrl('/post.php') 		 => http://localhost/post.php
	 * * $this->makeUrl('post.php', '/show') => http://localhost/dir/post.php/show
	 * * $this->makeUrl(null, '/show')  	 => http://localhost/dir/index.php/show
	 *
	 * @param string $uri 若為 null, 則為當前 url
	 * @param string
	 * @return string
	 */
	public function makeUrl($uri = null, $after = '')
	{
		$schemeAndHost =  $this->getScheme() . '://' . $this->getHttpHost();
		$baseUrl = $this->getBaseUrl();

		if (null === $uri) {
			return $schemeAndHost . $baseUrl . $after;
		}

		if (0 !== strpos($uri, '/')) {
			$uri = $this->getBasePath() . '/' . $uri;
		}

		return $schemeAndHost . $this->parsePath($uri) . $after;
	}

	/**
	 * 獲得包括 pathInfo 與 queryString 之完整 url
	 *
	 * @return string
	 */
	public function fullUrl()
	{
		$url = $this->url() . $this->getPathInfo();
		$queryString = $this->getQueryString();
		$queryString = ($queryString)? '?' . $queryString : $queryString;

		return $url . $queryString;
	}
}
