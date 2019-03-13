<?php
namespace ElementorPro\Modules\Forms\Fields;

use Elementor\Widget_Base;
use ElementorPro\Modules\Forms\Classes;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Widgets\Form;
use ElementorPro\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Upload extends Field_Base {

	private $has_errors = false;
	private $fixed_files_indices = false;

	public function get_type() {
		return 'upload';
	}

	public function get_name() {
		return __( 'File Upload', 'elementor-pro' );
	}

	/**
	 * @param Widget_Base $widget
	 */
	public function update_controls( $widget ) {
		$elementor = Plugin::elementor();

		$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		$file_size = [
			'name' => 'file_sizes',
			'label' => __( 'Max File Size', 'elementor-pro' ),
			'type' => Controls_Manager::SELECT,
			'condition' => [
				'field_type' => $this->get_type(),
			],
			'options' => $this->get_upload_file_size_options(),
			'description' => __( 'If you need to increase max upload size please contact your hosting', 'elementor-pro' ),
			'tab' => 'content',
			'inner_tab' => 'form_fields_content_tab',
			'tabs_wrapper' => 'form_fields_tabs',
		];

		$file_types = [
			'name' => 'file_types',
			'label' => __( 'Allowed File Types', 'elementor-pro' ),
			'type' => Controls_Manager::TEXT,
			'condition' => [
				'field_type' => $this->get_type(),
			],
			'description' => __( 'Enter the allowed file types, separated by a comma (jpg, gif, pdf, etc).', 'elementor-pro' ),
			'tab' => 'content',
			'inner_tab' => 'form_fields_content_tab',
			'tabs_wrapper' => 'form_fields_tabs',
		];

		$multiple_files = [
			'name' => 'allow_multiple_upload',
			'label' => __( 'Multiple Files', 'elementor-pro' ),
			'type' => Controls_Manager::SWITCHER,
			'condition' => [
				'field_type' => $this->get_type(),
			],
			'tab' => 'content',
			'inner_tab' => 'form_fields_content_tab',
			'tabs_wrapper' => 'form_fields_tabs',
		];

		$multiple_files_max = [
			'name' => 'max_files',
			'label' => __( 'Max Files', 'elementor-pro' ),
			'type' => Controls_Manager::NUMBER,
			'condition' => [
				'field_type' => $this->get_type(),
				'allow_multiple_upload' => 'yes',
			],
			'tab' => 'content',
			'inner_tab' => 'form_fields_content_tab',
			'tabs_wrapper' => 'form_fields_tabs',
		];

		$new_order = [];
		foreach ( $control_data['fields'] as $index => $field ) {
			if ( 'required' === $field['name'] ) {
				$new_order[] = $field;
				$new_order[] = $file_size;
				$new_order[] = $file_types;
				$new_order[] = $multiple_files;
				$new_order[] = $multiple_files_max;
			} else {
				$new_order[] = $field;
			}
		}

		$control_data['fields'] = $new_order;
		unset( $new_order );
		$widget->update_control( 'form_fields', $control_data );
	}

	/**
	 * @param $item
	 * @param $item_index
	 * @param Form $form
	 */
	public function render( $item, $item_index, $form ) {
		$form->add_render_attribute( 'input' . $item_index, 'class', 'elementor-upload-field' );
		$form->add_render_attribute( 'input' . $item_index, 'type', 'file', true );

		if ( ! empty( $item['allow_multiple_upload'] ) ) {
			$form->add_render_attribute( 'input' . $item_index, 'multiple', 'multiple' );
			$form->add_render_attribute( 'input' . $item_index, 'name', $form->get_attribute_name( $item ) . '[]', true );
		}

		echo '<input ' . $form->get_render_attribute_string( 'input' . $item_index ) . '>';
	}

	/**
	 * Fix multiple files upload indices in global $_FILES array
	 */
	private function fix_file_indices() {
		if ( $this->fixed_files_indices ) {
			return;
		}
		// a mapping of $_FILES indices for validity checking
		$names = [
			'name',
			'type',
			'tmp_name',
			'error',
			'size',
		];
		$files = $_FILES['form_fields'];
		// iterate over each uploaded file
		foreach ( $files as $key => $part ) {
			$key = (string) $key;
			if ( in_array( $key, $names ) && is_array( $part ) ) {
				foreach ( $part as $position => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $index => $inner_val ) {
							$files[ $position ][ $index ][ $key ] = $inner_val;
						}
					} else {
						$files[ $position ][0][ $key ] = $value;
					}
				}
				// remove old key reference
				unset( $files[ $key ] );
			}
		}
		$_FILES['form_fields'] = $files;
		$this->fixed_files_indices = true;
	}

	/**
	 * validate uploaded file size against allowed file size
	 * @param array $field
	 * @param $file
	 *
	 * @return bool
	 */
	private function is_file_size_valid( $field, $file ) {
		$allowed_size = ( ! empty( $field['file_sizes'] ) ) ? $field['file_sizes'] : wp_max_upload_size() / pow( 1024, 2 );
		// File size validation
		$file_size_meta = $allowed_size * pow( 1024, 2 );
		$upload_file_size = $file['size'];
		return ( $upload_file_size < $file_size_meta );
	}

	/**
	 * validates uploaded file type against allowed file types
	 *
	 * @param array $field
	 * @param $file
	 *
	 * @return bool
	 */
	private function is_file_type_valid( $field, $file ) {
		// File type validation
		if ( empty( $field['file_types'] ) ) {
			$field['file_types'] = 'jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv';
		}

		$file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$file_types_meta = explode( ',', $field['file_types'] );
		$file_types_meta = array_map( 'trim', $file_types_meta );
		return ( in_array( $file_extension, $file_types_meta ) && ! in_array( $file_extension, $this->get_blacklist_file_ext() ) );
	}

	/**
	 * a set of black listed file extensions
	 *
	 * @return array
	 */
	private function get_blacklist_file_ext() {
		static $blacklist = false;
		if ( ! $blacklist ) {
			$blacklist = [ 'php', 'php3', 'php4', 'php5', 'php6', 'phps', 'php7', 'phtml', 'shtml', 'pht', 'swf', 'html', 'asp', 'aspx', 'cmd', 'csh', 'bat', 'htm', 'hta', 'jar', 'exe', 'com', 'js', 'lnk', 'htaccess', 'htpasswd', 'phtml', 'ps1', 'ps2', 'py', 'rb', 'tmp', 'cgi' ];
			$blacklist = apply_filters( 'elementor_pro/forms/filetypes/blacklist', $blacklist );
		}
		return $blacklist;
	}

	/**
	 * validate uploaded file field
	 * @param array $field
	 * @param Classes\Form_Record $record
	 * @param Classes\Ajax_Handler $ajax_handler
	 */
	public function validation( $field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		static $upload_errors = false;
		if ( ! $upload_errors ) {
			$upload_errors = [
				UPLOAD_ERR_OK => __( 'There is no error, the file uploaded with success.', 'elementor-pro' ),
				UPLOAD_ERR_INI_SIZE => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'elementor-pro' ),
				UPLOAD_ERR_FORM_SIZE => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'elementor-pro' ),
				UPLOAD_ERR_PARTIAL => __( 'The uploaded file was only partially uploaded.', 'elementor-pro' ),
				UPLOAD_ERR_NO_FILE => __( 'No file was uploaded.', 'elementor-pro' ),
				UPLOAD_ERR_NO_TMP_DIR => __( 'Missing a temporary folder.', 'elementor-pro' ),
				UPLOAD_ERR_CANT_WRITE => __( 'Failed to write file to disk.', 'elementor-pro' ),
				UPLOAD_ERR_EXTENSION => __( 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.', 'elementor-pro' ),
			];
			$this->fix_file_indices();
		}

		$id = $field['id'];


		if ( ! empty( $field['max_files'] ) ) {
			if ( count( $_FILES['form_fields'][ $id ] ) > $field['max_files'] ) {
				// translators: %d is the number of allowed files
				$error_message = sprintf( __( 'You can only upload up to %d files', 'elementor-pro' ), intval( $field['max_files'] ) );
				$ajax_handler->add_error( $id, $error_message );
				return;
			}
		}
		foreach ( $_FILES['form_fields'][ $id ] as $index => $file ) {
			// not uploaded
			if ( ! $field['required'] && UPLOAD_ERR_NO_FILE === $file['error'] ) {
				return;
			}

			// is the file required and missing?
			if ( $field['required'] && UPLOAD_ERR_NO_FILE === $file['error'] ) {
				$ajax_handler->add_error( $id, $upload_errors[ $file['error'] ] );
				return;
			}

			// Has any error with upload the file?
			if ( $file['error'] > UPLOAD_ERR_OK ) {
				$ajax_handler->add_error( $id, $upload_errors[ $file['error'] ] );
				return;
			}

			// valid file type?
			if ( ! $this->is_file_type_valid( $field, $file ) ) {
				$ajax_handler->add_error( $id, __( 'This file type is not allowed.', 'elementor-pro' ) );
			}

			// allowed file size?
			if ( ! $this->is_file_size_valid( $field, $file ) ) {
				$ajax_handler->add_error( $id, __( 'This file exceeds the maximum allowed size.', 'elementor-pro' ) );
			}
		}
	}

	/**
	 * Gets path to upload form attachments
	 * @return string
	 */
	private function get_upload_dir() {
		$wp_upload_dir = wp_upload_dir();
		$path = $wp_upload_dir['basedir'] . '/elementor/forms';
		return apply_filters( 'elementor_pro/forms/upload_path', $path );
	}

	/**
	 *  Gets url to uploaded file
	 * @param $file_name
	 *
	 * @return string
	 */
	private function get_file_url( $file_name ) {
		$wp_upload_dir = wp_upload_dir();
		$url = $wp_upload_dir['baseurl'] . '/elementor/forms/' . $file_name;
		return apply_filters( 'elementor_pro/forms/upload_url', $url, $file_name );
	}

	/**
	 * This function returns the uploads folder after making sure
	 * it is created and has protection files
	 * @return string
	 */
	private function get_ensure_upload_dir() {
		$path = $this->get_upload_dir();
		if ( file_exists( $path . '/index.php' ) ) {
			return $path;
		}

		wp_mkdir_p( $path );

		//'base' => $upload_dir['basedir'] . '/elementor/forms',
		$files = array(
			array(
				'file' => 'index.php',
				'content' => '<?php' . PHP_EOL . '// Silence is golden.',
			),
			array(
				'file' => '.htaccess',
				'content' => 'Options -Indexes' . PHP_EOL . '<Files *.*>' . PHP_EOL . 'Header set Content-Disposition attachment' . PHP_EOL . '</Files>',
			),
		);

		foreach ( $files as $file ) {
			if ( ! file_exists( trailingslashit( $path ) . $file['file'] ) ) {
					@ file_put_contents( trailingslashit( $path ) . $file['file'], $file['content'] );
			}
		}
		return $path;
	}

	/**
	 * creates array of upload sizes based on server limits
	 * to use in the file_sizes control
	 * @return array
	 */
	private function get_upload_file_size_options() {
		$max_file_size = wp_max_upload_size() / pow( 1024, 2 ); //MB

		$sizes = array();
		for ( $file_size = 1; $file_size <= $max_file_size; $file_size++ ) {
			$sizes[ $file_size ] = $file_size . 'MB';
		}
		return $sizes;
	}

	/**
	 * process file and move it to uploads directory
	 * @param array $field
	 * @param Classes\Form_Record $record
	 * @param Classes\Ajax_Handler $ajax_handler
	 */
	public function process_field( $field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		$id = $field['id'];
		foreach ( $_FILES['form_fields'][ $id ] as $index => $file ) {
			if ( UPLOAD_ERR_NO_FILE === $file['error'] ) {
				continue;
			}
			$uploads_dir    = $this->get_ensure_upload_dir();
			$file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$filename       = uniqid() . '.' . $file_extension;
			$filename       = wp_unique_filename( $uploads_dir, $filename );
			$new_file       = trailingslashit( $uploads_dir ) . $filename;
			if ( is_dir( $uploads_dir ) && is_writable( $uploads_dir ) ) {
				$move_new_file = @ move_uploaded_file( $file['tmp_name'], $new_file );
				if ( false !== $move_new_file ) {
					// Set correct file permissions.
					$perms = 0644;
					@ chmod( $new_file, $perms );
					$record->add_file( $id, $index,
						[
							'path' => $new_file,
							'url'  => $this->get_file_url( $filename ),
						]
					);
				} else {
					$ajax_handler->add_error( $id, __( 'There was an error while trying to upload your file.', 'elementor-pro' ) );
				}
			} else {
				$ajax_handler->add_admin_error_message( __( 'Upload directory is not writable or does not exist.', 'elementor-pro' ) );
			}
		}
	}

	/**
	 * Used to set the upload filed values with
	 * value => file url
	 * raw_value => file path
	 * @param Classes\Form_Record $record
	 * @param Classes\Ajax_Handler $ajax_handler
	 */
	public function set_file_fields_values( Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		$files = $record->get( 'files' );
		if ( empty( $files ) ) {
			return;
		}
		foreach ( $files as $id => $files_array ) {
			$record->update_field( $id, 'value', implode( ' , ', $files_array['url'] ) );
			$record->update_field( $id, 'raw_value', implode( ' , ', $files_array['path'] ) );
		}
	}

	public function __construct() {
		parent::__construct();
		add_action( 'elementor_pro/forms/process', [ $this, 'set_file_fields_values' ], 10, 2 );
	}
}