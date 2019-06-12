import React, {PureComponent, Fragment} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {$get} from 'plow-js';
import mergeClassNames from 'classnames';

import {neos} from '@neos-project/neos-ui-decorators';

import {fetchWithErrorHandling} from '@neos-project/neos-ui-backend-connector';
import {selectors} from '@neos-project/neos-ui-redux-store';

import {Icon} from '@neos-project/react-ui-components';

import styles from './TaxonomyTreeSelect.css';

@connect((state, {identifier}) => {
	const contextForNodeLinking = selectors.UI.NodeLinking.contextForNodeLinking(state);
	const sourceValue = $get(['properties', identifier], selectors.CR.Nodes.focusedSelector(state));

	return {contextForNodeLinking, sourceValue};
})
@neos(globalRegistry => {
	const nodeTypesRegistry = globalRegistry.get('@neos-project/neos-ui-contentrepository');

	return {nodeTypesRegistry};
})
export default class TaxonomyTreeSelect extends PureComponent {
	static propTypes = {
		nodeTypesRegistry: PropTypes.object.isRequired,
		contextForNodeLinking: PropTypes.object.isRequired,
		options: PropTypes.object.isRequired,
		onToggleTaxonomy: PropTypes.func.isRequired,
		onToggleTaxonomyBranch: PropTypes.func.isRequired,
		onInitializeTaxonomyBranches: PropTypes.func.isRequired,
		openBranches: PropTypes.array,
		value: PropTypes.array,
		sourceValue: PropTypes.array
	};

	state = {
		tree: []
	};

	get searchNodesQuery() {
		const {contextForNodeLinking, options} = this.props;

		return {
			...contextForNodeLinking,
			nodeTypes: options.nodeTypes,
			contextNode: options.startingPoint
		};
	}

	get tree() {
		const {contextForNodeLinking, options} = this.props;
		const [, contextString] = contextForNodeLinking.contextNode.split('@');
		const startingPointWithContext = `${options.startingPoint}@${contextString}`;

		return fetchWithErrorHandling.withCsrfToken(csrfToken => ({
			url: `/taxonomy/secondary-inspector/tree?contextNode=${startingPointWithContext}`,
			method: 'GET',
			credentials: 'include',
			headers: {
				'X-Flow-Csrftoken': csrfToken,
				'Content-Type': 'application/json'
			}
		})).then(res => res.json())
	}

	async componentDidMount() {
		this.setState({tree: await this.tree}, () => {
			const {onInitializeTaxonomyBranches} = this.props;

			onInitializeTaxonomyBranches(this.state.tree.children.map(node => node.identifier));
		});
	}

	renderNodeLabel = node => {
		const {value = [], onToggleTaxonomy, nodeTypesRegistry} = this.props;
		const nodeType = nodeTypesRegistry.getNodeType(node.nodeType);

		if (nodeTypesRegistry.isOfType(node.nodeType, 'Sitegeist.Taxonomy:Taxonomy')) {
			return (
				<Fragment>
					<input
						className={styles.checkbox}
						type="checkbox"
						id={`taxonomy-treeselect-node-label-${node.identifier}`}
						checked={value.includes(node.identifier)}
						onClick={e => e.stopPropagation()}
						onChange={() => onToggleTaxonomy(node.identifier)}
					/>
					<label
						className={styles.label}
						htmlFor={`taxonomy-treeselect-node-label-${node.identifier}`}
						onClick={e => e.stopPropagation()}
						title={node.description}
					>

						<Icon className={styles.icon} icon={$get('ui.icon', nodeType)} />

						<span className={styles.title}>
							{node.title}
						</span>

						<small className={styles.nodePath}>
							{node.path}
						</small>
					</label>
				</Fragment>
			);
		}

		return (
			<div className={styles.label} title={node.description}>
				<span className={styles.title}>
					{node.title}
				</span>

				<small className={styles.nodePath}>
					{node.path}
				</small>
			</div>
		)
	}

	renderTreeRecursively = (tree, depth = 0) => {
		const {onToggleTaxonomyBranch, openBranches, value, sourceValue} = this.props;

		return (
			<ul className={styles.list}>
				{tree.map(node => (
					<li key={node.identifier} className={styles.item}>
						<span
							className={mergeClassNames({
								[styles.isDirty]: sourceValue.includes(node.identifier) ?
									!value.includes(node.identifier) : value.includes(node.identifier)
							})}
							/>
						{node.children.length ? (
							<details
								className={styles.details}
								open={openBranches.includes(node.identifier) ? 'open' : null}
								>
								<summary
									className={styles.summary}
									style={{paddingLeft: (depth * 18) + 'px'}}
									onClick={e => {
										console.log(e.currentTarget, e.target);
										e.preventDefault();
										e.stopPropagation();
										onToggleTaxonomyBranch(node.identifier);
									}}
									>
									{this.renderNodeLabel(node)}
								</summary>
								{node.children && this.renderTreeRecursively(node.children, depth + 1)}
							</details>
						) : (
							<div
								className={styles.summary}
								style={{paddingLeft: (depth * 18) + 'px'}}
								onClick={e => e.stopPropagation()}
								>
								{this.renderNodeLabel(node)}
							</div>
						)}
					</li>
				))}
			</ul>
		);
	}

	render() {
		const {tree} = this.state;

		return (
			<div className={styles.taxonomyTreeSelect}>
				<h2 className={styles.headline}>Taxonomies</h2>
				{tree && tree.children && this.renderTreeRecursively(tree.children)}
			</div>
		);
	}
}
