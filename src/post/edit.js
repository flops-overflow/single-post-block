import { __ } from '@wordpress/i18n';

import { 
	__experimentalLinkControl as LinkControl, 
	useBlockProps,
	BlockControls,
	InspectorControls, 
} from '@wordpress/block-editor';

import { ToggleControl, RangeControl, ToolbarGroup, ToolbarButton, Spinner, Panel, PanelBody, PanelRow } from '@wordpress/components';

import { edit as editIcon, undo as undoIcon } from '@wordpress/icons';

import { useState, Fragment, RawHTML } from '@wordpress/element';

import apiFetch from '@wordpress/api-fetch';

import './editor.scss';

const PostPreview = ( props ) => {

	let { postId, headingLevel, showImage } = props;

	const [ thePost, setPostState ] = useState( null );
	const [ isLoading, setLoadingState ] = useState( false );

	let postClass = 'single-post-block__post-body';

	if ( ! thePost && ! isLoading ) {
		setLoadingState( 'loading' );
		apiFetch( { 
			path: '/nfntscrl/v1/single_post/' + postId,
		} )
		.then( ( response ) => {
			setPostState( { 
				title: response.title, 
				thumbnail: response.thumbnail,
				excerpt: RawHTML( { children: response.excerpt, className: 'single-post-block__post-excerpt-wrapper' } )
			} );
			postClass = 'single-post-block__post-body single-post-block__post-body--ok-state';
			setLoadingState( 'done' );

		} )
		.catch( ( e ) => {
			setPostState( { title: 'Error retrieving post', excerpt: e.message || 'An error occurred.' } );
			postClass = 'single-post-block__post-body single-post-block__post-body--error-state';
			setLoadingState( 'error' );
		} );
	};

	if ( ! headingLevel ) {
		headingLevel = 3;
	}

	return (
		<div {...useBlockProps()}>
			<div className="single-post-block__post-preview">
				{ thePost && (
					<div className={ postClass }>
						{ wp.element.createElement( 'h' + headingLevel, null, thePost.title ) }
						<div className="single-post-block__post-excerpt">{ thePost.excerpt }</div>
						{
							showImage && thePost.thumbnail && (
								<figure className="single-post-block__post-image">
									<img src={ thePost.thumbnail } alt="Post preview thumbnail" />
								</figure>
							)
						}
					</div>
					
				) || (
					<div className="single-post-block__loading-placeholder">
						<Spinner />
					</div>
				) }
				
			</div>
		</div>
	);
}

const PostSelector = ( props ) => {
	
	const { onChange } = props;

	return (
		<div className="single-post-block__post-selector">
			<label>Select a post to display:</label>
			<LinkControl
				suggestionsQuery={ {
					type: 'post',
					subtype: nfntscrlPostBlock.post_types ?? 'post',
				} }
				onChange={ ( value ) => {
					onChange && onChange( value.id );
				} }
				noDirectEntry={ true }
				settings={ [] }
			/>
		</div>
	);
};


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( props ) {
	const {
		attributes: { postId, displayOptions },
		setAttributes,
		className
	} = props;

	const blockProps = useBlockProps();

	const [ editorState, setEditorState ] = useState( ( typeof postId !== 'undefined' ) );

	const setDisplayOption = ( attribute, newValue ) => {
		let newDisplayOptions = JSON.parse( JSON.stringify( props.attributes.displayOptions ) );
		newDisplayOptions[attribute] = newValue;
		setAttributes( { displayOptions: newDisplayOptions } );
	};

	return (
		<div {...blockProps}>
			<Fragment>
				<InspectorControls key="display">
					<Panel header="Settings" className="single-post-block__panel--settings">
						<PanelBody title={ "Display Options" } initialOpen={ true }>
							<PanelRow>
								<RangeControl
									label="Heading Level"
									value={ displayOptions.headingLevel || 3 }
									onChange={ ( value ) => {
										setDisplayOption( 'headingLevel', value );
									} }
									min={ 1 }
									max={ 6 }
								/>
							</PanelRow>
							<PanelRow>
								<ToggleControl
									label={ "Show Thumbnail" }
									checked={ displayOptions.showImage || false }
									onChange={ () => {
										setDisplayOption( 'showImage', ! displayOptions.showImage );
									} }
								/>
							</PanelRow>
						</PanelBody>
					</Panel>
				</InspectorControls>
				{ editorState && (
					<Fragment>
						{
							<BlockControls>
								<ToolbarGroup>
									<ToolbarButton icon={ editIcon } label="Set Post" onClick={ () => setEditorState( false ) } />
								</ToolbarGroup>
							</BlockControls>
						}
						<PostPreview 
							postId={ postId } 
							postClass={ className }
							headingLevel={ displayOptions.headingLevel || nfntscrlPostBlock.default_heading_level } 
							showImage={ displayOptions.showImage || nfntscrlPostBlock.default_show_image } 
						/>
					</Fragment>
				) || (
					<Fragment>
						{ postId && (
							<BlockControls>
								<ToolbarGroup>
									<ToolbarButton icon={ undoIcon } label="Cancel" onClick={ () => setEditorState( true ) } />
								</ToolbarGroup>
							</BlockControls>
						) }
						<PostSelector onChange={ ( postId ) => {
							postId = parseInt( postId );
							setAttributes( { postId } );
							setEditorState( true );
						} } />
					</Fragment>
				) }
			</Fragment>
		</div>
	);
}
