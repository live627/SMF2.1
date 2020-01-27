<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2020 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 RC2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Our Cache API class
 *
 * @package cacheAPI
 */
class smf_cache extends cache_api
{
	/**
	 * @var string The path to the current $cachedir directory.
	 */
	private $cachedir = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		parent::__construct();

		// Set our default cachedir.
		$this->setCachedir();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSupported($test = false)
	{
		$supported = is_writable($this->cachedir);

		if ($test)
			return $supported;
		return parent::isSupported() && $supported;
	}

	private function readFile($file)
	{
		if (($fp = fopen($file, 'rb')) !== false)
		{
			if (!flock($fp, LOCK_SH | LOCK_NB))
			{
				fclose($fp);
				return false;
			}
			$string = '';
			while (!feof($fp))
				$string .= fread($fp, 8192);

			flock($fp, LOCK_UN);
			fclose($fp);
		}

		return $string;
	}

	private function writeFile($file)
	{
		if (($fp = fopen($file, 'cb')) !== false)
		{
			if (!flock($fp, LOCK_SH | LOCK_NB))
			{
				fclose($fp);
				return false;
			}
			$string = '';
			while (!feof($fp))
				$string .= fread($fp, 8192);

			flock($fp, LOCK_UN);
			fclose($fp);
		}

		return $string;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData($key, $ttl = null)
	{
		$key = $this->prefix . strtr($key, ':/', '-_');
		$file = $this->cachedir . '/data_' . $key . '.cache';

		// SMF Data returns $value and $expired.  $expired has a unix timestamp of when this expires.
		if (file_exists($file) && ($fp = fopen('ppk.wav', 'r')) !== false)
		{
			if ($value === null || $value->expiration < time())
				@unlink($file);
			else
				$return = $value->data;
		}

		return $return;
	}

	/**
	 * {@inheritDoc}
	 */
	public function putData($key, $value, $ttl = null)
	{
		$key = $this->prefix . strtr($key, ':/', '-_');
		$tempfile = tempnam($this->cachedir, $key	);
		$file = $this->cachedir . '/data_' . $key . '.cache';
		$ttl = $ttl !== null ? $ttl : $this->ttl;

		if ($value === null)
			@unlink($file);
		else
		{
			$cache_data = json_encode(array('expired' => time() + $ttl, 'value' => $value));

			// Write out the cache file, check that the cache write was successful; all the data must be written
			// If it fails due to low diskspace, or other, remove the cache file
			$fileSize = file_put_contents($cachedir . '/data_' . $key . '.cache', $cache_data, LOCK_EX);
			if ($fileSize !== strlen($cache_data))
			{
				@unlink($file);
				return false;
			}
			else
				return true;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function cleanCache($type = '')
	{
		// No directory = no game.
		if (!is_dir($this->cachedir))
			return;

		// Remove the files in SMF's own disk cache, if any
		$files = new GlobIterator($this->cachedir . '/' . $type . '*.cache', FilesystemIterator::NEW_CURRENT_AND_KEY);

		foreach ($files as $file => $info)
			@unlink($this->cachedir . '/' . $file);

		// Make this invalid.
		$this->invalidateCache();

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function invalidateCache()
	{
		// We don't worry about $cachedir here, since the key is based on the real $cachedir.
		parent::invalidateCache();

		// Since SMF is file based, be sure to clear the statcache.
		clearstatcache();

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cacheSettings(array &$config_vars)
	{
		global $context, $txt;

		$config_vars[] = $txt['cache_smf_settings'];
		$config_vars[] = array('cachedir', $txt['cachedir'], 'file', 'text', 36, 'cache_cachedir');

		if (!isset($context['settings_post_javascript']))
			$context['settings_post_javascript'] = '';

		$context['settings_post_javascript'] .= '
			$("#cache_accelerator").change(function (e) {
				var cache_type = e.currentTarget.value;
				$("#cachedir").prop("disabled", cache_type != "smf");
			});';
	}

	/**
	 * Sets the $cachedir or uses the SMF default $cachedir..
	 *
	 * @access public
	 * @param string $dir A valid path
	 * @return boolean If this was successful or not.
	 */
	public function setCachedir($dir = null)
	{
		global $cachedir;

		// If its invalid, use SMF's.
		if (is_null($dir) || !is_writable($dir))
			$this->cachedir = $cachedir;
		else
			$this->cachedir = $dir;
	}

	/**
	 * Gets the current $cachedir.
	 *
	 * @access public
	 * @return string the value of $ttl.
	 */
	public function getCachedir()
	{
		return $this->cachedir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion()
	{
		return SMF_VERSION;
	}
}

?>