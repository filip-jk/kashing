<?php

class Kashing_Countries {

	private $countries;
	private $countries_data_path;

	/**
	 * Constructor class
	 */

	function __construct() {

		$this->countries_data_path = KASHING_PATH . 'inc/helpers/countries/countries-misc/';

		$this->init_countries();

	}

	/**
	 * Get country name by ISO 3166-1 Code
	 *
	 * @param string
	 *
	 * @return string
	 */

	function get_name( $code ) {

		if ( array_key_exists( $code, $this->countries ) ) {
			return $this->countries[ $code ];
		}

		return null; // country does not have a symbol

	}

	/**
	 * Get all countries.
	 *
	 * @return array
	 */

	function get_all() {
		return $this->countries;

	}

	/**
	 * Assign countries array to the countries variable.
	 *
	 * @return void
	 */

	function init_countries() {

		$file = $this->countries_data_path . 'countries-list.php'; // A full list of countries

		if ( is_file( $file ) ) {

			$countries_list_array = include $file;

			$new_countries_array = array();

			if ( is_array( $countries_list_array ) ) {

				foreach( $countries_list_array as $code => $name ) {

					$new_countries_array[ $code ] = $name;
				}

				$this->countries = $new_countries_array;

			}


		}

		return;

	}

}