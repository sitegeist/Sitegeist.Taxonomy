import React, {Fragment, PureComponent} from 'react';
import {createPortal} from 'react-dom';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';

import {Button, Icon} from '@neos-project/react-ui-components';

import styles from './TaxonomyEditor.css';

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
		identifier: PropTypes.string,
		renderSecondaryInspector: PropTypes.func.isRequired,
		commit: PropTypes.func.isRequired,
		options: PropTypes.array,

		ReferencesEditor: PropTypes.func.isRequired,
		TaxonomyTreeSelect: PropTypes.func.isRequired
    };

	state = {
		secondaryInspectorPortal: null,
		openTaxonomyBranchesInSecondaryInspector: null
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
		if (state.openTaxonomyBranchesInSecondaryInspector !== null) {
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
		}
	});

	handleInitializeTaxonomyBranchesInSecondaryInspector = taxonomyIdentifiers => this.setState(state => {
		if (state.openTaxonomyBranchesInSecondaryInspector === null) {
			return {
				openTaxonomyBranchesInSecondaryInspector: taxonomyIdentifiers
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
		const {ReferencesEditor, TaxonomyTreeSelect, value, identifier, options} = this.props;
		const {secondaryInspectorPortal, openTaxonomyBranchesInSecondaryInspector} = this.state;

		return (
				<div className={styles.taxonomyEditor}>
					<ReferencesEditor
						{...this.props}
						options={{
							...options,
							nodeTypes: ['Sitegeist.Taxonomy:Taxonomy']
						}}
						commit={this.handleCommit}
						/>
					<Button
						className={styles.button}
						onClick={this.handleOpenSecondaryScreen}
						isActive={Boolean(secondaryInspectorPortal)}
						>
						<Icon className={styles.icon} icon="sitemap"/>
						Toggle Taxonomy Tree
					</Button>
					{secondaryInspectorPortal ? createPortal(
						<TaxonomyTreeSelect
							value={value}
							identifier={identifier}
							options={options}
							onToggleTaxonomy={this.handleToggleTaxonomyInSecondaryInspector}
							onToggleTaxonomyBranch={this.handleToggleTaxonomyBranchInSecondaryInspector}
							onInitializeTaxonomyBranches={this.handleInitializeTaxonomyBranchesInSecondaryInspector}
							openBranches={openTaxonomyBranchesInSecondaryInspector || []}
							/>,
						secondaryInspectorPortal
					) : null}
				</div>
			);
		}
}
