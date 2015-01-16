<?php

namespace SunlightTools\LangTool;

/**
 * Dictionary merger
 */
class Merger
{
    /** @var Processor */
    protected $processor;
    /** @var Dictionary */
    protected $input;
    /** @var Dictionary */
    protected $template;
    /** @var Dictionary|null */
    protected $oldTemplate;

    /**
     * @param Processor  $processor
     * @param Dictionary $input
     * @param Dictionary $template
     * @param Dictionary $oldTemplate
     */
    public function __construct(
        Processor $processor,
        Dictionary $input,
        Dictionary $template,
        Dictionary $oldTemplate = null
    ) {
        $this->processor = $processor;
        $this->input = $input;
        $this->template = $template;
        $this->oldTemplate = $oldTemplate;
    }

    /**
     * Build a new dictionary using the input and templates
     *
     * @param int|null &$missingCount
     * @param int|null &$cleanedCount
     * @param int|null &$caseFixCount
     * @return Dictionary
     */
    public function merge(&$missingCount = null, &$cleanedCount = null, &$caseFixCount = null)
    {
        if (null !== $this->oldTemplate) {
            $renames = $this->processor->findRenames($this->template, $this->oldTemplate);
        } else {
            $renames = array();
        }

        // combine
        $dict = $this->combine($renames, $missingCount);

        // freshen
        $this->freshen($dict, $renames, $cleanedCount);
        
        // fix case
        $caseFixCount = $this->processor->fixCase($dict);

        return $dict;
    }

    /**
     * Combine the input with the templates
     *
     * @param string[] $renames
     * @param int|null &$missingCount
     * @return Dictionary
     */
    protected function combine(array $renames, &$missingCount = null)
    {
        $missingCount = 0;

        $dict = array();
        $meta = array();

        foreach (array_keys($this->template->entries) as $key) {
            if (isset($this->input->entries[$key])) {
                $dict[$key] = $this->input->entries[$key];
                if (isset($this->input->meta[$key])) {
                    $meta[$key] = $this->input->meta[$key];
                }
            } elseif (isset($renames[$key], $this->input->entries[$renames[$key]])) {
                $dict[$key] = $this->input->entries[$renames[$key]];
                if (isset($this->input->meta[$renames[$key]])) {
                    $meta[$key] = $this->input->meta[$renames[$key]];
                }
            } else {
                $dict[$key] = '';
                ++$missingCount;
            }
        }

        return new Dictionary($dict, $meta);
    }

    /**
     * Freshen a dictionary
     *
     * The dictionary MUST be compatible with the template.
     *
     * - cleans outdated entries
     * - updates meta data
     *
     * @param Dictionary $target
     * @param string[]   $renames
     * @param int|null   &$cleanedCount
     * @throws \RuntimeException if the target dictionary is not compatible with the template
     */
    protected function freshen(Dictionary $target, array $renames = array(), &$cleanedCount = null)
    {
        $cleanedCount = 0;

        if (null !== $this->oldTemplate) {
            $templateChanges = $this->processor->findChanges($this->template, $this->oldTemplate, $renames);
        } else {
            $templateChanges = array();
        }

        foreach ($this->template->entries as $key => $val) {
            // check entry
            if (!isset($target->entries[$key])) {
                throw new \RuntimeException(sprintf('The target dictionary is missing a key: "%s"', $key));
            }

            // clean outdated entry
            if ('' !== $target->entries[$key]) {
                if (isset($target->meta[$key])) {
                    // use meta data check
                    if ($target->meta[$key] !== $val) {
                        $target->entries[$key] = '';
                        ++$cleanedCount;
                    }
                } elseif (isset($templateChanges[$key])) {
                    // use template diff
                    $target->entries[$key] = '';
                    ++$cleanedCount;
                }
            }

            // update meta
            $target->meta[$key] = $val;
        }
    }
}
