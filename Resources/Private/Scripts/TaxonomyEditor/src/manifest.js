import manifest from '@neos-project/neos-ui-extensibility';

import TaxonomyEditor from './TaxonomyEditor';

manifest('Sitegeist.Taxonomy:TaxonomyEditor', {}, globalRegistry => {
    const editorsRegistry = globalRegistry.get('inspector').get('editors');

    editorsRegistry.set('Sitegeist.Taxonomy:TaxonomyEditor', {
        component: TaxonomyEditor
    });
});
