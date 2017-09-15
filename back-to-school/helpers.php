<?php
function sanitize_title_with_dashes( $title, $raw_title = '', $context = 'display' ) {
	$title = strip_tags($title);
	// Preserve escaped octets.
	$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
	// Remove percent signs that are not part of an octet.
	$title = str_replace('%', '', $title);
	// Restore octets.
	$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

	$title = strtolower($title);
	$title = preg_replace('/&.+?;/', '', $title); // kill entities
	$title = str_replace('.', '-', $title);

	if ( 'save' == $context ) {
		// Convert nbsp, ndash and mdash to hyphens
		$title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );

		// Strip these characters entirely
		$title = str_replace( array(
			// iexcl and iquest
			'%c2%a1', '%c2%bf',
			// angle quotes
			'%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
			// curly quotes
			'%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
			'%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
			// copy, reg, deg, hellip and trade
			'%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
			// acute accents
			'%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
			// grave accent, macron, caron
			'%cc%80', '%cc%84', '%cc%8c',
		), '', $title );

		// Convert times to x
		$title = str_replace( '%c3%97', 'x', $title );
	}

	$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
	$title = preg_replace('/\s+/', '-', $title);
	$title = preg_replace('|-+|', '-', $title);
	$title = trim($title, '-');

	return $title;
}

function wpautop($pee, $br = true) {
	$pre_tags = array();

	if ( trim($pee) === '' )
		return '';

	// Just to make things a little easier, pad the end.
	$pee = $pee . "\n";

	/*
	 * Pre tags shouldn't be touched by autop.
	 * Replace pre tags with placeholders and bring them back after autop.
	 */
	if ( strpos($pee, '<pre') !== false ) {
		$pee_parts = explode( '</pre>', $pee );
		$last_pee = array_pop($pee_parts);
		$pee = '';
		$i = 0;

		foreach ( $pee_parts as $pee_part ) {
			$start = strpos($pee_part, '<pre');

			// Malformed html?
			if ( $start === false ) {
				$pee .= $pee_part;
				continue;
			}

			$name = "<pre wp-pre-tag-$i></pre>";
			$pre_tags[$name] = substr( $pee_part, $start ) . '</pre>';

			$pee .= substr( $pee_part, 0, $start ) . $name;
			$i++;
		}

		$pee .= $last_pee;
	}
	// Change multiple <br>s into two line breaks, which will turn into paragraphs.
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);

	$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

	// Add a single line break above block-level opening tags.
	$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);

	// Add a double line break below block-level closing tags.
	$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);

	// Standardize newline characters to "\n".
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee);

	// Collapse line breaks before and after <option> elements so they don't get autop'd.
	if ( strpos( $pee, '<option' ) !== false ) {
		$pee = preg_replace( '|\s*<option|', '<option', $pee );
		$pee = preg_replace( '|</option>\s*|', '</option>', $pee );
	}

	/*
	 * Collapse line breaks inside <object> elements, before <param> and <embed> elements
	 * so they don't get autop'd.
	 */
	if ( strpos( $pee, '</object>' ) !== false ) {
		$pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
		$pee = preg_replace( '|\s*</object>|', '</object>', $pee );
		$pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
	}

	/*
	 * Collapse line breaks inside <audio> and <video> elements,
	 * before and after <source> and <track> elements.
	 */
	if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
		$pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
		$pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
		$pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
	}

	// Remove more than two contiguous line breaks.
	$pee = preg_replace("/\n\n+/", "\n\n", $pee);

	// Split up the contents into an array of strings, separated by double line breaks.
	$pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);

	// Reset $pee prior to rebuilding.
	$pee = '';

	// Rebuild the content as a string, wrapping every bit with a <p>.
	foreach ( $pees as $tinkle ) {
		$pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
	}

	// Under certain strange conditions it could create a P of entirely whitespace.
	$pee = preg_replace('|<p>\s*</p>|', '', $pee);

	// Add a closing <p> inside <div>, <address>, or <form> tag if missing.
	$pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);

	// If an opening or closing block element tag is wrapped in a <p>, unwrap it.
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

	// In some cases <li> may get wrapped in <p>, fix them.
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);

	// If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);

	// If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);

	// If an opening or closing block element tag is followed by a closing <p> tag, remove it.
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

	// Optionally insert line breaks.
	if ( $br ) {
		// Replace newlines that shouldn't be touched with a placeholder.
		$pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);

		// Replace any new line characters that aren't preceded by a <br /> with a <br />.
		$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);

		// Replace newline placeholders with newlines.
		$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
	}

	// If a <br /> tag is after an opening or closing block tag, remove it.
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);

	// If a <br /> tag is before a subset of opening or closing block tags, remove it.
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
	$pee = preg_replace( "|\n</p>$|", '</p>', $pee );

	// Replace placeholder <pre> tags with their original content.
	if ( !empty($pre_tags) )
		$pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);

	return $pee;
}

function _autop_newline_preservation_helper( $matches ) {
	return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
}

function do_widget( $matches ) {
	global $picks;
	$widget_title = $matches[1];
	if( !$widget_title ) {
		return '';
	}

	if( !isset( $picks[ $widget_title ] ) ) {
		return '';
	}

	$widget = $picks[ $widget_title ];
	$sizes = array(
		'180' => '180/' . $widget['image'] . '-180.jpg',
		'360' => '360/' . $widget['image'] . '-360.jpg',
	);

	$srcset = array();
	foreach( $sizes as $size => $path ) {
		$srcset[] = maybe_absolute_url() . 'img/widgets/' . $sizes[ $size ] . ' ' . $size . 'w';
	}

	$output = '<div class="widget">';
	$output .= '<a href="' . strip_affiliate_tags( $widget['link'] ) . '"><img src="' . maybe_absolute_url() . 'img/widgets/' . $sizes['180'] . '" srcset="' . implode(', ', $srcset) . '" alt="' . $widget['title'] . '" class="widget-image"></a>';
	$output .= '<p class="widget-tagline">' . $widget['tagline'] . '</p>';
	$output .= '<h6 class="widget-title">' . $widget['title'] . '</h6>';
	$output .= '<p class="widget-tease">' . $widget['tease'] . '</p>';
	$output .= '<a href="' . strip_affiliate_tags( $widget['link'] ) . '" class="widget-add-to-cart">Add to Cart</a>';
	$output .= '</div>';

	return $output;
}

function do_image( $matches ) {
	$file_name = $matches;
	if( is_array($matches) && isset( $matches[1] ) ) {
		$file_name = $matches[1];
	}

	$sizes = array(
		'466' => '466/' . $file_name . '-466.jpg',
		'700' => '700/' . $file_name . '-700.jpg',
		'1400' => '1400/' . $file_name . '-1400.jpg',
	);

	$srcset = array();
	foreach( $sizes as $size => $path ) {
		$srcset[] = maybe_absolute_url() . 'img/products/' . $sizes[ $size ] . ' ' . $size . 'w';
	}

	return '<img src="' . maybe_absolute_url() . 'img/products/' . $sizes['700'] . '" srcset="' . implode(', ', $srcset) . '" alt="" class="product-image">';
}

function format_widgets( $content ) {
	$content = preg_replace_callback(
        '/<p>\[widget\s+(.+)\]<\/p>/i',
        'do_widget',
        $content
    );

	return $content;
}

function format_images( $content ) {
	$content = preg_replace_callback(
        '/<p>\[image\s+(.+)\]<\/p>/i',
        'do_image',
        $content
    );

	return $content;
}


function strip_affiliate_tags( $str ) {
	return str_replace( '?tag=thesweethome-20', '', $str );
}

function get_review( $title ) {
	global $reviews;
	$title = strtolower($title);
	foreach( $reviews as $review ) {
		if ( ! isset( $review['title'] ) ) {
			continue;
		}

		if( $title == strtolower( $review['title'] ) ) {
			$review['content'] = wpautop( $review['content'] );
			$review['content'] = format_widgets( $review['content'] );
			$review['content'] = format_images( $review['content'] );
			$review['content'] = strip_affiliate_tags( $review['content'] );

			return $review;
		}
	}

	return array(
		'title' => '',
		'content' => ''
	);
}

function get_svg( $slug, $fallback_filename = false, $stroke_color = false, $fill_color = false ) {

	$fallback = 'img/' . $slug . '.png';
	if( $fallback_filename ) {
		$fallback = 'img/' . $fallback_filename . '.png';
	}

	$fill = 'none';
	if( $fill_color ) {
		$fill = $fill_color;
	}

	switch( $slug ) {
		case 'tech':
			$stroke = '#4E58D8';
			if( $stroke_color ) {
				$stroke = $stroke_color;
			}

			return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="130.852" height="130.852" viewBox="0 0 130.852 130.852"><a class="svg-hide-on-fallback"><defs><path id="a" d="M0 0h130.852v130.852H0z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><circle clip-path="url(#b)" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" cx="65.426" cy="65.426" r="64.426"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M88.874 96.857c-.004 5.175-4.214 9.38-9.39 9.377l-16.757-.012c-4.95-.003-9-3.86-9.343-8.72h2.01c.34 3.754 3.496 6.707 7.334 6.71l16.754.012c4.067.002 7.374-3.305 7.376-7.368l.016-22.788h2.01l-.015 22.79h.004zm-45.577-3.38l.042-63.674c0-1.48 1.202-2.683 2.684-2.682l16.755.012c1.476 0 2.68 1.203 2.68 2.685l-.043 63.674c0 1.478-1.202 2.678-2.684 2.678l-16.756-.012c-1.476 0-2.68-1.203-2.68-2.68M95.602 60.67l-.004 5.753-3.158 3.154c-.06.063-.112.136-.145.217-.034.08-.053.17-.053.255l-.002 2.683-8.713-.005.002-2.684c0-.086-.017-.175-.052-.256-.034-.08-.085-.158-.145-.217l-3.153-3.157.004-5.753 15.418.01zm-12.06-9.057c0-.554.45-1.005 1.006-1.005.554 0 1.005.452 1.004 1.006l-.005 7.708h-2.01l.005-7.71zm6.703.004c0-.554.452-1.005 1.006-1.005.555 0 1.006.453 1.005 1.006l-.005 7.708h-2.01l.005-7.71zM96.938 60c0-.372-.297-.67-.668-.67l-2.683-.003.005-7.708c0-1.296-1.05-2.348-2.345-2.35-1.296 0-2.35 1.05-2.35 2.346l-.005 7.708h-2.01l.005-7.71c0-1.294-1.05-2.346-2.345-2.347-1.294 0-2.348 1.05-2.348 2.345l-.005 7.708-2.68-.002c-.37 0-.67.298-.67.67l-.004 6.702c0 .09.017.174.05.255.035.082.086.158.145.218l3.156 3.158-.002 3.072c0 .368.297.668.668.67h2.683l-.015 22.79c-.003 3.324-2.71 6.026-6.035 6.024l-16.758-.01c-3.1-.003-5.655-2.355-5.99-5.367l5.994.003c2.22.002 4.022-1.803 4.023-4.017l.042-63.67c0-2.214-1.798-4.02-4.017-4.022l-16.755-.01c-2.22-.003-4.02 1.802-4.023 4.017l-.04 63.675c0 2.22 1.803 4.02 4.018 4.02l6.065.005c.345 5.602 4.997 10.06 10.683 10.063l16.754.01c5.91.005 10.728-4.804 10.73-10.716l.016-22.788h2.68c.37 0 .667-.298.667-.668l.002-3.075 3.16-3.153c.06-.063.11-.136.144-.218.034-.08.05-.17.05-.255L96.935 60h.003z"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M51.028 60.64c.554 0 1.005.453 1.005 1.007 0 .553-.452 1.004-1.006 1.003-.554 0-1.005-.452-1.005-1.005 0-.558.452-1.004 1.006-1.004m-.002 3.348c1.294 0 2.347-1.05 2.348-2.345 0-1.295-1.05-2.348-2.345-2.35-1.296 0-2.35 1.052-2.35 2.347 0 1.294 1.05 2.347 2.346 2.348M57.73 60.646c.555 0 1.006.452 1.006 1.006 0 .553-.452 1.004-1.006 1.003-.554 0-1.005-.452-1.005-1.005 0-.558.452-1.004 1.006-1.004m0 3.347c1.294 0 2.347-1.05 2.348-2.345 0-1.295-1.05-2.348-2.346-2.35-1.294 0-2.347 1.052-2.348 2.347 0 1.293 1.05 2.347 2.345 2.348"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M54.384 53.94c4.25.002 7.706 3.462 7.703 7.713-.003 4.25-3.463 7.706-7.713 7.703s-7.706-3.463-7.703-7.712c.004-4.25 3.464-7.707 7.714-7.705m-.01 16.755c4.987.003 9.05-4.054 9.052-9.04.003-4.99-4.053-9.05-9.04-9.053-4.99-.003-9.053 4.052-9.056 9.04-.005 4.99 4.056 9.05 9.044 9.053M51.014 82.756c-.554 0-1.005-.452-1.005-1.005 0-.553.45-1.004 1.005-1.004.554 0 1.005.452 1.005 1.006s-.452 1.005-1.006 1.004m.002-3.35c-1.295 0-2.348 1.05-2.35 2.345 0 1.296 1.052 2.348 2.347 2.348 1.294 0 2.347-1.05 2.348-2.344.002-1.295-1.05-2.347-2.344-2.348M57.718 80.75c.554 0 1.005.453 1.005 1.006 0 .554-.452 1.005-1.006 1.005s-1.005-.45-1.005-1.005c0-.553.452-1.005 1.006-1.004m-.002 3.352c1.295 0 2.348-1.05 2.35-2.345 0-1.294-1.052-2.347-2.347-2.347s-2.348 1.05-2.35 2.344c0 1.295 1.052 2.347 2.346 2.348"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M46.658 81.75c.002-4.25 3.463-7.707 7.713-7.704 4.25.004 7.707 3.464 7.704 7.714s-3.463 7.706-7.713 7.702c-4.25-.004-7.705-3.463-7.702-7.713m16.76.01c.002-4.987-4.054-9.05-9.04-9.053-4.992-.003-9.054 4.054-9.057 9.04-.002 4.99 4.054 9.054 9.045 9.057 4.986.004 9.048-4.057 9.052-9.044M51.042 40.53c.554 0 1.005.454 1.005 1.006 0 .554-.452 1.005-1.006 1.005-.553 0-1.004-.45-1.004-1.005 0-.554.452-1.005 1.006-1.005m-.002 3.353c1.294 0 2.347-1.05 2.348-2.346 0-1.294-1.05-2.347-2.345-2.348-1.295 0-2.348 1.05-2.35 2.344 0 1.295 1.052 2.348 2.347 2.35M57.745 40.534c.554 0 1.005.454 1.005 1.006 0 .554-.452 1.005-1.006 1.005s-1.005-.452-1.005-1.006.45-1.006 1.005-1.006m-.002 3.353c1.295 0 2.348-1.05 2.35-2.346 0-1.293-1.052-2.346-2.347-2.347-1.294 0-2.347 1.05-2.348 2.345-.002 1.295 1.05 2.348 2.345 2.35"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M54.397 33.834c4.25.003 7.706 3.463 7.703 7.713-.003 4.25-3.463 7.707-7.713 7.704-4.25 0-7.706-3.462-7.703-7.713.004-4.25 3.463-7.706 7.713-7.703m-.01 16.754c4.987.003 9.05-4.053 9.052-9.04.002-4.99-4.05-9.053-9.04-9.056-4.992-.003-9.054 4.053-9.057 9.04-.005 4.99 4.056 9.053 9.044 9.056"/></a><image src="' . $fallback . '" xlink:href=""></svg>';
		break;

		case 'school-supplies':
			$stroke = '#ff3d00';
			if( $stroke_color ) {
				$stroke = $stroke_color;
			}

			return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="130.852" height="130.852" viewBox="0 0 130.852 130.852"><a class="svg-hide-on-fallback"><defs><path id="a" d="M0 0h130.852v130.852H0z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><circle clip-path="url(#b)" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" cx="65.426" cy="65.426" r="64.426"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M90.176 90.303c-2.94.48-5.266 2.805-5.746 5.742H48.637v-62.07h41.54v56.328zm0 5.742h-3.453c.418-1.7 1.754-3.035 3.453-3.453v3.453zm-43.79 0h-5.71v-62.07h5.71v62.07zm44.915-64.32H39.55c-.62 0-1.124.504-1.124 1.125v64.32c0 .62.504 1.125 1.125 1.125H91.3c.622 0 1.126-.505 1.126-1.125V32.85c0-.62-.504-1.125-1.125-1.125"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M55.23 42.95H83.78v14.52H55.23V42.95zm-1.124 16.77h30.797c.62 0 1.125-.503 1.125-1.124v-16.77c0-.62-.504-1.125-1.125-1.125H54.106c-.62 0-1.125.505-1.125 1.126v16.77c0 .62.505 1.125 1.126 1.125"/></a><image src="' . $fallback . '" xlink:href=""></svg>';
		break;

		case 'dorm-life':
			$stroke = '#b41981';
			if( $stroke_color ) {
				$stroke = $stroke_color;
			}

			return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="130.852" height="130.852" viewBox="0 0 130.852 130.852"><a class="svg-hide-on-fallback"><defs><path id="a" d="M0 0h130.852v130.852H0z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><circle clip-path="url(#b)" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" cx="65.426" cy="65.426" r="64.426"/><path clip-path="url(#b)" fill="none" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" d="M33.496 48.86L44.033 94.48c.134.58.913.82 1.505.463l65.066-39.056c.773-.465.542-1.463-.356-1.54l-75.603-6.567c-.688-.06-1.284.497-1.15 1.076z"/><path transform="matrix(-.974 .225 -.225 -.974 88.176 134.369)" clip-path="url(#b)" fill="none" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" d="M34.035 45.66h4.79v53.1h-4.79z"/><path clip-path="url(#b)" fill="none" stroke="' . $stroke . '" stroke-width="2" stroke-miterlimit="10" d="M82.705 51.958l4.354 18.847M77.2 51.03l5.078 21.983M22.495 57.417l8.064 3.663-5.64 6.835M28.016 82.133l8.067 3.662-5.64 6.834"/><path clip-path="url(#b)" fill="' . $stroke . '" d="M45.89 61.308l1.66 7.195c.777 3.366 3.023 6.15 6.177 7.61l4.85 2.28 3.36-4.175c2.206-2.646 3.005-6.135 2.217-9.546l-1.66-7.195c-1.01.04-2.4.116-3.88.02-2.076-.152-3.815-.528-5.135-1.195-.94 1.188-2.293 2.278-4.09 3.324-1.3.69-2.574 1.274-3.5 1.682m12.963 18.286l-5.5-2.57c-3.437-1.634-5.874-4.618-6.725-8.308l-1.84-7.98.357-.13c.937-.362 2.35-.98 3.72-1.78 1.843-1.058 3.23-2.204 4.068-3.417l.26-.352.386.202c1.272.678 2.986 1.156 5.154 1.288 1.582.12 3.122.057 4.122-.03l.38-.037 1.84 7.98c.84 3.643-.03 7.44-2.416 10.37l-3.807 4.764z"/><path clip-path="url(#b)" fill="none" stroke="' . $stroke . '" stroke-width=".5" stroke-miterlimit="10" d="M45.89 61.308l1.66 7.195c.777 3.366 3.023 6.15 6.177 7.61l4.85 2.28 3.36-4.175c2.206-2.646 3.005-6.135 2.217-9.546l-1.66-7.195c-1.01.04-2.4.116-3.88.02-2.076-.152-3.815-.528-5.135-1.195-.94 1.188-2.293 2.278-4.09 3.324-1.3.69-2.574 1.274-3.5 1.682zm12.963 18.286l-5.5-2.57c-3.437-1.634-5.874-4.618-6.725-8.308l-1.84-7.98.357-.13c.937-.362 2.35-.98 3.72-1.78 1.843-1.058 3.23-2.204 4.068-3.417l.26-.352.386.202c1.272.678 2.986 1.156 5.154 1.288 1.582.12 3.122.057 4.122-.03l.38-.037 1.84 7.98c.84 3.643-.03 7.44-2.416 10.37l-3.807 4.764z"/><path transform="matrix(-.974 .225 -.225 -.974 125.562 119.796)" clip-path="url(#b)" fill="' . $stroke . '" d="M55.485 55.216h.947v23.667h-.947z"/><path transform="matrix(-.974 .225 -.225 -.974 125.56 119.799)" clip-path="url(#b)" fill="' . $stroke . '" d="M46.965 66.576h17.987v.948H46.965z"/><path transform="matrix(-.848 -.53 .53 -.848 54.794 139.435)" clip-path="url(#b)" fill="' . $stroke . '" d="M46.915 59.352h.945v5.018h-.945z"/><path transform="matrix(-.862 -.507 .507 -.862 61.506 140.541)" clip-path="url(#b)" fill="' . $stroke . '" d="M49.436 55.686h.946v12.4h-.946z"/><path transform="matrix(-.848 -.53 .53 -.848 60.588 145.43)" clip-path="url(#b)" fill="' . $stroke . '" d="M50.66 58.353h.946v11.36h-.946z"/><path transform="matrix(-.848 -.53 .53 -.848 63.099 148.305)" clip-path="url(#b)" fill="' . $stroke . '" d="M52.363 61.404h.946v7.383h-.947z"/><path transform="matrix(-.848 -.53 .53 -.848 65.697 151.2)" clip-path="url(#b)" fill="' . $stroke . '" d="M54.053 64.5h.945v3.362h-.945z"/><path transform="matrix(-.848 -.53 .53 -.848 70.001 155.983)" clip-path="url(#b)" fill="' . $stroke . '" d="M56.89 66.275h.946v3.362h-.945z"/><path transform="matrix(-.848 -.53 .53 -.848 72.62 158.82)" clip-path="url(#b)" fill="' . $stroke . '" d="M58.607 65.307h.945v7.384h-.945z"/><path transform="matrix(-.848 -.53 .53 -.848 75.203 161.692)" clip-path="url(#b)" fill="' . $stroke . '" d="M60.31 64.384h.945v11.36h-.945z"/><path transform="matrix(-.848 -.53 .53 -.848 75.837 166.314)" clip-path="url(#b)" fill="' . $stroke . '" d="M61.29 65.918h.945V78.65h-.945z"/></a><image src="' . $fallback . '" xlink:href=""></svg>';
		break;

		case 'eat-drink':
			$stroke = '#db8100';
			if( $stroke_color ) {
				$stroke = $stroke_color;
			}

			return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="130.852" height="130.852" viewBox="0 0 130.852 130.852"><a class="svg-hide-on-fallback"><defs><path id="a" d="M0 0h130.852v130.852H0z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><circle clip-path="url(#b)" fill="' . $fill . '" stroke=' . $stroke . ' stroke-width="2" stroke-miterlimit="10" cx="65.426" cy="65.426" r="64.426"/><path clip-path="url(#b)" fill=' . $stroke . ' d="M79.783 46.872l-15.31.02-.008-6.795 15.308-.016.01 6.792zm6.494 7.247l.217-.363c.023-.038.04-.083.062-.122 10.2.23 11.33 11.88 11.334 16.923.007 5.04-1.1 16.69-11.303 16.944-.02-.04-.034-.087-.06-.122l-.22-.36-4.1.003v-1.325h-.884l-.032-30.24h.886l-.002-1.326 4.103-.013zm-.89-1.502l-3.21.003-.003-2.92 3.21-.005c.352.873.353 2.055.002 2.923m16.042 17.933c.006 5.896-1.4 19.42-14.276 20.025.052-.533.027-1.077-.062-1.602 2.992-.176 5.52-1.296 7.484-3.266 3.106-3.112 4.822-8.32 4.815-15.16-.017-11.157-4.613-17.947-12.34-18.39.09-.527.114-1.077.06-1.612 12.877.586 14.312 14.112 14.318 20.006M82.214 91.444l-.006-2.925h3.21c.36.87.36 2.05.006 2.922l-3.21.003zm-1.496 1.327l-1.3.002-1.332-1.33-.002-2.917 1.33-1.333h1.298l.006 5.578zm-17.6 2.225l-.003-1.945c.356-.123.798-.124 1.155 0l.002 1.95-1.155-.005zm14.505 1.546v1.155l-27.698.03V96.57l27.698-.03zm2.217 3.38c0 .984-.383 1.904-1.075 2.6-.695.695-1.615 1.076-2.596 1.08l-24.774.027c-.983 0-1.906-.38-2.6-1.07-.694-.693-1.076-1.616-1.077-2.6l-.048-44.47 15.31-.016.04 36.066c-.396.08-.775.23-1.094.455l-.318.224.005 2.844h-.78l-1.498.005H58.8l-1.497.002h-.536l-1.502.002-.533.004-1.5.005h-.536l-1.497.002-2.774.003.004 4.154 30.698-.032-.004-4.154-2.995.003-1.503-.003h-.535l-1.497.002h-4.611l-1.498.004h-.717l-.003-2.845-.315-.228c-.273-.195-.597-.33-.934-.42l-.038-36.1 15.312-.015.03 30.245h-1.037l-2.204 2.21.004 4.16 2.213 2.207 1.036-.005.007 5.656zM46.777 48.41l33.892-.035.006 5.58-33.89.03-.01-5.575zm-.76-4.805c-.65-.467-1.388-.635-2.05-.667l3.216-2.82 15.78-.02.008 6.795-15.34.016c-.13-1.523-.672-2.635-1.614-3.305m.75-6.144l33.89-.032v1.155l-33.885.03-.003-1.152zm13.397-7.977l7.077-.007c5.355-.01 8.854.7 10.69 2.157 1.342 1.065 1.744 2.498 1.822 4.293l-32.086.03c.147-3.598 1.66-6.46 12.497-6.473m5.796-5.792c-.33.94-1.24 1.657-2.26 1.657-.645.004-1.247-.25-1.7-.702-.272-.27-.46-.596-.576-.95l4.535-.004zm-3.967-2.445c.455-.456 1.058-.706 1.698-.706.642-.002 1.25.246 1.703.698.27.27.456.6.575.95l-4.546.004c.113-.35.302-.676.57-.947M82.16 40.078l-.005-4.154h-.898c-.075-1.993-.514-3.976-2.394-5.466-2.143-1.702-5.837-2.49-11.623-2.48h-2.79v-1.203c.756-.15 1.45-.516 2.01-1.074.557-.558.924-1.257 1.068-2.01h.59V22.19h-.59c-.147-.76-.514-1.455-1.072-2.013-.738-.737-1.72-1.142-2.764-1.14-1.044 0-2.024.407-2.76 1.145-.558.56-.922 1.255-1.07 2.012h-.59v1.498h.59c.15.76.513 1.455 1.075 2.01.558.557 1.258.923 2.012 1.07v1.203l-2.79.003c-11.12.012-13.77 3.046-13.993 7.984l-.903-.002.004 3.834-3.537 3.098c-.163.14-.256.35-.256.566l.004.444c0 .243.118.468.315.61.197.14.45.177.68.096.016-.005 1.632-.528 2.673.213.545.39.87 1.093.978 2.084l-.845.002.008 8.578h.884l.045 44.47c.004 2.85 2.33 5.17 5.18 5.168l24.77-.026c1.427 0 2.72-.582 3.656-1.522.936-.938 1.514-2.23 1.516-3.66l-.008-5.65h.885v-1.326l4.098-.004.218-.362c.09-.152.168-.318.24-.49 4.267-.11 7.812-1.58 10.477-4.25 3.668-3.675 5.687-9.597 5.68-17.284-.013-13.273-6.064-21.272-16.203-21.513-.073-.167-.147-.33-.236-.48l-.218-.36-4.1.003v-1.325h-.885l-.007-6.794.88-.006z"/></a><image src="' . $fallback . '" xlink:href=""></svg>';
		break;

		case 'sweethome':
			return '<?xml version="1.0" encoding="utf-8"?>
<!-- Generator: Adobe Illustrator 16.0.4, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="702.991px" height="76.209px" viewBox="0 0 702.991 76.209" enable-background="new 0 0 702.991 76.209"
	 xml:space="preserve">
<g>
	<defs>
		<rect id="SVGID_1_" width="702.991" height="76.209"/>
	</defs>
	<clipPath id="SVGID_2_">
		<use xlink:href="#SVGID_1_"  overflow="visible"/>
	</clipPath>
	<path clip-path="url(#SVGID_2_)" fill="#E94036" d="M73.582,76.209h-23.65c-0.879,0-1.7-0.44-2.187-1.172l-9.639-14.456
		l-9.638,14.456c-0.489,0.731-1.309,1.172-2.187,1.172H2.629C1.176,76.209,0,75.032,0,73.58V26.279c0-0.88,0.44-1.701,1.17-2.188
		L36.646,0.44c0.885-0.587,2.034-0.587,2.917,0l35.477,23.651c0.73,0.487,1.17,1.308,1.17,2.188V73.58
		C76.21,75.032,75.033,76.209,73.582,76.209 M51.337,70.953h19.617V27.685l-32.849-21.9l-32.849,21.9v43.269h19.617l10.074-15.11
		L24.093,39.563c-0.537-0.81-0.589-1.845-0.131-2.7s1.347-1.388,2.318-1.388h23.651c0.968,0,1.859,0.533,2.317,1.388
		s0.407,1.891-0.131,2.7L41.264,55.843L51.337,70.953z M31.188,40.732l6.917,10.373l6.915-10.373H31.188z"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#3C4544" points="117.928,0.155 102.751,0.155 102.751,2.841 108.882,2.841
		108.882,13.397 111.796,13.397 111.796,2.841 117.928,2.841 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#3C4544" points="131.492,8.081 141.07,8.081 141.07,13.397 143.983,13.397
		143.983,0.155 141.07,0.155 141.07,5.394 131.492,5.394 131.492,0.155 128.58,0.155 128.58,13.397 131.492,13.397 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#3C4544" points="158.474,8.024 168.921,8.025 168.921,5.434 158.474,5.433
		158.474,2.747 169.772,2.747 169.772,0.155 155.58,0.155 155.58,13.397 169.869,13.397 169.869,10.804 158.474,10.804 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#E94036" points="257.095,34.384 257.095,76.209 298.919,76.209 298.919,68.173
		264.938,68.173 264.938,59.071 298.919,59.071 298.919,51.229 264.938,51.229 264.938,42.227 298.919,42.227 298.919,34.384 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#F5BE65" points="319.6,34.384 319.6,76.209 361.424,76.209 361.424,68.173
		327.441,68.173 327.441,59.071 361.424,59.071 361.424,51.229 327.441,51.229 327.441,42.227 361.424,42.227 361.424,34.384 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#8AC0C1" points="216.52,64.978 205.048,34.384 196.625,34.384 185.151,64.978
		173.679,34.384 165.257,34.384 180.94,76.21 189.363,76.21 200.835,45.616 212.308,76.21 220.73,76.21 236.415,34.384
		227.992,34.384 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#E94036" points="616.379,34.384 604.906,64.978 593.436,34.384 585.011,34.384
		569.327,76.209 577.75,76.209 589.223,45.614 600.695,76.209 609.118,76.209 620.591,45.616 632.062,76.209 640.487,76.209
		624.802,34.384 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#F5BE65" points="702.991,42.227 702.991,34.385 661.167,34.385 661.167,76.209
		702.991,76.209 702.991,68.173 669.009,68.173 669.009,59.071 702.991,59.071 702.991,51.229 669.009,51.229 669.009,42.227 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#3C4544" points="478.373,51.229 452.232,51.229 452.232,34.384 444.318,34.384
		444.318,76.21 452.232,76.21 452.232,59.071 478.373,59.071 478.373,76.21 486.144,76.21 486.144,34.384 478.373,34.384 	"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#888888" points="382.104,34.384 382.104,42.227 398.95,42.227 398.95,76.209
		406.791,76.209 406.791,42.227 423.637,42.227 423.637,34.384 	"/>
	<path clip-path="url(#SVGID_2_)" fill="#8AC0C1" d="M512.051,34.384l-5.228,5.229V70.98l5.228,5.229h31.368l5.228-5.229V39.612
		l-5.228-5.229H512.051z M540.805,68.366h-26.141V42.226h26.141V68.366z"/>
	<polygon clip-path="url(#SVGID_2_)" fill="#3C4544" points="110.594,51.229 110.594,42.227 139.347,42.227 139.347,34.384
		107.979,34.384 102.751,39.612 102.751,53.845 107.979,59.071 136.733,59.071 136.733,68.366 102.751,68.366 102.751,76.21
		139.347,76.21 144.575,70.98 144.575,56.46 139.347,51.229 	"/>
</g>
<image src="' . $fallback . '" xlink:href="">
</svg>';
		break;
	}
}

function maybe_absolute_url() {
	global $is_amazon;
	if( $is_amazon ) {
		return 'http://russellheimlich.com/back-to-school/';
	}
	return '';
}
