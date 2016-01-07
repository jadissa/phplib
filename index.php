<?php
class lib
{
    public function lib()
    {
    }
}

class file extends lib
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

class settings extends file
{
    private static $settings;

    public static function get($setting = NULL)
    {
        new settings;
        return (is_null($setting) === FALSE ? self::$settings[$setting] : self::$settings);
    }

    public function __construct()
    {
        if (empty(self::$settings))
        {
            try
            {
                self::$settings = simplexml_load_string(self::read(__DIR__ . '/config.xml'));
            }
            catch (Exception $e)
            {
                throw new Exception($e->getMessage());
            }
        }
    }
}