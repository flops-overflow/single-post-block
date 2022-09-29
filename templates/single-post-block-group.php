<?php

use nfntscrl\Blocks\Single_Post;

// global: $the_post, $attributes

$attributes = apply_filters(
	'single_post_block_group_attributes', 
	$attributes
);

$inner_content = apply_filters(
	'single_post_block_group_inner_content', 
	$inner_content
);

$tag_attrs = [
	'class' => 'single-post-group wp-block',
];
$tag_attrs = apply_filters( 
	'single_post_block_group_tag_attributes', 
	$tag_attrs,
	$attributes,
);

?>
<div <?php echo Single_Post::array_to_html_attrs( $tag_attrs ) ?>>

	<?php do_action( 'single_post_block_group_afterstart' ); ?>

	<?php echo $inner_content; ?>

	<?php do_action( 'single_post_block_group_beforeend' ); ?>

</div>