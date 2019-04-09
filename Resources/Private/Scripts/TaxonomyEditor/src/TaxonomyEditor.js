import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {Button, IconButton} from '@neos-project/react-ui-components';
import {SortableContainer, SortableElement, arrayMove} from 'react-sortable-hoc';
import {neos} from '@neos-project/neos-ui-decorators';
import TaxonomyFrame from './TaxonomyFrame';

const TaxonomyItem = SortableElement(({value, onDelete}) => ( // eslint-disable-line
	<li style={{position: 'relative'}}>
		<div style={{padding: '5px 10px'}}>{value}</div>
		<div style={{position: 'absolute', top: 0, right: 0, bottom: 0, left: 0, cursor: 'move'}}/>
		<div style={{position: 'absolute', top: '5px', right: '5px'}}>
			{/* eslint-disable */}
			<Button onClick={() => onDelete(value)}>X</Button>
			{/* eslint-enable */}
		</div>
	</li>
));

const TaxonomyList = SortableContainer(({items, onDeleteItem}) => ( // eslint-disable-line
	<ul
		style={{
			WebkitTouchCallout: 'none',
				WebkitUserSelect: 'none',
				KhtmlUserSelect: 'none',
				MozUserSelect: 'none',
				msUserSelect: 'none',
				userSelect: 'none'
		}}
	>
		{items.map((value, index) => (
			<TaxonomyItem
			key={`${value}`}
			index={index}
			value={value}
			onDelete={onDeleteItem}
			/>
		))}
	</ul>
));

@neos(globalRegistry => ({
	secondaryEditorsRegistry: globalRegistry.get('inspector').get('secondaryEditors')
}))
export default class TaxonomyEditor extends PureComponent {

    static propTypes = {
        value: PropTypes.string,
        commit: PropTypes.func.isRequired,
    };

	handleCloseSecondaryScreen = () => {
		const {renderSecondaryInspector} = this.props;
		renderSecondaryInspector(undefined, undefined);
	}

	handleOpenSecondaryScreen = (e) => {
		e.preventDefault();
		const {secondaryEditorsRegistry, renderSecondaryInspector} = this.props;
		const {component: TaxonomyFrame} = secondaryEditorsRegistry
			.get('Sitegeist.Taxonomy:TaxonomyFrame');

		renderSecondaryInspector('CANUSA_IMAGE_DATABASE', () => (
				<TaxonomyFrame onSelect={this.handleAddTaxonomy}/>
		));
	}

	handleAddTaxonomy = taxonomyIdentifier => {
		console.log(taxonomyIdentifier)
	}

	handleDelete = taxonomyIdentifier => {
		const {value, commit} = this.props;
		commit(value.filter(item => item !== taxonomyIdentifier));
	}

	handleSort = ({oldIndex, newIndex}) => {
		const {value, commit} = this.props;
		commit(arrayMove(value, oldIndex, newIndex));
	}

    render() {
		const {value} = this.props;

		return (
            <div>
				<TaxonomyList
					items={value}
					onSortEnd={this.handleSort}
					onDeleteItem={this.handleDelete}
				/>
                <IconButton style="lighter" icon="search" title="Reset" onClick={this.handleOpenSecondaryScreen}/>
            </div>
        );
    }
}
