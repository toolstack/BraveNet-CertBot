<?php
use Goutte\Client;

class BraveNet {
	private $client = false;
	private $crawler = false;
	private $current_page = false;
	private $cert_list = null;
	
	function login( $username, $password ) {
		$this->client = new Client();

		$this->crawler = $this->client->request( 'GET', 'http://www.bravenet.com/global/login.php' );
		$form = $this->crawler->selectButton( 'Login' )->form();

		$this->crawler = $this->client->submit( $form, array( 'userid' => $username, 'password' => $password ) );
		
		$this->current_page = 'home';
	}
	
	function go_to_certs_page() {
		if( false === $this->crawler ) {
			return false;
		}
		
		$this->crawler = $this->client->click( $this->crawler->selectLink( 'Web Hosting' )->link() );
		$this->current_page = 'hosting';

		$this->crawler = $this->client->click( $this->crawler->selectLink( 'SSL Certificates' )->link() );
		$this->current_page = 'certs';
		
		return true;
	}
	
	function get_cert_list() {
		if( false === $this->crawler ) {
			return false;
		}
		
		$table = $this->crawler->filter( '#domainTable' )->filter( 'tr' )->each( function ( $tr, $i ) {
			return $tr->filter( 'td,th' )->each( function ( $td, $i ) {
				return trim( $td->text() );
			});
		});

		$this->cert_list = array();
		
		foreach( $table as $key => $value ) {
			if( intval( $value[1] ) > 0 ) {
				$this->cert_list[ $value[1] ] = $value[2];
			}
		}
		
		return $this->cert_list;
	}
	
	function delete_cert( $id ) {
		$this->crawler = $this->client->request( 'POST', 'https://manage.bravehost.com/https_certificate/delete?cert_id=' . $id );
	}
	
	function add_cert( $public_key, $private_key ) {
		$form = $this->crawler->selectButton( 'Add Certificate' )->form();

		$this->crawler = $this->client->submit( $form, array( 'formType' => 'textForm', 'cert' => $public_key, 'pkey' => $private_key ) );
	}
	
	function logout() {
	}
}