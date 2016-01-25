<?php
namespace NikkenPHPLib;

class strings
{
    /**
     * json_encode results with error checking
     * @param array $results
     * @return mixed
     * @throws Exception
     */
    public static function json_encode($results)
    {
        $results = json_encode($results);
        if ($results === FALSE)
        {
            switch (json_last_error())
            {
                case JSON_ERROR_DEPTH:
                    throw new Exception('json_encode error: Max stack depth exceeded');
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    throw new Exception('json_encode error: Underflow or the modes mismatch');
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    throw new Exception('json_encode error: Unexpected control character found');
                    break;
                case JSON_ERROR_SYNTAX:
                    throw new Exception('json_encode error: Syntax error, malformed JSON');
                    break;
                case JSON_ERROR_UTF8:
                    throw new Exception('json_encode error: Malformed UTF-8 characters, possibly incorrectly encoded');
                    break;
                case JSON_ERROR_RECURSION:
                    throw new Exception('json_encode error: One or more recursive references in the value to be encoded');
                    break;
                case JSON_ERROR_INF_OR_NAN:
                    throw new Exception('One or more NAN or INF values in the value to be encoded');
                    break;
                case JSON_ERROR_UNSUPPORTED_TYPE:
                    throw new Exception('A value of a type that cannot be encoded was given');
                    break;
                default:
                    throw new Exception('json_encode error: Unknown error ' . json_last_error());
                    break;
            }
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

    public static function ls($directory)
    {
        self::$directory = $directory;
        self::$listing = scandir(self::$directory, SCANDIR_SORT_ASCENDING);
        return self::$listing;
    }

    public static function read($file)
    {
        self::$file = $file;
        $fh = fopen(self::$file, 'r');
        if (!is_resource($fh))
        {
            throw new Exception('The file could not be opened');
        }
        self::$contents = fread($fh, filesize(self::$file));
        return self::$contents;
    }

    public static function write($file, $contents)
    {
        self::$file = $file;
        self::$contents = $contents;
        $fh = fopen(self::$file, 'w');
        if (!is_resource($fh))
        {
            throw new Exception('The file could not be opened');
        }
        return fwrite($fh, self::$contents, filesize(self::$file));
    }
}

class Fw extends lib
{
    private static $settings;

    public static function getSettings($setting = NULL)
    {
        new self;
        return (is_null($setting) === FALSE ? self::$settings[$setting] : self::$settings);
    }

    public function __construct()
    {
        if (empty(self::$settings))
        {
            try
            {
                $current_directory = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator("."),
                    \RecursiveIteratorIterator::LEAVES_ONLY,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD);

                foreach ($current_directory as $file)
                {
                    if (preg_match('/settings.xml/', $file))
                    {
                        self::$settings = simplexml_load_string(file_get_contents((string) $file));
                        break;
                    }
                }
            }
            catch (Exception $e)
            {
                throw new Exception($e->getMessage());
            }
        }
    }
}