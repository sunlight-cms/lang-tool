<?php

namespace SunlightTools\LangTool;

/**
 * Dictionary directory iterator
 */
class DictionaryDirectoryIterator extends \FilterIterator
{
    /** @var string[] */
    private $filter = array();

    /**
     * Create the iterator
     *
     * @param string   $directory
     * @param string[] $filter
     * @return static
     */
    public static function create($directory, array $filter = array())
    {
        $iterator = new static(new \FilesystemIterator($directory));

        $iterator->filter = $filter;

        return $iterator;
    }

    /**
     * @param \FilesystemIterator $iterator
     * @param string[]            $filter
     */
    public function __construct(\FilesystemIterator $iterator, array $filter = array())
    {
        $this->filter = $filter;

        parent::__construct($iterator);
    }

    public function accept()
    {
        return
            Dictionary::isValidFile($this->current())
            && (
                empty($this->filter)
                || in_array(
                    Dictionary::getNameForFile($this->current()),
                    $this->filter,
                    true
                )
            )
        ;
    }
}
