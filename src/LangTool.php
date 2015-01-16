<?php

namespace SunlightTools\LangTool;

/**
 * Language tool
 */
class LangTool
{
    /** @var string */
    private $root;

    /**
     * @param string $root
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return string
     */
    public function getCurrentTemplateDir()
    {
        return $this->root . '/template-current';
    }

    /**
     * @return string
     */
    public function getOldTemplateDir()
    {
        return $this->root . '/template-old';
    }

    /**
     * @return string
     */
    public function getInputDir()
    {
        return $this->root . '/input';
    }

    /**
     * @return string
     */
    public function getOutputDir()
    {
        return $this->root . '/output';
    }
}
