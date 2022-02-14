<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that crypt data
 * $string variable return original data
 * $action variable return crypt type
 * $secret variable return secret data
 */
if ( ! function_exists( 'cf7_pardot_crypt' ) ) {
    function cf7_pardot_crypt( $string, $action = 'e', $secret ) {
        
        if ( extension_loaded( 'openssl' ) ) {
            $secret_key = $secret.'cf7_pardot_key';
            $secret_iv = $secret.'cf7_pardot_iv';

            $output = false;
            $encrypt_method = 'AES-256-CBC';
            $key = hash( 'sha256', $secret_key );
            $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

            if( $action == 'e' ) {
                $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
            }
            else if( $action == 'd' ){
                $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
            }

            return $output;
        } else {
            return $string;
        }
    }
}

/*
 * This is a function that integrate form.
 * $cf7 variable return current form data.
 */
if ( ! function_exists( 'cf7_pardot_integration' ) ) {
    add_action( 'wpcf7_before_send_mail', 'cf7_pardot_integration', 20, 1 );
    function cf7_pardot_integration( $cf7 ) {
        
        $submission = WPCF7_Submission::get_instance();
        if ( $submission ) {
          $request = $submission->get_posted_data();     
        }
        
        $form_id = 0;
        if ( isset( $request['_wpcf7'] ) ) {
            $form_id = intval( $request['_wpcf7'] );
        } else if ( isset( $_POST['_wpcf7'] ) ) {
            $form_id = intval( $_POST['_wpcf7'] );
        } else {
            //
        }
        
        if ( $form_id ) {
            $cf7_pardot = get_post_meta( $form_id, 'cf7_pardot', true );
            if ( $cf7_pardot ) {
                $cf7_pardot_fields = get_post_meta( $form_id, 'cf7_pardot_fields', true );
                if ( $cf7_pardot_fields != null ) {
                    $data = array();
                    foreach ( $cf7_pardot_fields as $cf7_pardot_field_key => $cf7_pardot_field ) {
                        if ( isset( $cf7_pardot_field['key'] ) && $cf7_pardot_field['key'] ) {
                            if ( is_array( $request[$cf7_pardot_field_key] ) ) {
                                $request[$cf7_pardot_field_key] = implode( ';', $request[$cf7_pardot_field_key] );
                            }
                            
                            $data[$cf7_pardot_field['key']] = strip_tags( $request[$cf7_pardot_field_key] );
                        }
                    }
                    
                    if ( $data != null ) {
                        $cf7_pardot_list = get_post_meta( $form_id, 'cf7_pardot_list', true );
                        if ( $cf7_pardot_list != null ) {
                            $data['cf7_pardot_list'] = $cf7_pardot_list;
                        }
                        
                        $client_id = get_option( 'cf7_pardot_client_id' );
                        $client_secret = get_option( 'cf7_pardot_client_secret' );
                        if ( $client_id ) {
                            $domain = get_option( 'cf7_pardot_domain' );
                            $pardot = new CF7_Salesforce_Pardot_API( $domain, $client_id, $client_secret );
                            $token = get_option( 'cf7_pardot' );
                            $pardot->getRefreshToken( $token );
                            $token = get_option( 'cf7_pardot' );
                            $business_unit_id = get_option( 'cf7_pardot_business_unit_id' );
                            if ( isset( $data['email'] ) && $data['email'] ) {
                                $prospect_email = $data['email'];
                                unset( $data['email'] );
                                
                                $action = get_option( 'cf7_pardot_action_'.$form_id );
                                if ( ! $action ) {
                                    $action = 'create';
                                }
                                
                                if ( $action == 'create' ) {
                                    $pardot->addProspect( $token, $business_unit_id, $data, $prospect_email, $form_id );
                                } else if ( $action == 'create_or_update' ) {
                                    $prospects = $pardot->getProspects( $token, $business_unit_id, $prospect_email );
                                    if ( $prospects != null ) {
                                        foreach ( $prospects as $prospect ) {
                                            $pardot->updateProspect( $token, $business_unit_id, $data, $prospect, $form_id );
                                        }
                                    } else {
                                        $pardot->addProspect( $token, $business_unit_id, $data, $prospect_email, $form_id );
                                    }
                                }
                            }
                        } else {
                            $user_key = get_option( 'cf7_pardot_user_key' );
                            $email = get_option( 'cf7_pardot_email' );
                            $password = cf7_pardot_crypt( get_option( 'cf7_pardot_password' ), 'd', $user_key );
                            $pardot = new CF7_Pardot_API( $email, $password, $user_key );
                            $api_key = $pardot->authentication();
                            if ( $api_key && isset( $data['email'] ) && $data['email'] ) {
                                $prospect_email = $data['email'];
                                unset( $data['email'] );
                                
                                $action = get_option( 'cf7_pardot_action_'.$form_id );
                                if ( ! $action ) {
                                    $action = 'create';
                                }
                                
                                if ( $action == 'create' ) {
                                    $pardot->addProspect( $api_key, $data, $prospect_email, $form_id );
                                } else if ( $action == 'create_or_update' ) {
                                    $prospects = $pardot->getProspects( $api_key, $prospect_email );
                                    if ( $prospects != null ) {
                                        foreach ( $prospects as $prospect ) {
                                            $pardot->updateProspect( $api_key, $data, $prospect, $form_id );
                                        }
                                    } else {
                                        $pardot->addProspect( $api_key, $data, $prospect_email, $form_id );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}