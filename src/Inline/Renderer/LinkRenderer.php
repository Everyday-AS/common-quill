<?php

namespace Everyday\CommonQuill\Inline\Renderer;

use Everyday\QuillDelta\DeltaOp;
use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\Configuration;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;
use League\CommonMark\Util\RegexHelper;

class LinkRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @param AbstractInline           $inline
     * @param ElementRendererInterface $quillRenderer
     *
     * @return string
     */
    public function render(AbstractInline $inline, ElementRendererInterface $quillRenderer)
    {
        if (!($inline instanceof Link)) {
            throw new InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        $target = $inline->data['attributes']['target'] ?? null;

        $link = $inline->getUrl();
        if (!$this->config->get('allow_unsafe_links') && RegexHelper::isLinkPotentiallyUnsafe($link)) {
            $link = 'about:blank';
        }

        /** @var DeltaOp[] $ops */
        $ops = unserialize($quillRenderer->renderInlines($inline->children()));

        DeltaOp::applyAttributes($ops, compact('link', 'target'));

        return serialize($ops);
    }

    /**
     * @param ConfigurationInterface $configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->config = $configuration;
    }
}
