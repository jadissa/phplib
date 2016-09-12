<?php
namespace JadissaPHPLib;

class strings
{
    /**
     * json_encode results with error checking
     * @param array $results
     * @throws \Exception
     * @return mixed
     */
    public static function json_encode($results)
    {
        $results = json_encode($results);
        if ($results === FALSE)
        {
            throw new \Exception(json_last_error_msg(json_last_error()));
        }
        else
        {
            return $results;
        }
    }

    /**
     * Remove accents from string
     * @param string
     * @return string
     */
    public static function stripAccents($stripAccents)
    {
        return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûü','aaaaaceeeeiiiinooooouuuu');
    }

    /**
     * Convert data to UTF8
     * @param array|string $d
     * @return array|string
     */
    function utf8ize($d)
    {
        if (is_array($d))
        {
            foreach ($d as $k => $v)
            {
                $d[$k] = utf8ize($v);
            }
        }
        else if (is_string ($d))
        {
            return utf8_encode($d);
        }
        return $d;
    }
}

class lib extends strings
{
    /**
     * Current file
     * @var string $file
     */
    private static $file;

    /**
     * Current directory
     * @var string $directory
     */
    private static $directory;

    /**
     * Current directory listing
     * @var array $listing
     */
    private static $listing;

    /**
     * Current file contents
     * @var string $contents
     */
    private static $contents;

    /**
     * Lists contents of directory
     * @param string $directory
     * @throws \Exception
     * @return array
     */
    public static function ls($directory)
    {
        self::$directory = $directory;
        self::$listing = scandir(self::$directory, SCANDIR_SORT_ASCENDING);
        if (!self::$listing)
        {
            throw new \Exception('scandir fails at life');
        }
        return self::$listing;
    }

    /*
    public static function ls($directory)
    {
        self::$directory = $directory;
        self::$listing = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::$directory),
            \RecursiveIteratorIterator::LEAVES_ONLY,
            \RecursiveIteratorIterator::CATCH_GET_CHILD);
        var_dump(self::$listing);
        return self::$listing;
    }
    */

    /**
     * Reads file
     * @param string $file
     * @throws \Exception
     * @return string
     */
    public static function read($file)
    {
        self::$file = $file;
        $fh = fopen(self::$file, 'r');
        if (!is_resource($fh))
        {
            throw new \Exception('The file could not be opened');
        }
        self::$contents = fread($fh, filesize(self::$file));
        return self::$contents;
    }

    /**
     * Writes contents to file
     * @param string $file
     * @param string $contents
     * @throws \Exception
     * @return bool
     */
    public static function write($file, $contents)
    {
        self::$file = $file;
        self::$contents = $contents;
        $fh = fopen(self::$file, 'w');
        if (!is_resource($fh))
        {
            throw new \Exception('The file could not be opened');
        }
        return fwrite($fh, self::$contents, filesize(self::$file));
    }
}

class Fw extends lib
{
    /**
     * The current settings object
     * @var \SimpleXMLElement
     */
    private static $settings;

    /**
     * Authenticates user against Active Directory
     * @param string $username
     * @param string $password
     * @throws \Exception
     * @return bool
     */
    public function ldapAuth($username, $password)
    {
        if (isset($username) AND isset($password))
        {
            if (empty(self::$settings))
            {
                new self;
            }

            $_SESSION['auth'] = FALSE;
            $adServer = (string) self::$settings->ldap->server;

            try
            {
                $ldap = ldap_connect($adServer);
                $ldaprdn = self::$settings->ldap->dn . "\\{$username}";
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
                $bind = @ldap_bind($ldap, $ldaprdn, $password);
                if (!$bind)
                {
                    return FALSE;
                }
                $filter = "(sAMAccountName={$username})";
                $result = ldap_search($ldap, (string) self::$settings->ldap->searchbase, $filter);
                if ((!$result) OR !is_resource($result))
                {
                    return FALSE;
                }
                ldap_sort($ldap, $result, 'sn');
                $info = ldap_get_entries($ldap, $result);
                if ((!$info) OR empty($info))
                {
                    return FALSE;
                }
                @ldap_close($ldap);
                $_SESSION['auth'] = TRUE;
                return TRUE;
            }
            catch(Exception $e)
            {
                return FALSE;
            }
        }
        return FALSE;
    }

    /**
     * Checks for authenticated session
     * @return bool
     */
    public static function hasLdapAuth()
    {
        if (!isset($_SESSION['auth']) OR $_SESSION['auth'] === FALSE)
        {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Initialize settings
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty(self::$settings))
        {
            try
            {
                self::$settings = simplexml_load_string(file_get_contents((string) 'settings.xml'));
            }
            catch (\Exception $e)
            {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * Gets settings object from niksettings.xml
     * @param string $setting
     * @throws \Exception
     * @return object
     */
    public static function getSettings($setting = NULL)
    {
        new self;
        return (is_null($setting) === FALSE ? self::$settings->$setting : self::$settings);
    }
}
?>