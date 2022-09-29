<?php

use nfntscrl\Blocks\Single_Post;

// global: $the_post, $attributes

$attributes = apply_filters(
	'single_post_block_excerpt_attributes', 
	$attributes,
	$the_post
);

$tag_name = $attributes['displayOptions']['tag'] ?? 'blockquote';
$heading_level = 'h' . absint( $attributes['displayOptions']['headingLevel'] ?? '3' );

$tag_attrs = [
	'cite' => get_permalink( $the_post ),
	'class' => 'single-post-block wp-block',
];
$tag_attrs = apply_filters( 
	'single_post_block_excerpt_tag_attributes', 
	$tag_attrs,
	$attributes,
	$the_post
);
$tag_attrs = apply_filters_deprecated(
	'single_post_block_tag_attributes', 
	[ $tag_attrs ],
	'0.2',
	'single_post_block_excerpt_tag_attributes'
);

?>
<<?php echo esc_html( $tag_name ) ?> <?php echo Single_Post::array_to_html_attrs( $tag_attrs ) ?>>

	<?php do_action_deprecated( 'single_post_block_start', [ $the_post ], '0.2', 'single_post_block_excerpt_afterstart' ); ?>
	<?php do_action( 'single_post_block_excerpt_afterstart', $the_post ); ?>

	<?php if ( has_post_thumbnail( $the_post ) && ( $attributes['displayOptions']['showImage'] ?? false ) ) : ?>
	<?php echo get_the_post_thumbnail( $the_post, apply_filters( 'single_post_block_image_size', 'thumbnail', $the_post ) ); ?>
	<?php endif; ?>

	<<?php echo $heading_level; ?>><a href="<?php echo esc_url( $tag_attrs['cite'] ) ?>"><?php echo get_the_title( $the_post ); ?></a></<?php echo $heading_level; ?>>

	<?php if ( apply_filters( 'single_post_block_excerpt_show_post_excerpt', true, $the_post ) ) : ?>
	<?php echo get_the_excerpt( $the_post ); ?>
	<?php endif; ?>

	<?php do_action( 'single_post_block_excerpt_beforeend', $the_post ); ?>
	<?php do_action_deprecated( 'single_post_block_end', [ $the_post ], '0.2', 'single_post_block_excerpt_beforeend' ); ?>

</<?php echo esc_html( $tag_name ) ?>>