<?php

declare (strict_types=1);
namespace Convoworks\League\HTMLToMarkdown\Converter;

use Convoworks\League\HTMLToMarkdown\Coerce;
use Convoworks\League\HTMLToMarkdown\Configuration;
use Convoworks\League\HTMLToMarkdown\ConfigurationAwareInterface;
use Convoworks\League\HTMLToMarkdown\ElementInterface;
use Convoworks\League\HTMLToMarkdown\PreConverterInterface;
class TableConverter implements ConverterInterface, PreConverterInterface, ConfigurationAwareInterface
{
    /** @var Configuration */
    protected $config;
    public function setConfig(Configuration $config) : void
    {
        $this->config = $config;
    }
    /** @var array<string, string> */
    private static $alignments = ['left' => ':--', 'right' => '--:', 'center' => ':-:'];
    /** @var array<int, string>|null */
    private $columnAlignments = [];
    /** @var string|null */
    private $caption = null;
    public function preConvert(ElementInterface $element) : void
    {
        $tag = $element->getTagName();
        // Only table cells and caption are allowed to contain content.
        // Remove all text between other table elements.
        if ($tag === 'th' || $tag === 'td' || $tag === 'caption') {
            return;
        }
        foreach ($element->getChildren() as $child) {
            if ($child->isText()) {
                $child->setFinalMarkdown('');
            }
        }
    }
    public function convert(ElementInterface $element) : string
    {
        $value = $element->getValue();
        switch ($element->getTagName()) {
            case 'table':
                $this->columnAlignments = [];
                if ($this->caption) {
                    $side = $this->config->getOption('table_caption_side');
                    if ($side === 'top') {
                        $value = $this->caption . "\n" . $value;
                    } elseif ($side === 'bottom') {
                        $value .= $this->caption;
                    }
                    $this->caption = null;
                }
                return $value . "\n";
            case 'caption':
                $this->caption = \trim($value);
                return '';
            case 'tr':
                $value .= "|\n";
                if ($this->columnAlignments !== null) {
                    $value .= '|' . \implode('|', $this->columnAlignments) . "|\n";
                    $this->columnAlignments = null;
                }
                return $value;
            case 'th':
            case 'td':
                if ($this->columnAlignments !== null) {
                    $align = $element->getAttribute('align');
                    $this->columnAlignments[] = self::$alignments[$align] ?? '---';
                }
                $value = \str_replace("\n", ' ', $value);
                $value = \str_replace('|', Coerce::toString($this->config->getOption('table_pipe_escape') ?? '\\|'), $value);
                return '| ' . \trim($value) . ' ';
            case 'thead':
            case 'tbody':
            case 'tfoot':
            case 'colgroup':
            case 'col':
                return $value;
            default:
                return '';
        }
    }
    /**
     * @return string[]
     */
    public function getSupportedTags() : array
    {
        return ['table', 'tr', 'th', 'td', 'thead', 'tbody', 'tfoot', 'colgroup', 'col', 'caption'];
    }
}
