<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a class for Pardot API.
 */
if ( ! class_exists( 'CF7_Pardot_API' ) ) {
    class CF7_Pardot_API {
        
        var $email;
        var $password;
        var $user_key;
        
        function __construct( $email, $password, $user_key ) {
            
            $this->email = $email;
            $this->password = $password;
            $this->user_key = $user_key;
        }
        
        function authentication() {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            $data = array(
                'email'     => $this->email,
                'password'  => $this->password,
                'user_key'  => $this->user_key,
            );
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/login/version/'.$version;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
            $api_key = 0;
            if ( isset( $response->api_key ) ) {
                $array_response = (array) $response;
                $api_key = $array_response['api_key'];                
            }
           
            if ( isset( $response->err ) ) {
                $log = "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $api_key;
        }
        
        function addProspect( $api_key, $data, $email, $form_id ) {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            if ( isset( $data['cf7_pardot_list'] ) ) {
                foreach ( $data['cf7_pardot_list'] as $list ) {
                    $data['list_'.$list] = 1;
                }
                
                unset( $data['cf7_pardot_list'] );
            }
            
            $data['api_key'] = $api_key;
            $data['user_key'] = $this->user_key;
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/prospect/version/'.$version.'/do/create/email/'.$email;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
           
            if ( isset( $response->err ) ) {
                $log = "Form ID: ".$form_id."\n";
                $log .= "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Form ID: ".$form_id."<br>";
                    $body .= "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getProspectCustomFields( $api_key, $offset ) {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            $data = array(
                'api_key'   => $api_key,
                'user_key'  => $this->user_key,
            );
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/customField/version/'.$version.'/do/query?limit=200&offset='.$offset;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
           
            if ( isset( $response->err ) ) {
                $log = "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            $fields = array();
            if ( isset( $response->result ) ) {
                $result = (array) $response->result;
                if ( isset( $result['customField'] ) && $result['customField'] != null ) {
                    foreach ( $result['customField'] as $custom_field ) {
                        $field = (array) $custom_field;
                        $fields[$field['field_id']] = array(
                            'label'     => $field['name'],
                            'type'      => $field['type'],  
                            'required'  => 0,
                        );
                    }
                    
                    $fields['source'] = array(
                        'label'     => 'Source',
                        'type'      => 'Text',
                        'required'  => 0,
                    );
                    $fields['opted_out'] = array(
                        'label'     => 'Opted Out',
                        'type'      => 'Boolean',
                        'required'  => 0,
                    );
                }
            }
            
            return $fields;
        }
        
        function getList( $api_key, $offset ) {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            $data = array(
                'api_key'   => $api_key,
                'user_key'  => $this->user_key,
            );
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/list/version/'.$version.'/do/query?limit=200&offset='.$offset;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
           
            if ( isset( $response->err ) ) {
                $log = "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            $list = array();
            if ( isset( $response->result ) ) {
                $result = (array) $response->result;
                if ( isset( $result['list'] ) && $result['list'] != null ) {
                    foreach ( $result['list'] as $list_data ) {
                        $list_data = (array) $list_data;
                        $list[$list_data['id']] = $list_data['name'];
                    }
                }
            }
            
            return $list;
        }
        
        function getProspects( $api_key, $email ) {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            $data = array(
                'api_key'   => $api_key,
                'user_key'  => $this->user_key,
            );
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/prospect/version/'.$version.'/do/read/email/'.$email;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
            
            $prospects = array();
            if ( isset( $response->prospect ) && $response->prospect != null ) {
                $response = (array) $response;
                if ( $response['prospect'] != null ) {
                    if ( is_array( $response['prospect'] ) ) {
                        foreach ( $response['prospect'] as $prospect ) {
                            $prospect = (array) $prospect;
                            if ( isset( $prospect['id'] ) ) {
                                $prospects[] = $prospect['id'];
                            }
                        }
                    } else {
                        $response = (array) $response['prospect'];
                        $prospects[] = $response['id'];
                    }
                }
            }
            
            return $prospects;
        }
        
        function updateProspect( $api_key, $data, $id, $form_id ) {
            
            $version = get_option( 'cf7_pardot_version' );
            if ( ! $version ) {
                $version = 4;
            }
            
            if ( isset( $data['cf7_pardot_list'] ) ) {
                foreach ( $data['cf7_pardot_list'] as $list ) {
                    $data['list_'.$list] = 1;
                }
                
                unset( $data['cf7_pardot_list'] );
            }
            
            $data['api_key'] = $api_key;
            $data['user_key'] = $this->user_key;
            $post_fields = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/prospect/version/'.$version.'/do/update/id/'.$id;
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            $json_response = curl_exec( $ch );
            curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $response = simplexml_load_string( $json_response );
            
            if ( isset( $response->err ) ) {
                $log = "Form ID: ".$form_id."\n";
                $log .= "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Form ID: ".$form_id."<br>";
                    $body .= "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
    }
}

if ( ! class_exists( 'CF7_Salesforce_Pardot_API' ) ) {
    class CF7_Salesforce_Pardot_API {

        var $url;
        var $client_id;
        var $client_secret;
        
        function __construct( $url, $client_id, $client_secret) {
            
            $url = rtrim( $url, '/' );
            $this->url = $url;
            $this->client_id = $client_id;
            $this->client_secret = $client_secret;
        }

        function getToken( $code, $redirect_uri ) {
            
            $data = array(
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $redirect_uri,
            );
            $url = $this->url.'/services/oauth2/token';
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'body'          => $data,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_post( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
            
            return $response;
        }

        function getRefreshToken( $token ) {
            
            if ( isset( $token->refresh_token ) ) {
                $data = array(
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $token->refresh_token,
                );
                $url = $this->url.'/services/oauth2/token';
                $args = array(
                    'timeout'       => 30,
                    'httpversion'   => '1.0',
                    'body'          => $data,
                    'sslverify'     => false,
                );
                $wp_remote_response = wp_remote_post( $url, $args );
                $json_response = '';
                if ( ! is_wp_error( $wp_remote_response ) ) {
                    $json_response = $wp_remote_response['body'];
                }
                
                $response = json_decode( $json_response );
                if ( isset( $response->access_token ) ) {
                    $token->access_token = $response->access_token;
                    update_option( 'cf7_pardot', $token );
                }
                
                return $response;
            }
        }

        function getProspectCustomFields( $token, $business_unit_id, $offset ) {
            
            $url = 'https://pi.pardot.com/api/customField/version/4/do/query?format=json&limit=200&offset='.$offset;
            $header = array(
                'Authorization'             => $token->token_type.' '.$token->access_token,
                'Pardot-Business-Unit-Id'   => $business_unit_id,
                'Content-Type'              => 'application/x-www-form-urlencoded',
            );
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'headers'       => $header,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_get( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
            
            if ( isset( $response->err ) ) {
                $log = "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            $fields = array();
            if ( isset( $response->result ) ) {
                $result = $response->result;
                if ( isset( $result->customField ) && $result->customField != null ) {
                    foreach ( $result->customField as $field ) {
                        $fields[$field->field_id] = array(
                            'label'     => $field->name,
                            'type'      => $field->type,  
                            'required'  => 0,
                        );
                    }
                    
                    $fields['source'] = array(
                        'label'     => 'Source',
                        'type'      => 'Text',
                        'required'  => 0,
                    );
                    $fields['opted_out'] = array(
                        'label'     => 'Opted Out',
                        'type'      => 'Boolean',
                        'required'  => 0,
                    );
                }
            }
            
            return $fields;
        }
        
        function getList( $token, $business_unit_id, $offset ) {
            
            $url = 'https://pi.pardot.com/api/list/version/4/do/query?format=json&limit=200&offset='.$offset;
            $header = array(
                'Authorization'             => $token->token_type.' '.$token->access_token,
                'Pardot-Business-Unit-Id'   => $business_unit_id,
                'Content-Type'              => 'application/x-www-form-urlencoded',
            );
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'headers'       => $header,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_get( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
           
            if ( isset( $response->err ) ) {
                $log = "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            $list = array();
            if ( isset( $response->result ) ) {
                $result = $response->result;
                if ( isset( $result->list ) && $result->list != null ) {
                    foreach ( $result->list as $list_data ) {
                        $list[$list_data->id] = $list_data->name;
                    }
                }
            }
            
            return $list;
        }

        function addProspect( $token, $business_unit_id, $data, $email, $form_id ) {
            
            if ( isset( $data['cf7_pardot_list'] ) ) {
                foreach ( $data['cf7_pardot_list'] as $list ) {
                    $data['list_'.$list] = 1;
                }
                
                unset( $data['cf7_pardot_list'] );
            }
            
            $data = http_build_query( $data );
            
            $url = 'https://pi.pardot.com/api/prospect/version/4/do/create/email/'.$email.'?format=json';
            $header = array(
                'Authorization'             => $token->token_type.' '.$token->access_token,
                'Pardot-Business-Unit-Id'   => $business_unit_id,
                'Content-Type'              => 'application/x-www-form-urlencoded',
            );
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'headers'       => $header,
                'body'          => $data,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_post( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
            
            if ( isset( $response->err ) ) {
                $log = "Form ID: ".$form_id."\n";
                $log .= "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Form ID: ".$form_id."<br>";
                    $body .= "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }

        function getProspects( $token, $business_unit_id, $email ) {
            
            $url = 'https://pi.pardot.com/api/prospect/version/4/do/read/email/'.$email.'?format=json';
            $header = array(
                'Authorization'             => $token->token_type.' '.$token->access_token,
                'Pardot-Business-Unit-Id'   => $business_unit_id,
                'Content-Type'              => 'application/x-www-form-urlencoded',
            );
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'headers'       => $header,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_get( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
            
            $prospects = array();
            if ( isset( $response->prospect ) && $response->prospect != null ) {
                if ( is_array( $response->prospect ) ) {
                    foreach ( $response->prospect as $prospect ) {
                        if ( isset( $prospect->id ) ) {
                            $prospects[] = $prospect->id;
                        }
                    }
                } else {
                    $prospects[] = $response->prospect->id;
                }
            }
            
            return $prospects;
        }
        
        function updateProspect( $token, $business_unit_id, $data, $id, $form_id ) {
            
            if ( isset( $data['cf7_pardot_list'] ) ) {
                foreach ( $data['cf7_pardot_list'] as $list ) {
                    $data['list_'.$list] = 1;
                }
                
                unset( $data['cf7_pardot_list'] );
            }
            
            $data = http_build_query( $data );

            $url = 'https://pi.pardot.com/api/prospect/version/4/do/update/id/'.$id.'?format=json';
            $header = array(
                'Authorization'             => $token->token_type.' '.$token->access_token,
                'Pardot-Business-Unit-Id'   => $business_unit_id,
                'Content-Type'              => 'application/x-www-form-urlencoded',
            );
            $args = array(
                'timeout'       => 30,
                'httpversion'   => '1.0',
                'headers'       => $header,
                'body'          => $data,
                'sslverify'     => false,
            );
            $wp_remote_response = wp_remote_post( $url, $args );
            $json_response = '';
            if ( ! is_wp_error( $wp_remote_response ) ) {
                $json_response = $wp_remote_response['body'];
            }
            
            $response = json_decode( $json_response );
            
            if ( isset( $response->err ) ) {
                $log = "Form ID: ".$form_id."\n";
                $log .= "Error: ".$response->err."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";
                
                $send_to = get_option( 'cf7_pardot_notification_send_to' );
                if ( $send_to ) {
                    $to = $send_to;
                    $subject = get_option( 'cf7_pardot_notification_subject' );
                    $body = "Form ID: ".$form_id."<br>";
                    $body .= "Error: ".$response->err."<br>";
                    $body .= "Date: ".date( 'Y-m-d H:i:s' );
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );
                    wp_mail( $to, $subject, $body, $headers );
                }
                
                file_put_contents( CF7_PARDOT_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
    }
}