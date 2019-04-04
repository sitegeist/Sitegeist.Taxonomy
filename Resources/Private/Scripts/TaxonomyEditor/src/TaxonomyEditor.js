import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {IconButton} from '@neos-project/react-ui-components';

export default class TaxonomyEditor extends PureComponent {

    static propTypes = {
        value: PropTypes.string,
        commit: PropTypes.func.isRequired,
    };

    // handleChangeColor = newColor => {
    //     this.props.commit('rgba(' + newColor.rgb.r + ',' + newColor.rgb.g + ',' + newColor.rgb.b + ',' + newColor.rgb.a + ')');
    // };
	//
    // handleResetColorClick = () => {
    //     this.props.commit('');
    // };

	handleButtonClick = () => {
		alert ("world");
	};

    render() {
        return (
            <div>
				Hello
                <IconButton style="lighter" icon="times" title="Reset" onClick={this.handleButtonClick}/>
            </div>
        );
    }
}
