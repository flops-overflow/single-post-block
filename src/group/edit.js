
import { __ } from '@wordpress/i18n';

import {
	useBlockProps,
	InnerBlocks,
	InspectorControls
} from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	return (
		<div {...useBlockProps()}>
			<InnerBlocks
				allowedBlocks={ [ 'infinity-scroll/single-post-block' ] }
				template={ [ [ 'infinity-scroll/single-post-block' ] ] }
			/>
		</div>
	);
}
