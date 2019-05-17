import React, {Fragment, PureComponent} from 'react';
import {createPortal} from 'react-dom';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';

import {Button} from '@neos-project/react-ui-components';

@neos(globalRegistry => {
	const secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');
	const editorsRegistry = globalRegistry.get('inspector').get('editors');

	const {component: ReferencesEditor} = editorsRegistry.get('Neos.Neos/Inspector/Editors/ReferencesEditor');
	const {component: TaxonomyTreeSelect} = secondaryEditorsRegistry.get('Sitegeist.Taxonomy:TaxonomyTreeSelect');


	return {
		ReferencesEditor,
		TaxonomyTreeSelect
	};
})
export default class TaxonomyEditor extends PureComponent {

    static propTypes = {
		value: PropTypes.string,
		renderSecondaryInspector: PropTypes.func.isRequired,
		commit: PropTypes.func.isRequired,
		options: PropTypes.array,

		ReferencesEditor: PropTypes.func.isRequired,
		TaxonomyTreeSelect: PropTypes.func.isRequired
    };

	state = {
		secondaryInspectorPortal: null,
		openTaxonomyBranchesInSecondaryInspector: []
	};

	componentWillUnmount() {
		this.handleCloseSecondaryScreen();
	}

	handleCloseSecondaryScreen = () => {
		const {renderSecondaryInspector} = this.props;
		renderSecondaryInspector(undefined, undefined);
	}

	handleOpenSecondaryScreen = () => {
		const {TaxonomyTreeSelect, renderSecondaryInspector} = this.props;
		renderSecondaryInspector('TAXONOMY_TREE_SELECT', () => {
			return (<div ref={secondaryInspectorPortal => this.setState({secondaryInspectorPortal})}/>);
		});
	}

	handleToggleTaxonomyInSecondaryInspector = taxonomyIdentifier => {
		const {value, commit} = this.props;

		if (value.includes(taxonomyIdentifier)) {
			commit(value.filter(item => item !== taxonomyIdentifier));
		} else {
			commit([...value, taxonomyIdentifier]);
		}
	}

	handleToggleTaxonomyBranchInSecondaryInspector = taxonomyIdentifier => this.setState(state => {
		if (state.openTaxonomyBranchesInSecondaryInspector.includes(taxonomyIdentifier)) {
			return {
				openTaxonomyBranchesInSecondaryInspector:
					state.openTaxonomyBranchesInSecondaryInspector.filter(item => item !== taxonomyIdentifier)
			};
		} else {
			return {
				openTaxonomyBranchesInSecondaryInspector:
					[...state.openTaxonomyBranchesInSecondaryInspector, taxonomyIdentifier]
			};
		}
	});

	handleSort = ({oldIndex, newIndex}) => {
		const {value, commit} = this.props;
		commit(arrayMove(value, oldIndex, newIndex));
	}

	handleCommit = value => {
		const {commit} = this.props;

		commit(value);
	}

    render() {
		const {ReferencesEditor, TaxonomyTreeSelect, value, options} = this.props;
		const {secondaryInspectorPortal, openTaxonomyBranchesInSecondaryInspector} = this.state;

		return (
            <Fragment>
				<ReferencesEditor
					{...this.props}
					commit={this.handleCommit}
					/>
				<Button onClick={this.handleOpenSecondaryScreen}>
					Blubb
				</Button>
				{secondaryInspectorPortal ? createPortal(
					<TaxonomyTreeSelect
						value={value}
						options={options}
						onToggleTaxonomy={this.handleToggleTaxonomyInSecondaryInspector}
						onToggleTaxonomyBranch={this.handleToggleTaxonomyBranchInSecondaryInspector}
						openBranches={openTaxonomyBranchesInSecondaryInspector}
						/>,
					secondaryInspectorPortal
				) : null}
			</Fragment>
        );
    }
}
