<?php

namespace SunlightTools\LangTool;

/**
 * Dictionary
 */
class Dictionary implements \Countable
{
    /** @var string[] */
    public $entries = array();
    /** @var string[] */
    public $meta = array();
    
    /**
     * @param string[] $entries
     * @param string[] $meta
     */
    public function __construct(array $entries = array(), array $meta = array())
    {
        $this->entries = $entries;
        $this->meta = $meta;
    }

    /**
     * See if a file is valid dictionary file
     *
     * @param \SplFileInfo $file
     * @return bool
     */
    public static function isValidFile(\SplFileInfo $file)
    {
        return $file->isFile() && strcasecmp('php', $file->getExtension()) === 0;
    }

    /**
     * Get dictionary name for the given file
     *
     * @param \SplFileInfo $file
     * @return string
     */
    public static function getNameForFile(\SplFileInfo $file)
    {
        return $file->getBasename(".{$file->getExtension()}");
    }

    /**
     * Load dictionary from a file
     *
     * @param \SplFileInfo $file
     * @return static
     */
    public static function fromFile(\SplFileInfo $file)
    {
        return new static(
            static::parseFile($file->getPathname()),
            static::parseMeta($file->getPathname())
        );
    }

    /**
     * Load dictionary from multiple files
     *
     * @param \SplFileInfo[] $files
     * @return static
     */
    public static function fromFiles(array $files)
    {
        $dict = array();
        $meta = array();

        foreach ($files as $file) {
            $dict += static::parseFile($file->getPathname());
            $meta += static::parseMeta($file->getPathname());
        }
        
        return new static($dict, $meta);
    }

    /**
     * Load all dictionaries from a directory
     *
     * @param string   $dir
     * @param string[] $filter only load dictionaries with these names
     * @return static[] name-indexed array
     */
    public static function fromDirEach($dir, array $filter = array())
    {
        $dicts = array();

        foreach (DictionaryDirectoryIterator::create($dir, $filter) as $item) {
            $dicts[static::getNameForFile($item)] = static::fromFile($item);
        }

        return $dicts;
    }

    /**
     * Load all dictionaries from a directory as a single dictionary
     *
     * @param string   $dir
     * @param string[] $filter only load dictionaries with these names
     * @return static
     */
    public static function fromDirMerged($dir, array $filter = array())
    {
        return static::fromFiles(iterator_to_array(DictionaryDirectoryIterator::create($dir, $filter)));
    }

    /**
     * Parse a dictionary file
     *
     * @param string $file
     * @throws \UnexpectedValueException if the file is not valid
     * @return array
     */
    protected static function parseFile($file)
    {
        $dict = require $file;

        // verify return value
        if (!is_array($dict)) {
            throw new \UnexpectedValueException(sprintf(
                'Invalid dictionary data in "%s" - expected array, got %s',
                $file,
                gettype($dict)
            ));
        }

        // scan the dictionary
        foreach ($dict as $key => &$val) {
            if (is_array($val)) {
                // silently ignore sub-arrays (compatibility with old dicts)
                unset($dict[$key]);
            } elseif (is_string($val)) {
                $val = trim($val);
            } else {
                throw new \UnexpectedValueException(sprintf(
                    'Only string values are allowed - found %s value at index "%s" in "%s"',
                    gettype($val),
                    $key,
                    $file
                ));
            }
        }

        return $dict;
    }

    /**
     * Parse metadata inside a dictionary file
     *
     * @param string $file
     * @return array
     */
    protected static function parseMeta($file)
    {
        $meta = array();

        $pattern = <<<'REGEX'
/\/\* (.*?) \*\/\s*("(?:[^"]|\\.)*"|'(?:[^']|\\.)*')\s*=>\s*(?:"(?:[^"]|\\.)*"|'(?:[^']|\\.)*')/ms
REGEX;

        $matches = array();
        preg_match_all($pattern, file_get_contents($file), $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $meta[substr($match[2], 1, -1)] = $match[1];
        }

        return $meta;
    }

    /**
     * Count dictionary entries
     *
     * @return int
     */
    public function count()
    {
        return sizeof($this->entries);
    }

    /**
     * Compile the dictionary into a file
     *
     * @param string $target
     */
    public function compile($target)
    {
        file_put_contents(
            $target,
            "<?php\n\nreturn " . var_export($this->entries, true) . ";\n"
        );
    }

    /**
     * Compile the dictionary into a file with meta data
     *
     * @param string   $target
     * @param string[] $meta      key => value meta data
     * @param bool     $makeEmpty make all values empty 1/0
     * @throws \RuntimeException if the meta data is missing a key
     */
    public function compileWithMeta($target, array $meta, $makeEmpty = false)
    {
        $code = '';

        foreach ($this->entries as $key => $val) {
            if (!isset($meta[$key])) {
                throw new \RuntimeException(sprintf('The index "%s" is missing in the meta data', $key));
            }

            $code .=
                "    /* {$meta[$key]} */\n    '{$key}' => "
                . ($makeEmpty ? "''" : var_export($val, true))
                . ",\n"
            ;
        }

        file_put_contents(
            $target,
            "<?php\n\nreturn array(\n" . $code . ");\n"
        );
    }
}
