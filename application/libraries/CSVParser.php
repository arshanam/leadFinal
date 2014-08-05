<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

 class CSVParser
    {
        private $m_saHeader = array();
        private $m_sFileName = '';
        private $m_fp = false;
        private $m_naHeaderMap = array();
        private $m_saValues = array();
       
        function __construct()
        {
           
        }
   
        function __call($sMethodName, $saArgs)
        {
            //check to see if this is a set() or get() request, and extract the name
            if (preg_match("/[sg]et(.*)/", $sMethodName, $saFound))
            {
                //convert the name portion of the [gs]et to uppercase for header checking
                $sName = strtoupper($saFound[1]);
               
                //see if the entry exists in our named header-> index mapping
                  if (array_key_exists($sName, $this->m_naHeaderMap))
                  {
                      //it does.. so consult the header map for which index this header controls
                      $nIndex = $this->m_naHeaderMap[$sName];
                      if ($sMethodName{0} == 'g')
                      {
                          //return the value stored in the index associated with this name
                             return $this->m_saValues[$nIndex];
                      }
                      else
                      {
                          //set the valuw
                          $this->m_saValues[$nIndex] = $saArgs[0];
                          return true;
                      }
                  }
            }
           
            //nothing we control so bail out with a false
              return false;
          }       
         
          //get a nicely formatted header name. This will take product_id and make
          //it PRODUCTID in the header map. So now you won't need to worry about whether you need
          //to do a getProductID, or getproductid, or getProductId.. all will work.
        public static function GetNiceHeaderName($sName)
        {
            return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sName));
        }

        //process the header entry so we can map our named header fields to a numerical index, which
        //we'll use when we use fgetcsv().
        public function processHeader($file)
        {
			
			$this->m_fp = fopen($file, 'r');
            $sLine = fgets($this->m_fp);
            
            //you'll want to make this configurable
            $saFields = explode(",", $sLine);
           
            $nIndex = 0;
            foreach ($saFields as $sField)
            {
                //get the nice name to use for "get" and "set".
                $sField = trim($sField);
               
                $sNiceName = CSVParser::GetNiceHeaderName($sField);
               
                //track correlation of raw -> nice name so we don't have to do on-the-fly nice name checks
                //$this->m_saHeader[$nIndex] = array(C_PPCSV_HEADER_RAW => $sField, C_PPCSV_HEADER_NICE => $sNiceName);
                $this->m_naHeaderMap[$sNiceName] = $nIndex;
                $nIndex++;
            }
        }
       
        //read the next CSV entry
        public function getNext()
        {
            //this is a basic read, you will likely want to change this to accomodate what
            //you are using for CSV parameters (tabs, encapsulation, etc).
			//print_r($saValues = fgetcsv($this->m_fp));
            if (($saValues = fgetcsv($this->m_fp)) !== false)
            {
                $this->m_saValues = $saValues;
				
                return true;
            }
            return false;
        }        
    } // end of CSVParser class