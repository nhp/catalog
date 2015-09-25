<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductSearchAutosuggestionMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_autosuggestion_meta';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer,
        ContextSource $contextSource
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
        $this->contextSource = $contextSource;
    }

    /**
     * @param mixed $dataObject
     * @return SnippetList
     */
    public function render($dataObject)
    {
        // todo: important! Use the data version embedded in $dataObject, whatever that may be!!
        foreach ($this->contextSource->getAllAvailableContexts() as $context) {
            $this->renderMetaInfoSnippetForContext($dataObject, $context);
        }

        return $this->snippetList;
    }

    /**
     * @param mixed $dataObject
     * @param Context $context
     */
    private function renderMetaInfoSnippetForContext($dataObject, Context $context)
    {
        $this->blockRenderer->render($dataObject, $context);

        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $metaSnippetContent = $this->getMetaSnippetContentJson($rootSnippetCode, $pageSnippetCodes);

        $this->snippetList->add(Snippet::create($metaSnippetKey, $metaSnippetContent));
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductSearchAutosuggestionMetaSnippetContent|string
     */
    private function getMetaSnippetContentJson($rootSnippetCode, array $pageSnippetCodes)
    {
        $metaSnippetContent = ProductSearchAutosuggestionMetaSnippetContent::create(
            $rootSnippetCode,
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
