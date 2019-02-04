<?php
namespace mbelchev\DummyImages;

class Image {

	const TEXT_FONT = DUMMY_IMAGES_DIR . 'assets/font/roboto.ttf';

	private $image,
			$text_color,
			$size,
			$bg_color,
			$text;

	public function __construct( $size = array(), $bg_color = null, $text = null, $text_color = null ) {
		if ( empty( $this->size = $size ) )
			throw new \Exception( 'Select sizes for the image' );

		if ( empty( $this->bg_color = $bg_color ) )
			throw new \Exception( 'Select background color for the image' );

		if ( empty( $this->text = $text ) )
			throw new \Exception( 'Enter text for the image' );

		if ( empty( $this->text_color = $text_color ) )
			throw new \Exception( 'Select text color for the image' );
	}

	public static function initiate( $size, $bg_color, $text, $text_color ) {
		return new self( $size, $bg_color, $text, $text_color );
	}

	public function generate( $destination, $file_name ) {
		$this->set_image()->set_background()->set_text()->create_image( $destination, $file_name )->destroy_image();
	}

	private function set_image() {
		if ( ! $this->image = imagecreate( $this->size[ 0 ], $this->size[ 1 ] ) ) {
			throw new \Exception( 'Create a new true color image failed' );
		}

		return $this;
	}

	private function set_text_container() {
		if ( ! $text_box = imagettfbbox( $this->get_font_size(), 0, self::TEXT_FONT, $this->text ) ) {
			throw new \Exception( 'Creating the text container failed' );
		}

		return $text_box;
	}

	private function set_text() {
		$text_box = $this->set_text_container();

		$x = ( $this->size[0] - ( $text_box[4] + $text_box[0] ) ) / 2;
		$y = ( $this->size[1] - ( $text_box[5] + $text_box[1] ) ) / 2;

		if ( ! imagettftext( $this->image, $this->get_font_size(), 0, $x, $y, $this->allocate_color( $this->text_color ), self::TEXT_FONT, $this->text ) ) {
			throw new \Exception( 'Writing the text to the image failed' );
		}

		return $this;
	}

	private function set_background() {
		if ( ! imagefill( $this->image, 0, 0, $this->allocate_color( $this->bg_color ) ) ) {
			throw new \Exception( 'Setting a background failed' );
		}

		return $this;
	}

	private function allocate_color( $color ) {
		$rgb = $this->convert_hex_rgb( $color );

		if ( ( $color = imagecolorallocate( $this->image, $rgb[ 0 ], $rgb[ 1 ], $rgb[ 2 ] ) ) === FALSE ) {
			throw new \Exception( 'Color allocating failed' );
		}

		return $color;
	}

	private function create_image( $destination, $file_name ) {
		if ( ! imagepng( $this->image, $destination . $file_name ) ) {
			throw new \Exception( 'Creating the image file failed' );
		}

		return $this;
	}

	private function destroy_image() {
		if ( ! imagedestroy( $this->image ) ) {
			throw new \Exception( 'Destroying the image failed' );
		}

		return $this;
	}

	private function get_font_size( $divide = 1 ) {
		if ( $this->size[0] > $this->size[1] ) $divide = 2;
		
		return ( $this->size[0] / strlen( $this->text ) ) / $divide;
	}

	private function convert_hex_rgb( $hex ) {
		return sscanf( $hex, '#%02x%02x%02x' );
	}
}