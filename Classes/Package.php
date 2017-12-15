<?php
namespace Sitegeist\Taxonomy;

use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Core\Bootstrap;
use Neos\ContentRepository\Domain\Model\Node;
use Sitegeist\Taxonomy\Hooks\ContentRepositoryHooks;

/**
 * The Flow Package
 */
class Package extends BasePackage
{
    const ROOT_NODE_NAME = 'taxonomy';

    const ROOT_NODE_TYPE = 'Sitegeist.Taxonomy:Root';

    const VOCABULARY_NODE_TYPE = 'Sitegeist.Taxonomy:Vocabulary';

    const TAXONOMY_NODE_TYPE = 'Sitegeist.Taxonomy:Taxonomy';

    /**
     * @param Bootstrap $bootstrap
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(
            Node::class,
            'nodeAdded',
            ContentRepositoryHooks::class,
            'nodeAdded'
        );
    }
}
