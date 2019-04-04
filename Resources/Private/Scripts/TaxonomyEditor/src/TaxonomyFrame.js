import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';

export default class TaxonomyFrame extends PureComponent {
	static propTypes = {
		onSelect: PropTypes.func.isRequired
	};

	componentDidMount() {
		const {onSelect} = this.props;

		window.open(
			'/thisWillBe',
			'imageDatabaseFrame'
		);
	}

	componentWillUnmount() {
		window.bilddb_callback = null; // eslint-disable-line
	}

	render() {
		return (
			<div style={{width: '100%', height: '100%'}}>
				<iframe
					style={{width: '100%', height: '100%'}}
					frameBorder="0"
					name="taxonomyFrame"
				/>
			</div>
	);
	}
}
