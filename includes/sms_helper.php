<?php
/**
 * BiharElection.com - SMS & OTP Gateway Helper
 * Powered by OfferPlant SMS Engine
 *
 * Sender ID: BIHELE
 * Template Name: BIHELE_OTP
 */

if (!defined('SMS_AUTH_KEY')) {
    define('SMS_AUTH_KEY', 'b0e99bea1fa7d15e27e1c5fd8e3c868');
}
if (!defined('SMS_SENDER_ID')) {
    define('SMS_SENDER_ID', 'BIHELE');
}
if (!defined('SMS_TEMPLATE_NAME')) {
    define('SMS_TEMPLATE_NAME', 'BIHELE_OTP');
}
if (!defined('SMS_API_URL')) {
    define('SMS_API_URL', 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms');
}
if (!defined('SMS_OTP_TEMPLATE')) {
    define('SMS_OTP_TEMPLATE', "Dear {#var#},\nYour OTP / EVC / Password is: {#var#}\nVisit https://biharelection.com\n  \nRegards\nBIHELE\nOfferPlant");
}

/**
 * Send bulk or single SMS through HTTP gateway
 *
 * @param string|array $mobile_list Comma-separated or array of 10-digit mobile numbers
 * @param string $sms Content of the SMS message
 * @param int $count Number of messages for status reporting
 * @param string $smstype 'english' or 'unicode'
 * @return array Status array with status ('success'|'error'), count, msg, and optional response
 */
function bulk_msg($mobile_list, $sms, $count = 1, $smstype = 'english')
{
    $auth_key = defined('SMS_AUTH_KEY') ? SMS_AUTH_KEY : 'b0e99bea1fa7d15e27e1c5fd8e3c868';
    $sender_id = defined('SMS_SENDER_ID') ? SMS_SENDER_ID : 'BIHELE';
    $api_url = defined('SMS_API_URL') ? SMS_API_URL : 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms';

    if (is_array($mobile_list)) {
        $mobile_list = implode(',', $mobile_list);
    }

    $data = array(
        'smsContent'     => $sms,
        'groupId'        => '',
        'routeId'        => 1,
        'mobileNumbers'  => $mobile_list,
        'senderId'       => $sender_id,
        'signature'      => '',
        'smsContentType' => $smstype
    );

    $text_sms = json_encode($data);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => $api_url . "?AUTH_KEY=" . urlencode($auth_key),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => $text_sms,
        CURLOPT_HTTPHEADER     => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    $res = array();
    if ($err) {
        $res['status'] = 'error';
        $res['count']  = 0;
        $res['msg']    = $err;
        $res['raw']    = null;
    } else {
        $res['status']    = 'success';
        $res['count']     = $count;
        $res['msg']       = $count . " SMS Sent Successfully";
        $res['http_code'] = $httpCode;
        $res['raw']       = $response;
    }

    return $res;
}

/**
 * Send OTP / EVC / Password SMS using approved DLT template (BIHELE_OTP)
 *
 * Approved DLT Template:
 * Dear {#var#},
 * Your OTP / EVC / Password is: {#var#}
 * Visit https://biharelection.com
 *   
 * Regards
 * BIHELE
 * OfferPlant
 *
 * @param string $mobile Recipient mobile number (10 digits)
 * @param string $name User name or recipient descriptor (1st {#var#})
 * @param string|int $otp OTP / Security code / Password (2nd {#var#})
 * @return array
 */
function sendOTP($mobile, $name, $otp)
{
    // Build approved DLT Template content
    $message = "Dear " . $name . ",\nYour OTP / EVC / Password is: " . $otp . "\nVisit https://biharelection.com\n  \nRegards\nBIHELE\nOfferPlant";

    return bulk_msg($mobile, $message);
}

/**
 * Send a custom direct SMS
 *
 * @param string $mobile
 * @param string $message
 * @return array
 */
function sendCustomSMS($mobile, $message)
{
    return bulk_msg($mobile, $message);
}
