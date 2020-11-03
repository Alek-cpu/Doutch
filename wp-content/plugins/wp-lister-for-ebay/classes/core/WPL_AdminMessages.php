<?php

class WPLE_AdminMessages {

	private $messages       = array();
	private $flash_messages = array();

    function __construct() {
        add_action( 'admin_notices', array( &$this, 'show_admin_notices' ), 10 );
        add_action( 'wple_admin_notices', array( &$this, 'show_admin_notices' ), 10 );
        // add_action( 'admin_footer', array( &$this, 'show_admin_notices' ), 10 );
        $this->flash_messages = array();
        $this->messages       = get_transient( 'wple_admin_messages' );

        if ( !$this->messages ) {
            $this->messages = array();
        }
    }

    function add_message( $message, $type = 'info', $persistent = false ) {

        // convert old error codes
        if ( $type === 0 ) $type = 'info';
        if ( $type === 1 ) $type = 'error';
        if ( $type === 2 ) $type = 'warn';

        $msg = new stdClass();
        $msg->type    = $type;
        $msg->message = $message;

        if ( ! $persistent ) {
            $this->flash_messages[] = $msg;
        } else {
            $this->messages[] = $msg;
            set_transient( 'wple_admin_messages', $this->messages );
        }

    } // show_admin_notices()


    function show_admin_notices() {
        // Don't show ouput when request is done via AJAX or REST
        if ( wple_request_is_ajax() || wple_request_is_rest() ) {
            return;
        }

        // dont output any messages when on SagePay endpoints #13032
        if ( isset( $_POST['cwcontroller'] ) ) {
            return;
        }

        // Start with flash messages
        foreach ( $this->flash_messages as $msg ) {
            $this->show_single_message( $msg->message, $msg->type );
        }

        // Display persistent messages
        foreach ( $this->messages as $msg ) {
            $this->show_single_message( $msg->message, $msg->type );
        }

        // clear messages after display
        $this->messages = array();
        set_transient( 'wple_admin_messages', $this->messages );

    } // show_admin_notices()


    // display a single admin notice - the WordPress way
    function show_single_message( $message, $msg_type = 'info' ) {

        switch ( $msg_type ) {
            case 'error':
                $class = 'error';
                break;
            
            case 'warn':
                $class = 'update-nag';
                break;
            
            default:
                $class = 'updated';
                break;
        }

        $message = apply_filters( 'wplister_admin_message_text', $message );
        echo '<div id="message" class="'.$class.'" style="display:block !important"><p>'.$message.'</p></div>';

    } // show_single_message()




    // create JSON compatible array to display in progress window
    function get_admin_notices_for_json_result() {
        $errors = array();

        foreach ( $this->messages as $msg ) {
            $errors[] = $this->get_single_message_as_json_error( $msg->message, $msg->type );
        }

        return $errors;
    } // get_admin_notices_for_json_result()

    // get a single admin notice - for progress window
    function get_single_message_as_json_error( $message, $msg_type = 'info' ) {

        switch ( $msg_type ) {
            case 'error':
                $class = 'error';
                $SeverityCode = 'Error';
                break;
            
            case 'warn':
                $class = 'updated update-nag';
                $SeverityCode = 'Warning';
                break;
            
            default:
                $class = 'updated';
                $SeverityCode = 'Note';
                break;
        }

        $message = apply_filters( 'wplister_admin_message_text', $message );
        $html_message = '<div id="message" class="'.$class.'" style="display:block !important"><p>'.$message.'</p></div>';

        // build error object
        $error = new stdClass();
        $error->SeverityCode = $SeverityCode;
        $error->ErrorCode    = 42;
        $error->ShortMessage = 'Your attention is required.';
        $error->LongMessage  = $message;
        $error->HtmlMessage  = $html_message;

        return $error;
    } // get_single_message_as_json_error()





} // class WPLE_AdminMessages
