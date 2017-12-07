<?php
namespace Sitegeist\Taxonomy;

use Neos\Flow\Package\Package as BasePackage;

/**
 * The Flow Package
 */
class Package extends BasePackage
{
    const ROOT_NODE_NAME = 'taxonomy';

    const ROOT_NODE_TYPE = 'Sitegeist.Taxonomy:Root';

    const VOCABULARY_NODE_TYPE = 'Sitegeist.Taxonomy:Vocabulary';

    const TAXONOMY_NODE_TYPE = 'Sitegeist.Taxonomy:Taxonomy';

}
