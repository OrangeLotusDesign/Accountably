<?php
/*
 * Functions related to payment gateways
 *
 */


/*
 * Function that returns an array with all payment gateways
 *
 * @param $only_slugs - returns an array with the payment gateways slugs
 *
 * @return array
 *
 */
function pms_get_payment_gateways( $only_slugs = false ) {

    $payment_gateways = apply_filters( 'pms_payment_gateways', array(

        'paypal_standard' => array(
            'display_name_user'  => __( 'PayPal', 'paid-member-subscriptions' ),
            'display_name_admin' => __( 'PayPal Standard', 'paid-member-subscriptions' ),
            'class_name'         => 'PMS_Payment_Gateway_PayPal_Standard'
        )

    ));


    if( $only_slugs )
        $payment_gateways = array_keys( $payment_gateways );

    return $payment_gateways;

}


/*
 * Returns the payment gateway object
 *
 * @param string $gateway_slug
 * @param array $payment_data
 *
 * @return object
 *
 */
function pms_get_payment_gateway( $gateway_slug = '', $payment_data = array() ) {

    if( empty( $gateway_slug ) )
        return null;

    $payment_gateways = pms_get_payment_gateways();

    if( !isset( $payment_gateways[$gateway_slug] ) || !isset( $payment_gateways[$gateway_slug]['class_name'] ) )
        return null;

    return new $payment_gateways[$gateway_slug]['class_name']( $payment_data );

}


/*
 * Returns the active pay gates selected by the admin in the Payments tab in PMS Settings
 *
 * @return array
 *
 */
function pms_get_active_payment_gateways() {

    $settings = get_option('pms_settings');

    if( !isset( $settings['payments']['active_pay_gates'] ) )
        return array();
    else
        return $settings['payments']['active_pay_gates'];

}
add_action( 'init', 'pms_get_active_payment_gateways' );


/*
 * Direct the data flow to the payment gateway
 *
 * @param string $payment_gateway_slug
 * @param array $payment_data
 *
 * @return void
 *
 */
function pms_to_gateway( $payment_gateway_slug, $payment_data ) {

    if( has_action( 'pms_process_payment_' . $payment_gateway_slug ) ) {

        $settings = get_option( 'pms_settings' );
        $settings = $settings['payments'];

        do_action( 'pms_process_payment_' . $payment_gateway_slug, $payment_data, $settings );

    } else {

        $payment_gateway = pms_get_payment_gateway( $payment_gateway_slug, $payment_data );
        $payment_gateway->process_sign_up();

    }

}


/*
 * Processes the webhooks for all active payment gateways
 *
 * @return void
 *
 */
function pms_payment_gateways_webhook_catcher() {

    $gateways = pms_get_payment_gateways();

    foreach( $gateways as $gateway_slug => $gateway_details ) {
        $gateway = pms_get_payment_gateway( $gateway_slug );

        if( $gateway !== null )
            $gateway->process_webhooks();
    }

}
add_action( 'init', 'pms_payment_gateways_webhook_catcher', 1 );


/*
 * If a payment process confirmation is provided in the request call the
 * payment gateway in question and process the confirmation
 *
 */
function pms_payment_gateways_process_confirmation() {

    if( !isset( $_REQUEST['pmstkn'] ) || !wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_payment_process_confirmation' ) )
        return;

    if( empty( $_REQUEST['pms-gateway'] ) )
        return;


    $gateway_slug = base64_decode( $_REQUEST['pms-gateway'] );

    $active_payment_gateways = pms_get_active_payment_gateways();

    if( in_array( $gateway_slug, $active_payment_gateways ) ) {

        $gateway = pms_get_payment_gateway( $gateway_slug );
        $gateway->process_confirmation();

    }

}
add_action( 'init', 'pms_payment_gateways_process_confirmation', 1 );