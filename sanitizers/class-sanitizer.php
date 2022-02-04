<?php
/**
 * Sanitizer
 *
 * @package Google\AMP_Picture_Compat
 */

namespace Google\AMP_Picture_Compat;

use AMP_Base_Sanitizer;
use DOMNodeList;
use DOMElement;
use DOMXPath;
use AMP_DOM_Utils;
use AMP_Image_Dimension_Extractor;
use AmpProject\Html\Attribute;

/**
 * Class Sanitizer
 */
class Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$xpath = new DOMXPath( $this->dom );

		$pictures = $xpath->query( '//picture' );

		if ( $pictures instanceof DOMNodeList ) {
			foreach ( $pictures as $picture ) {
				if ( $picture instanceof DOMElement ) {
					$sources_attributes = array();
					$img_attributes     = '';
					
					if ( ! $picture->hasChildNodes() ) {
						return;
					}

					foreach ( $picture->childNodes as $child_node ) {

						if ( XML_TEXT_NODE === $child_node->nodeType ) {
							continue;
						}

						if ( 'source' === $child_node->tagName ) {
							$sources_attributes[] = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $child_node );
						}
						
						if ( 'img' === $child_node->tagName && empty( $img_attributes ) ) {
							$img_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $child_node );
						}
					}

					$picture->parentNode->replaceChild(
						$this->create_amp_img( $img_attributes, $sources_attributes ),
						$picture
					);

				}
			}
		}
	}

	/**
	 * Create AMP Image tag.
	 *
	 * @param array $img_attributes Image Attributes.
	 * @param array $sources_attributes Sources.
	 * @return DOMElement An amp-state element.
	 */
	private function create_amp_img( $img_attributes, $sources_attributes ) {
		if ( empty( $img_attributes ) && empty( $sources_attributes ) ) {
			return;
		}
		
		$amp_img = $this->dom->createElement( 'amp-img' );

		$src_set = '';
		foreach ( $sources_attributes as $sources_attribute ) {
			$size     = (int) filter_var( $sources_attribute['media'], FILTER_SANITIZE_NUMBER_INT );
			$src_set .= $sources_attribute['srcset'] . ' ' . trim( $size, '-' ) . 'w,';
		}

		$amp_img->setAttribute( 'layout', 'intrinsic' );
		$amp_img->setAttribute( 'srcset', rtrim( $src_set, ',' ) );
		
		$need_dimensions = array();
		foreach ( $img_attributes as $img_attribute_name => $img_attribute ) {
			if ( 'src' === $img_attribute_name ) {
				$need_dimensions[ $img_attribute ][] = $amp_img;
				$this->determine_dimensions( $need_dimensions );
			}
			$amp_img->setAttribute( $img_attribute_name, $img_attribute );
		}

		return $amp_img;
	}


	/**
	 * Determine width and height attribute values for images without them.
	 *
	 * Attempt to determine actual dimensions, otherwise set reasonable defaults.
	 *
	 * @param DOMElement[][] $need_dimensions Map <img> @src URLs to node for images with missing dimensions.
	 */
	private function determine_dimensions( $need_dimensions ) {

		$dimensions_by_url = AMP_Image_Dimension_Extractor::extract( array_keys( $need_dimensions ) );

		foreach ( $dimensions_by_url as $url => $dimensions ) {
			foreach ( $need_dimensions[ $url ] as $node ) {
				if ( ! $node instanceof DOMElement ) {
					continue;
				}
				$class = $node->getAttribute( Attribute::CLASS_ );
				if ( ! $class ) {
					$class = '';
				}
				if ( ! $dimensions ) {
					$class .= ' amp-wp-unknown-size';
				}

				$width  = isset( $this->args['content_max_width'] ) ? $this->args['content_max_width'] : self::FALLBACK_WIDTH;
				$height = self::FALLBACK_HEIGHT;
				if ( isset( $dimensions['width'] ) ) {
					$width = $dimensions['width'];
				}
				if ( isset( $dimensions['height'] ) ) {
					$height = $dimensions['height'];
				}

				if ( ! is_numeric( $node->getAttribute( Attribute::WIDTH ) ) ) {

					// Let width have the right aspect ratio based on the height attribute.
					if ( is_numeric( $node->getAttribute( Attribute::HEIGHT ) ) && isset( $dimensions['height'], $dimensions['width'] ) ) {
						$width = ( (float) $node->getAttribute( Attribute::HEIGHT ) * $dimensions['width'] ) / $dimensions['height'];
					}

					$node->setAttribute( Attribute::WIDTH, $width );
					if ( ! isset( $dimensions['width'] ) ) {
						$class .= ' amp-wp-unknown-width';
					}
				}
				if ( ! is_numeric( $node->getAttribute( Attribute::HEIGHT ) ) ) {

					// Let height have the right aspect ratio based on the width attribute.
					if ( is_numeric( $node->getAttribute( Attribute::WIDTH ) ) && isset( $dimensions['height'], $dimensions['width'] ) ) {
						$height = ( (float) $node->getAttribute( Attribute::WIDTH ) * $dimensions['height'] ) / $dimensions['width'];
					}

					$node->setAttribute( Attribute::HEIGHT, $height );
					if ( ! isset( $dimensions['height'] ) ) {
						$class .= ' amp-wp-unknown-height';
					}
				}
				$node->setAttribute( Attribute::CLASS_, trim( $class ) );
			}
		}
	}

}
