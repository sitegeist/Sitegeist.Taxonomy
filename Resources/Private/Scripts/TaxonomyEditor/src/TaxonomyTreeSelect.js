import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {$transform} from 'plow-js';

import backend from '@neos-project/neos-ui-backend-connector';
import {selectors} from '@neos-project/neos-ui-redux-store';

@connect($transform({
	contextForNodeLinking: selectors.UI.NodeLinking.contextForNodeLinking
}))
export default class TaxonomyTreeSelect extends PureComponent {
	static propTypes = {
		contextForNodeLinking: PropTypes.object.isRequired,
		options: PropTypes.object.isRequired,
		onToggleTaxonomy: PropTypes.func.isRequired,
		onToggleTaxonomyBranch: PropTypes.func.isRequired,
		openBranches: PropTypes.array,
		value: PropTypes.array
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

	get nodes() {
		const {q} = backend.get();
		const {contextForNodeLinking, options} = this.props;
		const nodeTypes = options.nodeTypes || [
			'Sitegeist.Taxonomy:Taxonomy',
			'Sitegeist.Taxonomy:Vocabulary'
		];
		const filter = nodeTypes.map(nodeType => `[instanceof ${nodeType}]`).join('');

		return q(options.startingPoint).find(filter).get();
	}

	async componentDidMount() {
		const nodes = await this.nodes;

		if (!nodes.length) {
			return;
		}

		const nodeMap = nodes.reduce((nodeMap, node) => {
			nodeMap[node.contextPath] = node;
			return nodeMap;
		}, {});
		const [baseDepth] = nodes.map(node => node.depth).sort();
		const rootNodes = nodes.filter(node => node.depth === baseDepth);
		const buildNodeTreeRecursively = nodes => nodes.map(node => ({
			...node,
			children: buildNodeTreeRecursively(
				node.children.map(child => nodeMap[child.contextPath]).filter(i => i)
			)
		}))

		this.setState({tree: buildNodeTreeRecursively(rootNodes)});
	}

	renderNodeLabel = node => {
		const {value = [], onToggleTaxonomy} = this.props;

		return (
			<label
				htmlFor={`taxonomy-treeselect-node-label-${node.identifier}`}
				onClick={e => e.stopPropagation()}
				>
				<input
					type="checkbox"
					id={`taxonomy-treeselect-node-label-${node.identifier}`}
					checked={value.includes(node.identifier)}
					onChange={() => onToggleTaxonomy(node.identifier)}
					/>
				{node.label}
			</label>
		);
	}

	renderTreeRecursively = tree => {
		const {onToggleTaxonomyBranch, openBranches} = this.props;

		return (
			<ul>
				{tree.map(node => (
					<li key={node.identifier}>
						{node.children.length ? (
							<details
								open={openBranches.includes(node.identifier) ? 'open' : null}
								onClick={e => {
									e.preventDefault();
									e.stopPropagation();
									onToggleTaxonomyBranch(node.identifier);
								}}
								>
								<summary>
									{this.renderNodeLabel(node)}
								</summary>
								{this.renderTreeRecursively(node.children)}
							</details>
						) : this.renderNodeLabel(node)}
					</li>
				))}
			</ul>
		);
	}

	render() {
		const {tree} = this.state;

		return (
			<div>
				{this.renderTreeRecursively(tree)}
			</div>
		);
	}
}
