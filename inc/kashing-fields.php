<?php

class Kashing_Fields {


		private $form_fields = array(
			array(
				'name' => 'firstname',
				'required' => true
			),
			array(
				'name' => 'lastname'
			),
			array(
				'name' => 'address1',
				'required' => true
			),
			array(
				'name' => 'address2'
			),
			array(
				'name' => 'city',
				'required' => true
			),
			array(
				'name' => 'postcode',
				'required' => true
			),
			array(
				'name' => 'phone'
			),
			array(
				'name' => 'email',
				'type' => 'email'
			)
			);


		public function __construct() {

		}


		public function get_all_fields() {

			return $this->form_fields;

		}

		//$kashing_fields_validate

}