<?php

namespace SunlightTools\LangTool;

/**
 * Dictionary processor
 */
class Processor
{
    /**
     * Find renames in two dictionaries
     *
     * @param Dictionary $new
     * @param Dictionary $old
     * @return string[] new key => old key
     */
    public function findRenames(Dictionary $new, Dictionary $old)
    {
        $renames = array();

        foreach ($new->entries as $newKey => $newVal) {
            if (!isset($old->entries[$newKey])) {
                $previousKey = null;

                foreach ($old->entries as $oldKey => $oldVal) {
                    if ($oldVal === $newVal) {
                        if (null === $previousKey) {
                            $previousKey = $oldKey;
                        } else {
                            $previousKey = null;
                            break;
                        }
                    }
                }

                if (null !== $previousKey) {
                    $renames[$newKey] = $previousKey;
                }
            }
        }

        return $renames;
    }

    /**
     * Find changes in two dictionaries
     *
     * @param Dictionary $new
     * @param Dictionary $old
     * @param string[]   $renames
     * @return string[] key => true for each changed entry
     */
    public function findChanges(Dictionary $new, Dictionary $old, array $renames = array())
    {
        $changes = array();

        foreach ($new->entries as $key => $val) {
            if (isset($old->entries[$key])) {
                $oldKey = $key;
            } elseif (isset($renames[$key], $old->entries[$renames[$key]])) {
                $oldKey = $renames[$key];
            } else {
                $oldKey = null;
            }

            if (null !== $oldKey && $old->entries[$oldKey] !== $val) {
                $changes[$key] = true;
            }
        }

        return $changes;
    }

    /**
     * Fix entry case in a dictionary
     *
     * Meta data is used in comparison.
     *
     * @param Dictionary $target
     * @return int number of fixed entries
     */
    public function fixCase(Dictionary $target)
    {
        $fixCounter = 0;

        foreach ($target->entries as $key => &$val) {
            if (
                isset($target->meta[$key])
                && '' !== $val
                && '' !== $target->meta[$key]
            ) {
                $valFirst = mb_substr($val, 0, 1);
                $valFirstIsUpper = $valFirst === mb_strtoupper($valFirst);
                $metaFirst = mb_substr($target->meta[$key], 0, 1);
                $metaFirstIsUpper = $metaFirst === mb_strtoupper($metaFirst);

                if ($valFirstIsUpper xor $metaFirstIsUpper) {
                    if ($metaFirstIsUpper) {
                        $val = mb_strtoupper($valFirst) . mb_substr($val, 1);
                    } else {
                        $val = mb_strtolower($valFirst) . mb_substr($val, 1);
                    }
                    ++$fixCounter;
                }
            }
        }

        return $fixCounter;
    }
}
