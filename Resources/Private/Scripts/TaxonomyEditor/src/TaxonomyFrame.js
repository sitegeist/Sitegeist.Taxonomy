import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';

export default class TaxonomyFrame extends PureComponent {
	static propTypes = {
		onSelect: PropTypes.func.isRequired
	};

	render() {
		return (
			<div style={{width: '100%', height: '100%'}}>
				I am the taxonomy frame
			</div>
		);
	}
}
