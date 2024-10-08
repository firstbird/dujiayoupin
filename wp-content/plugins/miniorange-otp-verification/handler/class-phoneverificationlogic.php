<?php
/**
 * Comman handler to handle the phone logic during phone verification.
 *
 * @package miniorange-otp-verification/handler
 */

namespace OTP\Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use OTP\Helper\FormSessionVars;
use OTP\Helper\GatewayFunctions;
use OTP\Helper\MoConstants;
use OTP\Helper\MoMessages;
use OTP\Helper\MoUtility;
use OTP\Helper\SessionUtils;
use OTP\Objects\FormSessionData;
use OTP\Objects\VerificationLogic;
use OTP\Traits\Instance;
use OTP\LicenseLibrary\Mo_License_Service;

// mzl add begin
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Console\Console;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
// mzl add end 

/**
 * This class handles all the phone related logic for OTP Verification
 * Process the phone number and starts the phone verification process.
 */
if ( ! class_exists( 'PhoneVerificationLogic' ) ) {
	/**
	 * PhoneVerificationLogic class
	 */
	final class PhoneVerificationLogic extends VerificationLogic {

		use Instance;


// mzl add begin
/**
     * 使用AK&SK初始化账号Client
     * @return Dysmsapi Client
     */
    public static function createClient(){
        // 工程代码泄露可能会导致 AccessKey 泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考。
        // 建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html。
        $config = new Config([
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID。
            "accessKeyId" => "LTAI5tKJBzKEiGrcaaDbyHGz",
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
            "accessKeySecret" => "1REL31xhKAnXBBr7GP1ELDsOaxPBYL"
        ]);
        // Endpoint 请参考 https://api.aliyun.com/product/Dysmsapi
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function sendSms($phone_number){
        $client = self::createClient();
		$code = ''.mt_rand(1000,9999).'';
        $sendSmsRequest = new SendSmsRequest([
            "signName" => "VirtualSpace",
            "templateCode" => "SMS_460725266",
            "phoneNumbers" => '86' . $phone_number,
            "templateParam" => "{\"code\":\"$code\"}",
            "smsUpExtendCode" => ""
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            $resp = $client->sendSmsWithOptions($sendSmsRequest, $runtime);
	        $respMap = $resp->toMap();
			$content = array(
				"statusCode" => $respMap['statusCode'] === 200 && $respMap['body']['Code'] === 'OK' ? "OK" : "ERR",
				"smsCode" => $code,
				"requestId" => $respMap['body']['RequestId'],
			);
			return $content;
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 此处仅做打印展示，请谨慎对待异常处理，在工程项目中切勿直接忽略异常。
            // 错误 message
            var_dump($error->message);
            // 诊断地址
            var_dump($error->data["Recommend"]);
            Utils::assertAsString($error->message);
        }
    }
// mzl add end 
		/**
		 * This function is called to handle Email Verification request. Processes
		 * the request and starts the OTP Verification process. Starts of with
		 * checking if the phone number is in the requested format.
		 *
		 * @param string $user_login username of the user.
		 * @param string $user_email email of the user.
		 * @param string $phone_number phone number of the user.
		 * @param string $otp_type email or sms verification.
		 * @param string $from_both has user enabled from both.
		 */
		public function handle_logic( $user_login, $user_email, $phone_number, $otp_type, $from_both ) {
			// $this->checkIfUserRegistered( $otp_type, $from_both );
			$match                   = MoUtility::validate_phone_number( '+86' . $phone_number );
			// $is_country_block        = MoUtility::check_for_selected_country_addon( $phone_number );
			$is_country_block = false;
			$message                 = MoMessages::showMessage( MoMessages::BLOCKED_COUNTRY );
			$mle                     = MoUtility::mllc();
			$license_expired_message = MoMessages::showMessage( MoMessages::ERROR_OTP_PHONE );
			if ( $mle['STATUS'] ) {
				if ( $this->is_ajax_form() ) {
					wp_send_json( MoUtility::create_json( $license_expired_message, MoConstants::ERROR_JSON_TYPE ) );
				} else {
					miniorange_site_otp_validation_form( null, null, null, $license_expired_message, $otp_type, $from_both );
				}
			}
			if ( $match && $is_country_block ) {
				if ( $this->is_ajax_form() ) {
					wp_send_json( MoUtility::create_json( $message, MoConstants::COUNTRY_BLOCKED_ERROR ) );
				} else {
					miniorange_site_otp_validation_form( null, null, null, $message, $otp_type, $from_both );
				}
			}
			switch ( $match ) {
				case 0:
					$this->handle_not_matched( $phone_number, $otp_type, $from_both );
					break;
				case 1:
					$this->handle_matched( $user_login, $user_email, $phone_number, $otp_type, $from_both );
					break;
			}
		}


		/**
		 * Checks if the user is registered with miniorange and show the error message if not registereed.
		 *
		 * @param array $otp_type .
		 * @param array $from_both .
		 * @return void
		 */
		private function checkIfUserRegistered( $otp_type, $from_both ) {
			if ( ! MoUtility::micr() ) {
				$message = MoMessages::showMessage( MoMessages::NEED_TO_REGISTER );
				if ( $this->is_ajax_form() ) {

					wp_send_json( MoUtility::create_json( $message, MoConstants::ERROR_JSON_TYPE ) );
				} else {
					miniorange_site_otp_validation_form( null, null, null, $message, $otp_type, $from_both );
				}
			}
		}


		/**
		 * This function starts the OTP Verification process if phone number matches the
		 * correct format and is not blocked by the admin.
		 *
		 * @param string $user_login username of the user.
		 * @param string $user_email email of the user.
		 * @param string $phone_number  phone number of the user.
		 * @param string $otp_type email or sms verification.
		 * @param string $from_both string has user enabled from both.
		 */
		public function handle_matched( $user_login, $user_email, $phone_number, $otp_type, $from_both ) {
			$message = str_replace( '##phone##', $phone_number, $this->get_is_blocked_message() );
			if ( $this->is_blocked( $user_email, $phone_number ) ) {
				if ( $this->is_ajax_form() ) {
					wp_send_json( MoUtility::create_json( $message, MoConstants::ERROR_JSON_TYPE ) );
				} else {
					miniorange_site_otp_validation_form( null, null, null, $message, $otp_type, $from_both );
				}
			} else {
				do_action( 'mo_globally_banned_phone_check', $phone_number, $this->is_ajax_form(), $otp_type, $from_both );
				$this->start_otp_verification( $user_login, $user_email, $phone_number, $otp_type, $from_both );
			}
		}


		/**
		 * This function starts the OTP Verification process and contacts server to send OTP to the
		 * user's phone number.
		 *
		 * @param string $user_login    username of the user.
		 * @param string $user_email email of the user.
		 * @param string $phone_number  phone number of the user.
		 * @param string $otp_type email or sms verification.
		 * @param string $from_both string has user enabled from both.
		 */
		public function start_otp_verification( $user_login, $user_email, $phone_number, $otp_type, $from_both ) {
			$gateway           = GatewayFunctions::instance();
			$verification_type = 'SMS';
			//$content           = $gateway->mo_send_otp_token( $verification_type, '', $phone_number );
			$content = self::sendSms($phone_number);
			switch ( $content['statusCode'] ) {
				case "OK":
					SessionUtils::set_phone_vcode( $content['smsCode'] );
					$this->handle_otp_sent( $user_login, $user_email, $phone_number, $otp_type, $from_both, $content );
					break;
				default:
					$this->handle_otp_sent_failed( $user_login, $user_email, $phone_number, $otp_type, $from_both, $content );
					break;
			}
		}


		/**
		 * This function handles what message needs to be shown to the user if phone number
		 * doesn't match the correct format. Check if admin has set any message, and check
		 * if the form is an ajax form to show the message in the correct format.
		 *
		 * @param string $phone_number  the phone number being processed.
		 * @param string $otp_type      email or sms verification.
		 * @param string $from_both has user enabled from both.
		 */
		public function handle_not_matched( $phone_number, $otp_type, $from_both ) {
			$message = str_replace( '##phone##', $phone_number, $this->get_otp_invalid_format_message() );
			if ( $this->is_ajax_form() ) {
				wp_send_json( MoUtility::create_json( $message, MoConstants::ERROR_JSON_TYPE ) );
			} else {
				miniorange_site_otp_validation_form( null, null, null, $message, $otp_type, $from_both );
			}
		}


		/**
		 * This function is called to handle what needs to be done when OTP sending fails.
		 * Checks if the current form is an AJAX form and decides what message has to be
		 * shown to the user.
		 *
		 * @param string $user_login    username of the user.
		 * @param string $user_email email of the user.
		 * @param string $phone_number  phone number of the user.
		 * @param string $otp_type email or sms verification.
		 * @param string $from_both has user enabled from both.
		 * @param array  $content string the json decoded response from server.
		 */
		public function handle_otp_sent_failed( $user_login, $user_email, $phone_number, $otp_type, $from_both, $content ) {
			$message = str_replace( '##phone##', $phone_number, $this->get_otp_sent_failed_message() );

			if ( $this->is_ajax_form() ) {
				wp_send_json( MoUtility::create_json( $message, MoConstants::ERROR_JSON_TYPE ) );
			} else {
				miniorange_site_otp_validation_form( null, null, null, $message, $otp_type, $from_both );
			}
		}


		/**
		 * This function is called to handle what needs to be done when OTP sending is successful.
		 * Checks if the current form is an AJAX form and decides what message has to be
		 * shown to the user.
		 *
		 * @param string $user_login username of the user.
		 * @param string $user_email email of the user.
		 * @param string $phone_number phone number of the user.
		 * @param string $otp_type email or sms verification.
		 * @param string $from_both has user enabled from both.
		 * @param array  $content string the json decoded response from server.
		 */
		public function handle_otp_sent( $user_login, $user_email, $phone_number, $otp_type, $from_both, $content ) {
			// txId => RequestId
			SessionUtils::set_phone_transaction_id( $content['requestId'] );
			$message = str_replace( '##phone##', $phone_number, $this->get_otp_sent_message() );
			apply_filters( 'mo_start_reporting', $content['requestId'], $phone_number, $phone_number, $otp_type, $message, 'OTP_SENT' );
			if ( $this->is_ajax_form() ) {
				wp_send_json( MoUtility::create_json( $message, MoConstants::SUCCESS_JSON_TYPE ) );
			} else {
				miniorange_site_otp_validation_form( $user_login, $user_email, $phone_number, $message, $otp_type, $from_both );
			}
		}


		/**
		 * Get the success message to be shown to the user when OTP was sent
		 * successfully. If admin has set his own unique message then
		 * show that to the user instead of the default one.
		 */
		public function get_otp_sent_message() {
			$send_msg = get_mo_option( 'success_phone_message', 'mo_otp_' );
			return $send_msg ? mo_( $send_msg ) : MoMessages::showMessage( MoMessages::OTP_SENT_PHONE );
		}


		/**
		 * Get the error message to be shown to the user when there was an
		 * error sending OTP. If admin has set his own unique message then
		 * show that to the user instead of the default one.
		 */
		public function get_otp_sent_failed_message() {
			$failed_msg = get_mo_option( 'error_phone_message', 'mo_otp_' );
			$failed_msg = $failed_msg ? mo_( $failed_msg ) : MoMessages::showMessage( MoMessages::ERROR_OTP_PHONE );

			$failed_msg = apply_filters( 'mo_get_otp_sent_failed_message', $failed_msg );

			return $failed_msg;
		}


		/**
		 * Function decides what message needs to be sent to the user when the
		 * phone number does not match the required format. It checks if the admin
		 * has set any message in the plugin settings and returns that instead of the
		 * default one.
		 */
		public function get_otp_invalid_format_message() {
			$invalid_msg = get_mo_option( 'invalid_phone_message', 'mo_otp_' );
			return $invalid_msg ? mo_( $invalid_msg ) : MoMessages::showMessage( MoMessages::ERROR_PHONE_FORMAT );
		}


		/**
		 * This function checks if the phone number has been blocked by the admin
		 *
		 * @param string $user_email email of the user.
		 * @param string $phone_number phone number of the user.
		 * @return bool
		 */
		public function is_blocked( $user_email, $phone_number ) {
			$blocked_phone_numbers = explode( ';', get_mo_option( 'blocked_phone_numbers' ) );
			$blocked_phone_numbers = apply_filters( 'mo_blocked_phones', $blocked_phone_numbers, $phone_number );
			return in_array( $phone_number, $blocked_phone_numbers, true );
		}


		/**
		 * Function decides what message needs to be shown to the user when he enters a
		 * blocked phone number. It checks if the admin has set any message in the
		 * plugin settings and returns that instead of the default one.
		 */
		public function get_is_blocked_message() {
			$blocked_msg = get_mo_option( 'blocked_phone_message', 'mo_otp_' );
			return $blocked_msg ? mo_( $blocked_msg ) : MoMessages::showMessage( MoMessages::ERROR_PHONE_BLOCKED );
		}
	}
}
