<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that creates admin menu.
 */
if ( ! function_exists( 'cf7_pardot_main_menu' ) ) {
    add_action( 'admin_menu', 'cf7_pardot_main_menu' );
    function cf7_pardot_main_menu() {
        
        add_menu_page( esc_html__( 'Contact Form 7 - Pardot Integration', 'cf7_pardot' ), esc_html__( 'CF7 - Pardot', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_integration', 'cf7_pardot_integration_callback', 'dashicons-migrate' );
        add_submenu_page( 'cf7_pardot_integration', esc_html__( 'CF7 - Pardot: Integration', 'cf7_pardot' ), esc_html__( 'Integration', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_integration', 'cf7_pardot_integration_callback' );
        add_submenu_page( 'cf7_pardot_integration', esc_html__( 'CF7 - Pardot: Configuration', 'cf7_pardot' ), esc_html__( 'Configuration', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_configuration', 'cf7_pardot_configuration_callback' );
        add_submenu_page( 'cf7_pardot_integration', esc_html__( 'CF7 - Pardot: API Error Logs', 'cf7_pardot' ), esc_html__( 'API Error Logs', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_api_error_logs', 'cf7_pardot_api_error_logs_callback' );
        add_submenu_page( 'cf7_pardot_integration', esc_html__( 'CF7 - Pardot: Settings', 'cf7_pardot' ), esc_html__( 'Settings', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_settings', 'cf7_pardot_settings_callback' );
        add_submenu_page( 'cf7_pardot_integration', esc_html__( 'CF7 - Pardot: Licence Verification', 'cf7_pardot' ), esc_html__( 'Licence Verification', 'cf7_pardot' ), 'manage_options', 'cf7_pardot_licence_verification', 'cf7_pardot_licence_verification_callback' );
    }
}

/*
 * This is a function for configuration.
 */
if ( ! function_exists( 'cf7_pardot_configuration_callback' ) ) {
    function cf7_pardot_configuration_callback() {
        
        if ( isset( $_REQUEST['submit'] ) ) {
            $domain = sanitize_text_field( $_POST['cf7_pardot_domain'] );
            update_option( 'cf7_pardot_domain', $domain );
            
            $cf7_pardot_business_unit_id = sanitize_text_field( $_POST['cf7_pardot_business_unit_id'] );
            update_option( 'cf7_pardot_business_unit_id', $cf7_pardot_business_unit_id );
            
            $client_id = sanitize_text_field( $_POST['cf7_pardot_client_id'] );
            update_option( 'cf7_pardot_client_id', $client_id );
            
            $client_secret = sanitize_text_field( $_POST['cf7_pardot_client_secret'] );
            update_option( 'cf7_pardot_client_secret', $client_secret );

            $redirect_uri = menu_page_url( 'cf7_pardot_configuration', 0 );
            $domain = rtrim( $domain, '/' );
            $url = $domain."/services/oauth2/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&scope=pardot_api refresh_token";
            ?>
                <script type="text/javascript">
                    jQuery( document ).ready( function( $ ) {
                        window.location.replace( '<?php echo $url; ?>' );
                    });
                </script>
            <?php
        } else if ( isset( $_REQUEST['code'] ) ) {
            $domain = get_option( 'cf7_pardot_domain' );
            $client_id = get_option( 'cf7_pardot_client_id' );
            $client_secret = get_option( 'cf7_pardot_client_secret' );
            $code = $_REQUEST['code'];
            $redirect_uri = menu_page_url( 'cf7_pardot_configuration', 0 );
            $pardot = new CF7_Salesforce_Pardot_API( $domain, $client_id, $client_secret );
            $token = $pardot->getToken( $code, $redirect_uri );
            if ( isset( $token->error ) ) {
                ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong><?php esc_html_e( 'Error', 'cf7_pardot' ); ?></strong>: <?php echo $token->error; ?></p>
                    </div>
                <?php
            } else {
                update_option( 'cf7_pardot', $token );
                $redirect_uri = menu_page_url( 'cf7_pardot_integration', 0 );
                ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php esc_html_e( 'Configuration successful.', 'cf7_pardot' ); ?></p>
                    </div>
                    <script type="text/javascript">
                        jQuery( document ).ready( function( $ ) {
                            window.setTimeout(function(){
                                window.location.replace( '<?php echo $redirect_uri; ?>' );
                            }, 3000);
                        });
                    </script>
                <?php
            }
        } else {
            //
        }
        
        $licence = get_site_option( 'cf7_pardot_licence' );
        $domain = get_option( 'cf7_pardot_domain' );
        $cf7_pardot_business_unit_id = get_option( 'cf7_pardot_business_unit_id' );
        $client_id = get_option( 'cf7_pardot_client_id' );
        $client_secret = get_option( 'cf7_pardot_client_secret' );
        if ( ! $domain ) {
            $domain = 'https://login.salesforce.com/';
        }

        ?>
        <div class="wrap">                
            <h1><?php esc_html_e( 'Pardot Configuration', 'cf7_pardot' ); ?></h1>
            <hr>
            <?php
                if ( $licence ) {
                ?>
                <form method="post">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label><?php esc_html_e( 'Domain', 'cf7_pardot' ); ?> <span class="description">(required)</span></label></th>
                                <td>
                                    <input class="regular-text" type="text" name="cf7_pardot_domain" value="<?php echo $domain; ?>" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e( 'Business Unit ID', 'cf7_pardot' ); ?> <span class="description">(required)</span></label></th>
                                <td>
                                    <input class="regular-text" type="text" name="cf7_pardot_business_unit_id" value="<?php echo $cf7_pardot_business_unit_id; ?>" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e( 'Consumer Key', 'cf7_pardot' ); ?> <span class="description">(required)</span></label></th>
                                <td>
                                    <input class="regular-text" type="text" name="cf7_pardot_client_id" value="<?php echo $client_id; ?>" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e( 'Consumer Secret', 'cf7_pardot' ); ?> <span class="description">(required)</span></label></th>
                                <td>
                                    <input class="regular-text" type="text" name="cf7_pardot_client_secret" value="<?php echo $client_secret; ?>" required />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <input type='submit' class='button-primary' name="submit" value="<?php esc_html_e( 'Authorize', 'cf7_pardot' ); ?>" />
                    </p>
                </form>
                <br>
                <p><strong><?php esc_html_e( 'Callback URL', 'cf7_pardot' ); ?></strong>: <?php echo menu_page_url( 'cf7_pardot_configuration', 0 ); ?></p>
                <?php
                } else {
                    ?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php esc_html_e( 'Please verify purchase code.', 'cf7_pardot' ); ?></p>
                        </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }
}

/*
 * This is a function for integration.
 */
if ( ! function_exists( 'cf7_pardot_integration_callback' ) ) {
    function cf7_pardot_integration_callback() {
        
        ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Pardot Integration', 'cf7_pardot' ); ?></h1>
                <hr>
                <?php
                $licence = get_site_option( 'cf7_pardot_licence' );
                if ( $licence ) {
                    if ( isset( $_REQUEST['id'] ) ) {
                        $id = intval( $_REQUEST['id'] );
                        $form_id = $id;
                        if ( isset( $_REQUEST['submit'] ) ) {
                            update_post_meta( $id, 'cf7_pardot', $_REQUEST['cf7_pardot'] );
                            update_post_meta( $id, 'cf7_pardot_fields', $_REQUEST['cf7_pardot_fields'] );
                            update_post_meta( $id, 'cf7_pardot_list', $_REQUEST['cf7_pardot_list'] );
                            update_option( 'cf7_pardot_action_'.$id, $_REQUEST['cf7_pardot_action'] );
                            ?>
                                <div class="notice notice-success is-dismissible">
                                    <p><?php esc_html_e( 'Integration settings saved.', 'cf7_pardot' ); ?></p>
                                </div>
                            <?php
                        }
                        
                        $cf7_pardot = get_post_meta( $id, 'cf7_pardot', true );
                        $cf7_pardot_fields = get_post_meta( $id, 'cf7_pardot_fields', true );
                        $cf7_pardot_list = get_post_meta( $id, 'cf7_pardot_list', true );
                        if ( ! $cf7_pardot_list ) {
                            $cf7_pardot_list = array();
                        }
                        
                        $action = get_option( 'cf7_pardot_action_'.$id );
                        if ( ! $action ) {
                            $action = 'create';
                        }
                        
                        ?>
                        <p style="font-size: 17px;"><strong><?php esc_html_e( 'Form Name', 'cf7_pardot' ); ?>:</strong> <?php echo get_the_title( $form_id ); ?></p>
                        <hr>
                        <form method="post">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row"><label><?php esc_html_e( 'Object', 'cf7_pardot' ); ?></label></th>
                                        <td>
                                            <select name="cf7_pardot_module">
                                                <option value=""><?php esc_html_e( 'Select an object', 'cf7_pardot' ); ?></option>
                                                <option value="prospect" selected="selected"><?php esc_html_e( 'Prospects', 'cf7_pardot' ); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label><?php esc_html_e( 'Pardot Integration?', 'cf7_pardot' ); ?></label></th>
                                        <td>
                                            <input type="hidden" name="cf7_pardot" value="0" />
                                            <input type="checkbox" name="cf7_pardot" value="1"<?php echo ( $cf7_pardot ? ' checked' : '' ); ?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label><?php esc_html_e( 'Action Event', 'cf7_pardot' ); ?></label></th>
                                        <td>
                                            <fieldset>
                                                <label><input type="radio" name="cf7_pardot_action" value="create"<?php echo ( $action == 'create' ? ' checked="checked"' : '' ); ?> /> <?php esc_html_e( 'Create Object Record', 'cf7_pardot' ); ?></label>&nbsp;&nbsp;
                                                <label><input type="radio" name="cf7_pardot_action" value="create_or_update"<?php echo ( $action == 'create_or_update' ? ' checked="checked"' : '' ); ?> /> <?php esc_html_e( 'Create/Update Object Record', 'cf7_pardot' ); ?></label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label><?php esc_html_e( 'List Memberships', 'cf7_pardot' ); ?></label></th>
                                        <td>
                                            <input type="hidden" name="cf7_pardot_list" value="" />
                                            <fieldset>
                                                <?php
                                                    $list = get_option( 'cf7_pardot_list' );
                                                    if ( $list != null ) {
                                                        foreach ( $list as $key => $value ) {
                                                            $checked = '';
                                                            if ( in_array( $key, $cf7_pardot_list ) ) {
                                                                $checked = ' checked="checked"';
                                                            }
                                                            ?><label><input type="checkbox" name="cf7_pardot_list[]" value="<?php echo $key; ?>"<?php echo $checked; ?> /> <?php echo $value; ?></label><br><?php
                                                        }
                                                    }
                                                ?>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php
                                $_form = get_post_meta( $id, '_form', true );
                                if ( $_form ) {
                                    preg_match_all( '#\[(.*?)\]#', $_form, $matches );
                                    $cf7_fields = array();
                                    if ( $matches != null ) {
                                        foreach ( $matches[1] as $match ) {
                                            $match_explode = explode( ' ', $match );
                                            $field_type = str_replace( '*', '', $match_explode[0] );
                                            if ( $field_type != 'submit' ) {
                                                if ( isset( $match_explode[1] ) ) {
                                                    $cf7_fields[$match_explode[1]] = array(
                                                        'key'   => $match_explode[1],
                                                        'type'  => $field_type,
                                                    );
                                                }
                                            }
                                        }

                                        if ( $cf7_fields != null ) {
                                            ?>
                                                <table class="widefat striped">
                                                    <thead>
                                                        <tr>
                                                            <th><?php esc_html_e( 'Contact Form 7 Form Field', 'cf7_pardot' ); ?></th>
                                                            <th><?php esc_html_e( 'Pardot Object Field', 'cf7_pardot' ); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tfoot>
                                                        <tr>
                                                            <th><?php esc_html_e( 'Contact Form 7 Form Field', 'cf7_pardot' ); ?></th>
                                                            <th><?php esc_html_e( 'Pardot Object Field', 'cf7_pardot' ); ?></th>
                                                        </tr>
                                                    </tfoot>
                                                    <tbody>
                                                        <?php
                                                            $fields = get_option( 'cf7_pardot_module_fields' );
                                                            if ( $fields ) {
                                                                $fields = unserialize( $fields );
                                                                asort( $fields );
                                                            } else {
                                                                $fields = array();
                                                            }
                                                            
                                                            foreach ( $cf7_fields as $cf7_field_key => $cf7_field_value ) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $cf7_field_key; ?></td>
                                                                        <td>
                                                                            <select name="cf7_pardot_fields[<?php echo $cf7_field_key; ?>][key]">
                                                                                <option value=""><?php esc_html_e( 'Select a field', 'cf7_pardot' ); ?></option>
                                                                                <?php
                                                                                    $type = '';
                                                                                    if ( $fields != null ) {
                                                                                        foreach ( $fields as $field_key => $field_value ) {
                                                                                            $selected = '';
                                                                                            if ( isset( $cf7_pardot_fields[$cf7_field_key]['key'] ) && $cf7_pardot_fields[$cf7_field_key]['key'] == $field_key ) {
                                                                                                $selected = ' selected="selected"';
                                                                                                $type = $field_value['type'];
                                                                                            }
                                                                                            ?><option value="<?php echo $field_key; ?>"<?php echo $selected; ?>><?php echo $field_value['label']; ?> (<?php esc_html_e( 'Data Type:', 'cf7_pardot' ); ?> <?php echo $field_value['type']; echo ( $field_value['required'] ? esc_html__( ' and Field: required', 'cf7_pardot' ) : '' ); ?>)</option><?php
                                                                                        }
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                            <input type="hidden" name="cf7_pardot_fields[<?php echo $cf7_field_key; ?>][type]" value="<?php echo $type; ?>" />
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                            }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php
                                        }
                                    }
                                }
                            ?>
                            <p>
                                <input type='submit' class='button-primary' name="submit" value="<?php esc_html_e( 'Save Changes', 'cf7_pardot' ); ?>" />
                            </p>
                        </form>
                        <?php
                    } else {
                        $client_id = get_option( 'cf7_pardot_client_id' );
                        $client_secret = get_option( 'cf7_pardot_client_secret' );
                        if ( $client_id ) {
                            $domain = 'https://login.salesforce.com/';
                            $pardot = new CF7_Salesforce_Pardot_API( $domain, $client_id, $client_secret );
                            $token = get_option( 'cf7_pardot' );
                            $pardot->getRefreshToken( $token );
                            $token = get_option( 'cf7_pardot' );
                            $business_unit_id = get_option( 'cf7_pardot_business_unit_id' );
                            $fields = get_option( 'cf7_pardot_module_fields' );
                            if ( $fields ) {
                                $fields = unserialize( $fields );
                            } else {
                                $fields = array();
                            }

                            $prospect_custom_fields = array();
                            $has_fields = 1;
                            $offset = 0;
                            for ( $i=1; $i<=5; $i++ ) {
                                if ( $has_fields ) {
                                    $temp_prospect_custom_fields = $pardot->getProspectCustomFields( $token, $business_unit_id, $offset );
                                    if ( $temp_prospect_custom_fields != null ) {
                                        $prospect_custom_fields = $prospect_custom_fields + $temp_prospect_custom_fields;
                                        $offset = $offset + 200;
                                    } else {
                                        $has_fields = 0;
                                    }
                                }
                            }

                            $prospect_fields = serialize( array_merge( $fields, $prospect_custom_fields ) );
                            update_option( 'cf7_pardot_module_fields', $prospect_fields );

                            $prospect_fields = serialize( array_merge( $fields, $prospect_custom_fields ) );
                            update_option( 'cf7_pardot_module_fields', $prospect_fields );
                            
                            $list = array();
                            $has_list = 1;
                            $offset = 0;
                            for ( $i=1; $i<=25; $i++ ) {
                                if ( $has_list ) {
                                    $temp_list = $pardot->getList( $token, $business_unit_id, $offset );
                                    if ( $temp_list != null ) {
                                        $list = $list + $temp_list;
                                        $offset = $offset + 200;
                                    } else {
                                        $has_list = 0;
                                    }
                                }
                            }
                            update_option( 'cf7_pardot_list', $list );
                        } else {
                            $user_key = get_option( 'cf7_pardot_user_key' );
                            if ( $user_key ) {
                                $user_key = get_option( 'cf7_pardot_user_key' );
                                $email = get_option( 'cf7_pardot_email' );
                                $password = cf7_pardot_crypt( get_option( 'cf7_pardot_password' ), 'd', $user_key );
                                $pardot = new CF7_Pardot_API( $email, $password, $user_key );
                                $api_key = $pardot->authentication();
                                if ( $api_key ) {
                                    $fields = get_option( 'cf7_pardot_module_fields' );
                                    if ( $fields ) {
                                        $fields = unserialize( $fields );
                                    } else {
                                        $fields = array();
                                    }
                                    
                                    $prospect_custom_fields = array();
                                    $has_fields = 1;
                                    $offset = 0;
                                    for ( $i=1; $i<=5; $i++ ) {
                                        if ( $has_fields ) {
                                            $temp_prospect_custom_fields = $pardot->getProspectCustomFields( $api_key, $offset );
                                            if ( $temp_prospect_custom_fields != null ) {
                                                $prospect_custom_fields = $prospect_custom_fields + $temp_prospect_custom_fields;
                                                $offset = $offset + 200;
                                            } else {
                                                $has_fields = 0;
                                            }
                                        }
                                    }
                                    
                                    $prospect_fields = serialize( array_merge( $fields, $prospect_custom_fields ) );
                                    update_option( 'cf7_pardot_module_fields', $prospect_fields );
                                    
                                    $list = array();
                                    $has_list = 1;
                                    $offset = 0;
                                    for ( $i=1; $i<=25; $i++ ) {
                                        if ( $has_list ) {
                                            $temp_list = $pardot->getList( $api_key, $offset );
                                            if ( $temp_list != null ) {
                                                $list = $list + $temp_list;
                                                $offset = $offset + 200;
                                            } else {
                                                $has_list = 0;
                                            }
                                        }
                                    }
                                    update_option( 'cf7_pardot_list', $list );
                                }
                            }
                        }
                        
                        ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Form Name', 'cf7_pardot' ); ?></th>
                                    <th><?php esc_html_e( 'Integration Status', 'cf7_pardot' ); ?></th>       
                                    <th><?php esc_html_e( 'Action', 'cf7_pardot' ); ?></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th><?php esc_html_e( 'Form Name', 'cf7_pardot' ); ?></th>
                                    <th><?php esc_html_e( 'Integration Status', 'cf7_pardot' ); ?></th>       
                                    <th><?php esc_html_e( 'Action', 'cf7_pardot' ); ?></th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                    $args = array(
                                        'post_type'         => 'wpcf7_contact_form',
                                        'order'             => 'ASC',
                                        'posts_per_page'    => -1,
                                    );

                                    $forms = new WP_Query( $args );
                                    if ( $forms->have_posts() ) {
                                        while ( $forms->have_posts() ) {
                                            $forms->the_post();
                                            ?>
                                                <tr>
                                                    <td><?php echo get_the_title(); ?></td>
                                                    <td><?php echo ( get_post_meta( get_the_ID(), 'cf7_pardot', true ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>' ); ?></td>
                                                    <td><a href="<?php echo menu_page_url( 'cf7_pardot_integration', 0 ); ?>&id=<?php echo get_the_ID(); ?>"><span class="dashicons dashicons-edit"></span></a></td>
                                                </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                            <tr>
                                                <td colspan="3"><?php esc_html_e( 'No forms found.', 'cf7_pardot' ); ?></td>
                                            </tr>
                                        <?php
                                    }

                                    wp_reset_postdata();
                                ?>
                            </tbody>
                        </table>
                        <?php
                    }
                } else {
                    ?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php esc_html_e( 'Please verify purchase code.', 'cf7_pardot' ); ?></p>
                        </div>
                    <?php
                }
                ?>
            </div>
        <?php
    }
}

/*
 * This is a function that verify product licence.
 */
if ( ! function_exists( 'cf7_pardot_licence_verification_callback' ) ) {
    function cf7_pardot_licence_verification_callback() {
        
        if ( isset( $_REQUEST['verify'] ) ) {
            if ( isset( $_REQUEST['cf7_pardot_purchase_code'] ) ) {
                update_site_option( 'cf7_pardot_purchase_code', $_REQUEST['cf7_pardot_purchase_code'] );
                
                $data = array(
                    'sku'           => '22240057',
                    'purchase_code' => $_REQUEST['cf7_pardot_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'verify',
                    
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://obtaincode.net/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);
                
                $response = json_decode( $json_response );
                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'cf7_pardot_licence', 1 );
                    }
                }
            }
        } else if ( isset( $_REQUEST['unverify'] ) ) {
            if ( isset( $_REQUEST['cf7_pardot_purchase_code'] ) ) {
                $data = array(
                    'sku'           => '22240057',
                    'purchase_code' => $_REQUEST['cf7_pardot_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'unverify',
                    
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://obtaincode.net/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);

                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'cf7_pardot_purchase_code', '' );
                        update_site_option( 'cf7_pardot_licence', 0 );
                    }
                }
            }
        }    
        
        $cf7_pardot_purchase_code = get_site_option( 'cf7_pardot_purchase_code' );
        ?>
            <div class="wrap">      
                <h2><?php esc_html_e( 'Licence Verification', 'cf7_pardot' ); ?></h2>
                <hr>
                <?php
                    if ( isset( $response->success ) ) {
                        if ( $response->success ) {                            
                             ?>
                                <div class="notice notice-success is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        } else {
                            update_site_option( 'cf7_pardot_licence', 0 );
                            ?>
                                <div class="notice notice-error is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        }
                    }
                ?>
                <form method="post">
                    <table class="form-table">                    
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Purchase Code', 'cf7_pardot' ); ?></th>
                                <td>
                                    <input name="cf7_pardot_purchase_code" type="text" class="regular-text" value="<?php echo $cf7_pardot_purchase_code; ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <input type='submit' class='button-primary' name="verify" value="<?php esc_html_e( 'Verify', 'cf7_pardot' ); ?>" />
                        <input type='submit' class='button-primary' name="unverify" value="<?php esc_html_e( 'Unverify', 'cf7_pardot' ); ?>" />
                    </p>
                </form>   
            </div>
        <?php
    }
}

if ( ! function_exists( 'cf7_pardot_api_error_logs_callback' ) ) {
    function cf7_pardot_api_error_logs_callback() {
        
        $file_path = CF7_PARDOT_PLUGIN_PATH.'debug.log';
        if ( isset( $_POST['submit'] ) ) {
            $file = fopen( $file_path, 'w' );
            fclose( $file );
        }
        
        $licence = get_site_option( 'cf7_pardot_licence' );
        ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Pardot API Error Logs', 'cf7_pardot' ); ?></h1>
                <hr>
                <?php
                    if ( $licence ) {
                        $file = fopen( $file_path, 'r' );
                            $file_size = filesize( $file_path );
                            if ( $file_size ) {
                                $file_data = fread( $file, $file_size );
                                if ( $file_data ) {
                                    echo '<pre style="overflow: scroll;">'; print_r( $file_data ); echo '</pre>';
                                    ?>
                                        <form method="post">
                                            <p>
                                                <input type='submit' class='button-primary' name="submit" value="<?php esc_html_e( 'Clear API Error Logs', 'cf7_pardot' ); ?>" />
                                            </p>
                                        </form>
                                    <?php
                                }
                            } else {
                                ?><p><?php esc_html_e( 'No API error logs found.', 'cf7_pardot' ); ?></p><?php
                            }
                        fclose( $file );
                    } else {
                        ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'Please verify purchase code.', 'cf7_pardot' ); ?></p>
                            </div>
                        <?php
                    }
                ?>
            </div>
        <?php
    }
}

if ( ! function_exists( 'cf7_pardot_settings_callback' ) ) {
    function cf7_pardot_settings_callback() {
        
        if ( isset( $_POST['submit'] ) ) {
            $notification_subject = sanitize_text_field( $_POST['cf7_pardot_notification_subject'] );
            update_option( 'cf7_pardot_notification_subject', $notification_subject );
            
            $notification_send_to = sanitize_text_field( $_POST['cf7_pardot_notification_send_to'] );
            update_option( 'cf7_pardot_notification_send_to', $notification_send_to );
            
            $uninstall = (int) $_POST['cf7_pardot_uninstall'];
            update_option( 'cf7_pardot_uninstall', $uninstall );

            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Settings saved.', 'cf7_pardot' ); ?></p>
                </div>
            <?php
        }
        
        $notification_subject = get_option( 'cf7_pardot_notification_subject' );
        if ( ! $notification_subject ) {
            $notification_subject = esc_html__( 'API Error Notification', 'cf7_pardot' );
        }
        $notification_send_to = get_option( 'cf7_pardot_notification_send_to' );
        $uninstall = get_option( 'cf7_pardot_uninstall' );
        $licence = get_site_option( 'cf7_pardot_licence' );
        ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Settings', 'cf7_pardot' ); ?></h1>
                <hr>
                <?php
                    if ( $licence ) {
                        ?>
                            <form method="post">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row"><label><?php esc_html_e( 'API Error Notification', 'cf7_pardot' ); ?></label></th>
                                            <td>
                                                <label><?php esc_html_e( 'Subject', 'cf7_pardot' ); ?></label><br>
                                                <input class="regular-text" type="text" name="cf7_pardot_notification_subject" value="<?php echo $notification_subject; ?>" />
                                                <p class="description"><?php esc_html_e( 'Enter the subject.', 'cf7_pardot' ); ?></p><br><br>
                                                <label><?php esc_html_e( 'Send To', 'cf7_pardot' ); ?></label><br>
                                                <input class="regular-text" type="text" name="cf7_pardot_notification_send_to" value="<?php echo $notification_send_to; ?>" />
                                                <p class="description"><?php esc_html_e( 'Enter the email address. For multiple email addresses, you can add email address by comma separated.', 'cf7_pardot' ); ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label><?php esc_html_e( 'Delete data on uninstall?', 'cf7_pardot' ); ?></label></th>
                                            <td>
                                                <input type="hidden" name="cf7_pardot_uninstall" value="0" />
                                                <input type="checkbox" name="cf7_pardot_uninstall" value="1"<?php echo ( $uninstall ? ' checked' : '' ); ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p>
                                    <input type='submit' class='button-primary' name="submit" value="<?php esc_html_e( 'Save Changes', 'cf7_pardot' ); ?>" />
                                </p>
                            </form>
                        <?php
                    } else {
                        ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'Please verify purchase code.', 'cf7_pardot' ); ?></p>
                            </div>
                        <?php
                    }
                ?>
            </div>
        <?php
    }
}