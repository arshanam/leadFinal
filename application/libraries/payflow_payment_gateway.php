<?php

class Payflow_payment_gateway {

    /**
     * Transaction Types
     */
    const TRANSACTION_TYPE_SALE = 'S';
    const TRANSACTION_TYPE_CREDIT = 'C';
    const TRANSACTION_TYPE_AUTH_ONLY = 'A';
    const TRANSACTION_TYPE_DELAYED_CAPTURE = 'D';
    const TRANSACTION_TYPE_VOID = 'V';
    const TRANSACTION_TYPE_VOICE_AUTH = 'F';
    const TRANSACTION_TYPE_INQUIRY = 'I';

    /**
     * Tender Types
     */
    const PAYMENT_METHOD_ACH = 'A';
    const PAYMENT_METHOD_CREDIT_CARD = 'C';
    const PAYMENT_METHOD_PINLESS_DEBIT = 'P';
    const PAYMENT_METHOD_TELECHECK = 'K';
    const PAYMENT_METHOD_PAYPAL = 'P';

    /**
     * Payment Gateway URLs
     */
    const GATEWAY_URL_LIVE_MODE = 'https://payflowpro.paypal.com/transaction';
    const GATEWAY_URL_TEST_MODE = 'https://pilot-payflowpro.paypal.com/transaction';

    /**
     * Response Codes
     */
    const RESPONSE_CODE_APPROVED = 0;
    const RESPONSE_CODE_DECLINED = 12;
	const RESPONSE_CODE_INVALID_CC = 23;
	const RESPONSE_CODE_INVALID_CC_EXP_DATE = 24;
    const RESPONSE_CODE_FRAUDSERVICE_FILTER = 126;
    const RESPONSE_CODE_DECLINED_BY_FILTER = 125;
    const RESPONSE_CODE_DECLINED_BY_MERCHANT = 128;
    const RESPONSE_CODE_CAPTURE_ERROR = 111;

    /**
     * Gateway request timeout
     */
    protected $_clientTimeout = 45;

    /**
     * which gateway url the transactions will be sent to
     * @var boolean
     */
    protected $_test_mode = FALSE;

    /**
     * Holds the configuration options for the current instance.
     * @var array
     */
    protected $_config = array();

    function __construct(){
        get_instance()->load->config('payment_gateway');
        $this->_config = get_instance()->config->item('payflow_payment_gateway');

        if ( !is_array($this->_config) || empty($this->_config) ){
            throw new Exception('Invalid payment gateway configuration.');
        }
    }

    public function setTestMode($flag = TRUE){
        $this->_test_mode = $flag;
    }

    /**
     * Determines which action we should take when processing payments.
     *
     * @return string
     */
    public function getPaymentAction(){
        return $this->_config['payment_action'];
    }

    /**
     * Determines which URL the payment gateway requests will be sent to.
     *
     * @return string
     */
    protected function _getTransactionUrl(){
        return ($this->_test_mode === TRUE) ? self::GATEWAY_URL_TEST_MODE : self::GATEWAY_URL_LIVE_MODE;
    }

    /**
      * Return basic configuration information required for a request
      *
      * @param Orders_orders $invoice
      * @return array
      */
    protected function _buildBasicRequest($amount){
        return array(
            'VENDOR' => $this->_config['vendor'],
            'PARTNER' => $this->_config['partner'],
            'USER' => $this->_config['username'],
            'PWD' => $this->_config['password'],
            'VERBOSITY' => 'MEDIUM',
            'TENDER' => self::PAYMENT_METHOD_CREDIT_CARD,
            'AMT' => sprintf('%01.2f', round($amount, 2)),
			'COMMENT1' => 'Grading Application'
        );
    }

    /**
      * Configures the request based on the passed $invoice
      *
      * @param Orders_orders $invoice
      * @return array
      */
    protected function _buildPlaceRequest($data){
        $request = $this->_buildBasicRequest($data['payment_amount']);

        $request['CURRENCY'] = 'USD';
        $request['ACCT'] = $data['cc_number'];
        $request['EXPDATE'] = sprintf('%02d',$data['cc_exp_month']) . $data['cc_exp_year'];
        if($data['cc_cvv'] != '') {
			$request['CVV2'] = $data['cc_cvv'];
		}
        // set billing information
        $request['FIRSTNAME'] = $data['first_name'];
        $request['LASTNAME'] = $data['last_name'];
        $request['STREET'] = $data['street_1'];
        if ( $data['street_2'] ){
            $request['STREET'] .= ' ' . $data['street_2'];
        }
        $request['CITY'] = $data['city'];
        $request['STATE'] = (($data['country'] == 'US') ? $data['state_id'] : $data['state']);
        $request['ZIP'] = $data['zipcode'];
        $request['COUNTRY'] = $data['country'];
        $request['EMAIL'] = $data['email'];

        /*// set shipping information
        $request['SHIPTOFIRSTNAME'] = $invoice->shipping_firstname;
        $request['SHIPTOLASTNAME'] = $invoice->shipping_lastname;
        $request['SHIPTOSTREET'] = $invoice->shipping_street_1;
        if ( $invoice->shipping_street_2 ){
            $request['SHIPTOSTREET'] .= ' ' . $invoice->shipping_street_2;
        }
        $request['SHIPTOCITY'] = $invoice->shipping_city;
        $request['SHIPTOSTATE'] = $invoice->shipping_state;
        $request['SHIPTOZIP'] = $invoice->shipping_zipcode;
        $request['SHIPTOCOUNTRY'] = $invoice->shipping_country;*/

        return $request;
    }

    protected function _get_request_id(){
        return uniqid('', TRUE);
    }

    /**
     * Post request to payment gateway and return response.
     *
     * @param array $request
     * @return array
     */
    protected function _postRequest(array $request){
		$curl_client = curl_init();
        curl_setopt($curl_client, CURLOPT_URL, $this->_getTransactionUrl());

        // set headers required by the payment gateway
        curl_setopt($curl_client, CURLOPT_HTTPHEADER, array(
            'X-VPS-VIT-CLIENT-CERTIFICATION-ID: 33baf5893fc2123d8b191d2d011b7fdc',
            'X-VPS-Request-ID: ' . $this->_get_request_id(),
            'X-VPS-CLIENT-TIMEOUT: ' . $this->_clientTimeout,
        ));

        curl_setopt($curl_client, CURLOPT_HEADER, 0);
        curl_setopt($curl_client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_client, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_client, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl_client, CURLOPT_TIMEOUT, $this->_clientTimeout);
        curl_setopt($curl_client, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_client, CURLOPT_POSTFIELDS, urldecode(http_build_query($request)));
        curl_setopt($curl_client, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($curl_client, CURLOPT_FORBID_REUSE, TRUE);
        curl_setopt($curl_client, CURLOPT_POST, 1);

        if ( $this->_config['use_proxy'] && $this->_config['proxy_host'] != NULL && $this->_config['proxy_port'] != NULL ){
            curl_setopt($curl_client, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($curl_client, CURLOPT_PROXY, $this->_config['proxy_host']);
            curl_setopt($curl_client, CURLOPT_PROXYPORT, $this->_config['proxy_port']);
        }

        $result = curl_exec($curl_client);
        curl_close($curl_client);

        if ($result === FALSE) {
            trigger_error(curl_error($curl_client));
        }

        $result_array = array();
        parse_str($result, $result_array);

        return $result_array;
    }

    /**
     * Authorize payment
     *
     * @param Orders_orders $invoice
     * @return Payflow_pro_payment_gateway
     */
    public function authorize($data){
        $request = $this->_buildPlaceRequest($data);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_AUTH_ONLY;

        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response['RESULT']){
            case self::RESPONSE_CODE_APPROVED:
                $data['transaction_id'] = $response['PNREF'];
                $data['transaction_status'] = 'PENDING_CAPTURE';
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $data['transaction_id'] = $response['PNREF'];
                $data['transaction_status'] = 'PENDING_FRAUD';
                break;
        }

        return $data;
    }

    /**
     * Capture payment
     *
     * @param Orders_orders $invoice
     * @return Payflow_pro_payment_gateway
     */
    public function capture($data){
        if (isset($data['transaction_id']) && !empty($data['transaction_id'])) {
            $request = $this->_buildBasicRequest($data['payment_amount']);
            $request['TRXTYPE'] = self::TRANSACTION_TYPE_DELAYED_CAPTURE;
            $request['ORIGID'] = $data['transaction_id'];
        } else {
            $request = $this->_buildPlaceRequest($data);
            $request['TRXTYPE'] = self::TRANSACTION_TYPE_SALE;
        }

        $data['response'] = $response = $this->_postRequest($request);

		//$this->_processErrors($response);

        switch ($response['RESULT']){
            case self::RESPONSE_CODE_APPROVED:
                $data['transaction_id'] = $response['PNREF'];
                $data['transaction_status'] = 'CAPTURED';
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $data['transaction_id'] = $response['PNREF'];
                $data['transaction_status'] = 'PENDING_FRAUD';
                break;
			case self::RESPONSE_CODE_INVALID_CC:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'INVAID_CARD_NUMBER';
				break;
			case self::RESPONSE_CODE_DECLINED:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'CARD_DECLINED';
				break;
			case self::RESPONSE_CODE_INVALID_CC_EXP_DATE:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'INVALID_CARD_EXP_DATE';
				break;
			case self::RESPONSE_CODE_DECLINED_BY_FILTER:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'INVALID_CARD_EXP_DATE';
				break;
			case self::RESPONSE_CODE_DECLINED_BY_MERCHANT:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'DECLINED_BY_MERCHANT';
				break;
			case self::RESPONSE_CODE_CAPTURE_ERROR:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'CAPTURE_ERROR';
				break;
			default:
				$data['transaction_id'] = '';
                $data['transaction_status'] = 'ERROR_IN_PROCESSING';
        }

        return $data;
    }

    /**
     * Void payment
     *
     * @param Orders_orders $invoice
     * @return Payflow_pro_payment_gateway
     */
    public function void($data)
    {
        throw new Exception('Void functionality not implemented yet.');

        // @FIXME update $invoice->total reference below, so that it will void the passed in amount instead of the entire order?
        $request = $this->_buildBasicRequest($data['payment_amount']);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_VOID;
        $request['ORIGID'] = $data['transaction_id'];
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response['RESULT'] == self::RESPONSE_CODE_APPROVED){
            $data['transaction_id'] = $response['PNREF'];
            $data['transaction_status'] = 'VOID';
        }

        return $this;
    }

    /**
     * Refund capture
     *
     * @param Orders_orders $invoice
     * @return Payflow_pro_payment_gateway
     */
    public function refund($data, $amount){
        $request = $this->_buildBasicRequest($amount);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_CREDIT;
        $request['ORIGID'] = $data['transaction_id'];
        $response = $this->_postRequest($request);
        //$this->_processErrors($response);

        if ($response['RESULT'] == self::RESPONSE_CODE_APPROVED){
            // @TODO returned PNREF can't be used to refund a second time... log this somewhere instead
            // $invoice->transaction_id = $response['PNREF'];

            // I believe that this is no longer necessary...
            // $invoice->transaction_status = 'REFUNDED';
        }

        return $this;
    }

    /**
      * determines if there are errors with the request and handles them accordingly
      */
    protected function _processErrors($response){
        if ($response['RESULT'] != self::RESPONSE_CODE_APPROVED && $response['RESULT'] != self::RESPONSE_CODE_FRAUDSERVICE_FILTER && !$response['RESULT'] != self::RESPONSE_CODE_INVALID_CC && $response['RESULT'] != self::RESPONSE_CODE_INVALID_CC_EXP_DATE){
            throw new Exception($response['RESPMSG']);
        }
    }
}