<?php

namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Encrypt/Decrypt strings for database storage
 */
class Encryption {

	/**
	 * Key to use for encrypting/decrypting
	 *
	 * @since 1.0
	 * @var string
	 */
	private $key;

	/**
	 * Salt to use prior to encryption
	 *
	 * @since 1.0
	 * @var string
	 */
	private $salt;

	/**
	 * Method to be used for encrypting
	 *
	 * @since 1.0
	 * @var string
	 */
	private $method;

	/**
	 * The initialization vector length based on the method
	 *
	 * @since 1.0
	 * @var string
	 */
	private $ivlength;

	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->key      = $this->get_key();
		$this->salt     = $this->get_salt();
		$this->method   = 'aes-256-ctr';
		$this->ivlength = openssl_cipher_iv_length( $this->method );
	}

	/**
	 * Returns the encryption key to use
	 *
	 * @since 1.0
	 *
	 * @return string The encryption key
	 */
	private function get_key() {
		if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		// If a site does not have a LOGGED_IN_KEY set, then they already have more
		// pressing security issues than exposure of the data connection token.
		// We'll set a basic fallback just in case.
		return 'DB6F4B11FF31F37F3C53D6FDD13A12F06D2802DA';
	}

	/**
	 * Returns the salt to use before encryption
	 *
	 * @since 1.0
	 *
	 * @return string The salt
	 */
	private function get_salt() {
		if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		// If a site does not have a LOGGED_IN_SALT set, then they already have more
		// pressing security issues than exposure of the data connection token.
		// We'll set a basic fallback just in case.
		return 'EE3440AA014D3E0627127D844EFAE7946B51BFBB';
	}


	/**
	 * Encrypts a value
	 *
	 * @since 1.0
	 *
	 * @param string $value The string to be encrypted
	 *
	 * @return string|False The encrypted value, or false on failure
	 */
	public function encrypt( $value ) {

		// If we don't have openssl for some reason, we'll just bail and return the value
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$salty_value = $value . $this->salt;
		$iv          = openssl_random_pseudo_bytes( $this->ivlength );

		$cipher = openssl_encrypt( $salty_value, $this->method, $this->key, 0, $iv );

		// If encryption failed
		if ( ! $cipher ) {
			return false;
		}

		return base64_encode( $iv . $cipher );
	}

	/**
	 * Decrypts a value
	 *
	 * @since 1.0
	 *
	 * @param string $cipher The value to be decrypted
	 *
	 * @return string|False The decrypted value or false on failure
	 */
	public function decrypt( $cipher ) {

		// If we don't have openssl for some reason, we'll just bail and return the value
		if ( ! extension_loaded( 'openssl' ) ) {
			return $cipher;
		}

		$cipher = base64_decode( $cipher, true );

		// Grab the IV from the front of the passed encrypted string
		$iv = substr( $cipher, 0, $this->ivlength );

		// Get the encrypted value from the second half
		$value = substr( $cipher, $this->ivlength );

		// Decrypt!
		$decrypted_value = openssl_decrypt( $value, $this->method, $this->key, 0, $iv );

		// Decription failed, or the salt doesn't match the end of the decrypted string
		if ( ! $value || substr( $decrypted_value, - strlen( $this->salt ) ) !== $this->salt ) {
			return false;
		}

		// Remove the salt from the end and return the decypted value
		return substr( $decrypted_value, 0, - strlen( $this->salt ) );
	}
}
