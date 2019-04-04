import manifest from '@neos-project/neos-ui-extensibility';

import TaxonomyEditor from './TaxonomyEditor';
import TaxonomyFrame from './TaxonomyFrame';

manifest('Sitegeist.Taxonomy:TaxonomyEditor', {}, globalRegistry => {
    const editorsRegistry = globalRegistry.get('inspector').get('editors');
	const secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');

    editorsRegistry.set('Sitegeist.Taxonomy:TaxonomyEditor', {
        component: TaxonomyEditor
    });

	secondaryEditorsRegistry.set('Sitegeist.Taxonomy:TaxonomyFrame', {
		component: TaxonomyFrame
	});
});
