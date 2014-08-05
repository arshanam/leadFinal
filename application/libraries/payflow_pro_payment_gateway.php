<?

class Payflow_pro_payment_gateway {

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
        $this->_config = get_instance()->config->item('payflow_pro_payment_gateway');

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
      * @param Orders_orders $order
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
        );
    }

    /**
      * Configures the request based on the passed $order
      *
      * @param Orders_orders $order
      * @return array
      */
    protected function _buildPlaceRequest(Orders_orders $order){
        $request = $this->_buildBasicRequest($order->total);

        $request['CURRENCY'] = 'USD';
        $request['ACCT'] = $order->payment_method_cc_number;
        $request['EXPDATE'] = sprintf('%02d',$order->payment_method_expiration_month) . substr($order->payment_method_expiration_year,-2,2);
        $request['CVV2'] = $order->payment_method_cvv2;

        // set billing information
        $request['FIRSTNAME'] = $order->billing_firstname;
        $request['LASTNAME'] = $order->billing_lastname;
        $request['STREET'] = $order->billing_street_1;
        if ( $order->billing_street_2 ){
            $request['STREET'] .= ' ' . $order->billing_street_2;
        }
        $request['CITY'] = $order->billing_city;
        $request['STATE'] = $order->billing_state;
        $request['ZIP'] = $order->billing_zipcode;
        $request['COUNTRY'] = $order->billing_country;
        $request['EMAIL'] = $order->email;

        // set shipping information
        $request['SHIPTOFIRSTNAME'] = $order->shipping_firstname;
        $request['SHIPTOLASTNAME'] = $order->shipping_lastname;
        $request['SHIPTOSTREET'] = $order->shipping_street_1;
        if ( $order->shipping_street_2 ){
            $request['SHIPTOSTREET'] .= ' ' . $order->shipping_street_2;
        }
        $request['SHIPTOCITY'] = $order->shipping_city;
        $request['SHIPTOSTATE'] = $order->shipping_state;
        $request['SHIPTOZIP'] = $order->shipping_zipcode;
        $request['SHIPTOCOUNTRY'] = $order->shipping_country;

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
     * @param Orders_orders $order
     * @return Payflow_pro_payment_gateway
     */
    public function authorize(Orders_orders $order){
        $request = $this->_buildPlaceRequest($order);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_AUTH_ONLY;

        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response['RESULT']){
            case self::RESPONSE_CODE_APPROVED:
                $order->transaction_id = $response['PNREF'];
                $order->transaction_status = 'PENDING_CAPTURE';
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $order->transaction_id = $response['PNREF'];
                $order->transaction_status = 'PENDING_FRAUD';
                break;
        }

        return $this;
    }

    /**
     * Capture payment
     *
     * @param Orders_orders $order
     * @return Payflow_pro_payment_gateway
     */
    public function capture(Orders_orders $order){
        if ($order->transaction_id) {
            $request = $this->_buildBasicRequest($order->total);
            $request['TRXTYPE'] = self::TRANSACTION_TYPE_DELAYED_CAPTURE;
            $request['ORIGID'] = $order->transaction_id;
        } else {
            $request = $this->_buildPlaceRequest($order);
            $request['TRXTYPE'] = self::TRANSACTION_TYPE_SALE;
        }

        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response['RESULT']){
            case self::RESPONSE_CODE_APPROVED:
                $order->transaction_id = $response['PNREF'];
                $order->transaction_status = 'CAPTURED';
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $order->transaction_id = $response['PNREF'];
                $order->transaction_status = 'PENDING_FRAUD';
                break;
        }

        return $this;
    }

    /**
     * Void payment
     *
     * @param Orders_orders $order
     * @return Payflow_pro_payment_gateway
     */
    public function void(Orders_orders $order)
    {
        throw new Exception('Void functionality not implemented yet.');

        // @FIXME update $order->total reference below, so that it will void the passed in amount instead of the entire order?
        $request = $this->_buildBasicRequest($order->total);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_VOID;
        $request['ORIGID'] = $order->transaction_id;
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response['RESULT'] == self::RESPONSE_CODE_APPROVED){
            $order->transaction_id = $response['PNREF'];
            $order->transaction_status = 'VOID';
        }

        return $this;
    }

    /**
     * Refund capture
     *
     * @param Orders_orders $order
     * @return Payflow_pro_payment_gateway
     */
    public function refund(Orders_orders $order, $amount){
        $request = $this->_buildBasicRequest($amount);
        $request['TRXTYPE'] = self::TRANSACTION_TYPE_CREDIT;
        $request['ORIGID'] = $order->transaction_id;
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response['RESULT'] == self::RESPONSE_CODE_APPROVED){
            // @TODO returned PNREF can't be used to refund a second time... log this somewhere instead
            // $order->transaction_id = $response['PNREF'];

            // I believe that this is no longer necessary...
            // $order->transaction_status = 'REFUNDED';
        }

        return $this;
    }

    /**
      * determines if there are errors with the request and handles them accordingly
      */
    protected function _processErrors($response){
        if ($response['RESULT'] != self::RESPONSE_CODE_APPROVED && $response['RESULT'] != self::RESPONSE_CODE_FRAUDSERVICE_FILTER){
            throw new Exception($response['RESPMSG']);
        }
    }
}