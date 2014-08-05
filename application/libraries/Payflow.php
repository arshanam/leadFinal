<?php
class Payflow {
    protected $_config;
    protected $_gateway = NULL;
    protected $data = NULL;

    function __construct(){
        get_instance()->load->config('payment_gateway');
        $this->_config = get_instance()->config->item('payment');

        if ( !is_array($this->_config) || empty($this->_config) ){
            throw new Exception('Invalid payment configuration.');
        }

        $this->getPaymentGateway();
    }

    function getPaymentGateway() {
        if ( $this->_gateway == NULL ){
            if( isset($this->_config['library']) ){
                get_instance()->load->library($this->_config['library']);
                $this->_gateway = get_instance()->{$this->_config['library']};
                $this->_gateway->setTestMode($this->_config['test_mode']);
            }
        }

        return $this->_gateway;
    }

    public function processPayment(){
        if ($this->data === NULL){
            throw new Exception('Invalid order configuration.');
        }

        switch( $this->getPaymentGateway()->getPaymentAction() ){
            case 'AUTH_CAPTURE':
                $result = $this->getPaymentGateway()->capture($this->data);
                break;

            case 'AUTH':
                $result = $this->getPaymentGateway()->authorize($this->data);
                break;
        }

        return $result;
    }

    public function refund($amount){
        if ($this->_invoice === NULL){
            throw new Exception('Invalid invoice configuration.');
        } elseif (!$amount || !is_numeric($amount)) {
            throw new Exception('Invalid refund amount specified.');
        }

        $this->getPaymentGateway()->refund($this->data, $amount);
    }

    /**
     * @param Grading_invoices $invoice
     * @return void
     */
    public function setInvoice(Grading_invoices $invoice){
        $this->_invoice = $invoice;

        return $this;
    }

	/**
     * @param Grading_invoices $invoice
     * @return void
     */
    public function setData($data){
        $this->data = $data;

        return $this;
    }
}
