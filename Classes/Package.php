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
    /**
     * @param Bootstrap $bootstrap
     */
    public function boot(Bootstrap $bootstrap)
    {
        if (PHP_SAPI === 'cli') {
            // no automagic on the cli
        } else {
//            $dispatcher = $bootstrap->getSignalSlotDispatcher();
//            $dispatcher->connect(
//                Node::class,
//                'nodeAdded',
//                ContentRepositoryHooks::class,
//                'nodeAdded'
//            );
//
//            $dispatcher = $bootstrap->getSignalSlotDispatcher();
//            $dispatcher->connect(
//                Node::class,
//                'nodeRemoved',
//                ContentRepositoryHooks::class,
//                'nodeRemoved'
//            );
        }
    }
}
