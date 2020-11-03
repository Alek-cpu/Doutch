<?php

class JobsModel extends WPL_Model {

	public $key = '';
	public $item = '';

	public function __construct( $job = null ) {
		parent::__construct();
		
		global $wpdb;
		$this->tablename = $wpdb->prefix . 'ebay_jobs';

		// return nothing if params are empty
		if ( ! $job ) return;

		// handle string as job_key
		if ( is_string( $job ) ) {
			$this->item = $this->getItemByKey( $job );
			return $this;
		}

		// handle object as new job object
		if ( is_object( $job ) ) {
			return $this->insertJob( $job->jobname, $job->tasklist );
		}

	}
	

	function getAll() {
		global $wpdb;	
		$jobs = $wpdb->get_results("
			SELECT * 
			FROM $this->tablename
		", ARRAY_A);		

		foreach( $jobs as &$job ) {
			$job['tasklist'] = self::decodeObject( $job['tasklist'] );
		}

		return $jobs;		
	}


	function getItem( $id ) {
		global $wpdb;	
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT * 
			FROM $this->tablename
			WHERE job_id = %s
		", $id 
		), ARRAY_A);		

		$item['tasklist'] = self::decodeObject( $item['tasklist'], true );

		return $item;		
	}

	function getItemByKey( $key ) {
		global $wpdb;	
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT * 
			FROM $this->tablename
			WHERE job_key = %s
		", $key 
		), ARRAY_A);		

		$item['tasklist'] = self::decodeObject( $item['tasklist'], true );

		$this->key = $key;
		return $item;		
	}


	function deleteItem( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare("
			DELETE
			FROM $this->tablename
			WHERE job_id = %s
		", $id ) );
	}


	function insertJob( $jobname, $tasklist )
	{
		global $wpdb;

		// get current user id
		$user = wp_get_current_user();

		// generate job key
		$key = md5( $jobname . rand() );
		
		// insert row into db
		$data = array();
		$data['job_key'] = $key;
		$data['job_name'] = $jobname;
		$data['tasklist'] = self::encodeObject($tasklist);
		$data['date_created'] = gmdate( 'Y-m-d H:i:s' );
		$data['user_id'] = $user->ID;

		$wpdb->insert($this->tablename, $data);

		WPLE()->logger->info("insertJob( $jobname ) - key $key" );
		$this->key = $key;					
		return $key;
	}

	function updateJob($id, $data) {
		global $wpdb;	
		$result = $wpdb->update( $this->tablename, $data, array( 'job_id' => $id ) );

		return $result;		

	}

	function completeJob() {
		global $wpdb;	

		$data = array();
		$data['success'] = 'complete';
		$data['date_finished'] = gmdate( 'Y-m-d H:i:s' );
		$result = $wpdb->update( $this->tablename, $data, array( 'job_key' => $this->key ) );

		// update item data
		$this->item = $this->getItemByKey( $this->key );

		return $result;		

	}


} // class JobsModel
