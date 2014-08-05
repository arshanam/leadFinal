<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Jqgrid {

    public $version = '3.8.0.1';
    protected $pdo;
    protected $I = '';
    protected $dbtype;
    protected $select = "";
    protected $dbdateformat = 'Y-m-d';
    protected $dbtimeformat = 'Y-m-d H:i:s';
    protected $userdateformat = 'd/m/Y';
    protected $usertimeformat = 'd/m/Y H:i:s';
    protected static $queryLog = array();

    public function logQuery($sql, $data = null, $types=null, $input= null, $fld=null, $primary='') {
        self::$queryLog[] = array('time' => date('Y-m-d H:i:s'), 'query' => $sql, 'data' => $data, 'types' => $types, 'fields' => $fld, 'primary' => $primary, 'input' => $input);
    }

    public $debug = false;
    public $logtofile = true;

    public function debugout() {
        if ($this->logtofile) {
            $fh = @fopen("jqGrid.log", "a+");
            if ($fh) {
                $the_string = "Executed " . count(self::$queryLog) . " query(s) - " . date('Y-m-d H:i:s') . "\n";
                $the_string .= print_r(self::$queryLog, true);
                fputs($fh, $the_string, strlen($the_string));
                fclose($fh);
                return( true );
            } else {
                echo "Can not write to log!";
            }
        } else {
            echo "<pre>\n";
            print_r(self::$queryLog);
            echo "</pre>\n";
        }
    }

    protected $GridParams = array("page" => "page", "rows" => "rows", "sort" => "sidx", "order" => "sord", "search" => "_search", "nd" => "nd", "id" => "id", "filter" => "filters", "searchField" => "searchField", "searchOper" => "searchOper", "searchString" => "searchString", "oper" => "oper", "query" => "grid", "addoper" => "add", "editoper" => "edit", "deloper" => "del", "excel" => "excel", "subgrid" => "subgrid", "totalrows" => "totalrows", "autocomplete" => "autocmpl");
    public $dataType = "xml";
    public $encoding = "utf-8";
    public $jsonencode = true;
    public $datearray = array();
    public $SelectCommand = "";
    public $ExportCommand = "";
    public $gSQLMaxRows = 1000;
    public $SubgridCommand = "";
    public $table = "";
    public $readFromXML = false;
    protected $userdata = null;
    public $customFunc = null;
    public $xmlCDATA = false;
    public $optimizeSearch = false;
    public $cacheCount = false;

    function __construct($db=null) {
        if (class_exists('jqGridDB'))
            $interface = jqGridDB::getInterface(); else
            $interface = 'local';
        $this->pdo = $db;
        if ($interface == 'pdo') {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbtype = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($this->dbtype == 'pgsql')
                $this->I = 'I';
        } else {
            $this->dbtype = $interface;
        }
    }

    protected function parseSql($sqlElement, $params, $bind=true) {
        $sql = jqGridDB::prepare($this->pdo, $sqlElement, $params, $bind);
        return $sql;
    }

    protected function execute($sqlId, $params, &$sql, $limit=false, $nrows=-1, $offset=-1, $order='', $sort='') {
        $this->select = $sqlId;
        if ($limit) {
            $this->select = jqGridDB::limit($this->select, $this->dbtype, $nrows, $offset, $order, $sort);
        } if
        ($this->debug)
            $this->logQuery($this->select, $params);
        $sql = $this->parseSql($this->select, $params);
        if ($sql) {
            return jqGridDB::execute($sql, $params);
        } else
            return false;
    }

    protected function getSqlElement($sqlId) {
        $tmp = explode('.', $sqlId);
        $sqlFile = trim($tmp[0]) . '.xml';
        if (file_exists($sqlFile)) {
            $root = simplexml_load_file($sqlFile);
            foreach ($root->sql as $sql) {
                if ($sql['Id'] == $tmp[1])
                    return $sql;
            }
        }
        return false;
    }

    protected function _getcount($sql, array $params=null, array $sumcols=null) {
        $qryRecs->COUNT = 0;
        $s = '';
        if (is_array($sumcols) && !empty($sumcols)) {
            foreach ($sumcols as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $dbfield => $oper) {
                        $s .= "," . trim($oper) . "(" . $dbfield . ") AS " . $k;
                    }
                } else {
                    $s .= ",SUM(" . $v . ") AS " . $k;
                }
            }
        }
        if
        (preg_match("/^\s*SELECT\s+DISTINCT/is", $sql) || preg_match('/\s+GROUP\s+BY\s+/is', $sql) || preg_match('/\s+UNION\s+/is', $sql) || substr_count(strtoupper($sql), 'SELECT') > 1) {
            $rewritesql = "SELECT COUNT(*) AS COUNT " . $s . " FROM ($sql) gridalias";
        } else {
            $rewritesql = preg_replace('/^\s*SELECT\s.*\s+FROM\s/Uis', 'SELECT COUNT(*) AS COUNT ' . $s . ' FROM ', $sql);
        } if
        (isset($rewritesql) && $rewritesql != $sql) {
            if (preg_match('/\sLIMIT\s+[0-9]+/i', $sql, $limitarr))
                $rewritesql .= $limitarr[0];
            $qryRecs = $this->queryForObject($rewritesql, $params, false);
            if ($qryRecs)
                return $qryRecs;
        } return
        $qryRecs;
    }

    protected function queryForObject($sqlId, $params, $fetchAll=false) {
        $sql = null;
        $ret = $this->execute($sqlId, $params, $sql, false);
        if ($ret === true) {
            $ret = jqGridDB::fetch_object($sql, $fetchAll, $this->pdo);
            jqGridDB::closeCursor($sql);
            return $ret;
        } else
            return $ret;
    }

    protected function _buildSearch($sqlEx, array $prm=null) {
        $s = '';
        $s1 = '';
        $i_ = $this->I;
        $sopt = array('eq' => "=", 'ne' => "<>", 'lt' => "<", 'le' => "<=", 'gt' => ">", 'ge' => ">=", 'bw' => " {$i_}LIKE ", 'bn' => " NOT {$i_}LIKE ", 'in' => ' IN ', 'ni' => ' NOT IN', 'ew' => " {$i_}LIKE ", 'en' => " NOT {$i_}LIKE ", 'cn' => " {$i_}LIKE ", 'nc' => " NOT {$i_}LIKE ");
        $s1 = " ( ";
        $filters = jqGridUtils::GetParam($this->GridParams["filter"], "");
        $rules = "";
        if ($filters) {
            if (function_exists('json_decode') && strtolower(trim($this->encoding)) == "utf-8")
                $jsona = json_decode($filters, true); else
                $jsona = jqGridUtils::decode($filters);
            if (is_array($jsona)) {
                $gopr = $jsona['groupOp'];
                $rules = $jsona['rules'];
            }
        } else if (jqGridUtils::GetParam($this->GridParams['searchField'], '')) {
            $gopr = '';
            $rules[0]['field'] = jqGridUtils::GetParam($this->GridParams['searchField'], '');
            $rules[0]['op'] = jqGridUtils::GetParam($this->GridParams['searchOper'], '');
            $rules[0]['data'] = jqGridUtils::GetParam($this->GridParams['searchString'], '');
        } $i
                = 0;
        if (!$rules)
            return array('', $prm);
        if (!is_array($prm))
            $prm = array();
        foreach ($rules as $key => $val) {
            $field = $val['field'];
            $op = $val['op'];
            $v = $val['data'];
            if (strlen($v) != 0 && $op) {
                if (in_array($field, $this->datearray)) {
                    $v = jqGridUtils::parseDate($this->userdateformat, $v, $this->dbdateformat);
                } $i
                        ++;
                $s .= $i == 1 ? "" : " " . $gopr . " ";
                switch ($op) {
                    case 'bw': case 'bn': $s .= $field . $sopt[$op] . " ?";
                        $prm[] = "$v%";
                        break;
                    case 'ew': case 'en': $s .= $field . $sopt[$op] . " ?";
                        $prm[] = "%$v";
                        break;
                    case 'cn': case 'nc': $s .= $field . $sopt[$op] . " ?";
                        $prm[] = "%$v%";
                        break;
                    case 'in': case 'ni': $s .= $field . $sopt[$op] . "( ?)";
                        $prm[] = $v;
                        break;
                    default : $s .= $field . $sopt[$op] . " ?";
                        $prm[] = $v;
                        break;
                }
            }
        }
        $s
                = $s ? $s1 . $s . " )" : "";
        return array($s, $prm);
    }

    protected function _setSQL() {
        $sqlId = false;
        if ($this->readFromXML == true && strlen($this->SelectCommand) > 0) {
            $sqlId = $this->getSqlElement($this->SelectCommand);
        } else if ($this->SelectCommand && strlen($this->SelectCommand) > 0) {
            $sqlId = $this->SelectCommand;
        } else if ($this->table && strlen($this->table) > 0) {
            $sqlId = "SELECT * FROM " . (string) $this->table;
        } return
        $sqlId;
    }

    public function getUserDate() {
        return $this->userdateformat;
    }

    public function setUserDate($newformat) {
        $this->userdateformat = $newformat;
    }

    public function getUserTime() {
        return $this->usertimeformat;
    }

    public function setUserTime($newformat) {
        $this->usertimeformat = $newformat;
    }

    public function getDbDate() {
        return $this->dbdateformat;
    }

    public
    function setDbDate($newformat) {
        $this->dbdateformat = $newformat;
    }

    public
    function getDbTime() {
        return $this->dbtimeformat;
    }

    public
    function setDbTime($newformat) {
        $this->dbtimeformat = $newformat;
    }

    public
    function getGridParams() {
        return $this->GridParams;
    }

    public
    function setGridParams($_aparams) {
        if (is_array($_aparams) && !empty($_aparams)) {
            $this->GridParams = array_merge($this->GridParams, $_aparams);
        }
    }

    public
    function selectLimit($limsql='', $nrows=-1, $offset=-1, array $params=null, $order='', $sort='') {
        $sql = null;
        $sqlId = strlen($limsql) > 0 ? $limsql : $this->_setSQL();
        if (!$sqlId)
            return false;
        $ret = $this->execute($sqlId, $params, $sql, true, $nrows, $offset, $order, $sort);
        if ($ret === true) {
            $ret = jqGridDB::fetch_object($sql, true, $this->pdo);
            jqGridDB::closeCursor($sql);
            return $ret;
        } else
            return $ret;
    }

    public
    function queryGrid(array $summary=null, array $params=null, $echo=true) {
        $sql = null;
        $sqlId = $this->_setSQL();
        if (!$sqlId)
            return false;
        $page = $this->GridParams['page'];
        $page = (int) jqGridUtils::GetParam($page, '1');
        $limit = $this->GridParams['rows'];
        $limit = (int) jqGridUtils::GetParam($limit, '20');
        $sidx = $this->GridParams['sort'];
        $sidx = jqGridUtils::GetParam($sidx, '');
        $sord = $this->GridParams['order'];
        $sord = jqGridUtils::GetParam($sord, '');
        $search = $this->GridParams['search'];
        $search = jqGridUtils::GetParam($search, 'false');
        $totalrows = jqGridUtils::GetParam($this->GridParams['totalrows'], '');
        $sord = preg_replace("/[^a-zA-Z0-9]/", "", $sord);
        $sidx = preg_replace("/[^a-zA-Z0-9. _,]/", "", $sidx);
        $performcount = true;
        $gridcnt = false;
        $gridsrearch = '1';
        if ($this->cacheCount) {
            $gridcnt = jqGridUtils::GetParam('grid_recs', false);
            $gridsrearch = jqGridUtils::GetParam('grid_search', '1');
            if ($gridcnt && (int) $gridcnt >= 0)
                $performcount = false;
        } if
        ($search == 'true') {
            $sGrid = $this->_buildSearch($sqlId, $params);
            if ($this->optimizeSearch === true) {
                $whr = "";
                if ($sGrid[0]) {
                    if (preg_match("/WHERE/i", $sqlId))
                        $whr = " AND " . $sGrid[0]; else
                        $whr = " WHERE " . $sGrid[0];
                } $sqlId
                        .= $whr;
            } else {
                $whr = $sGrid[0] ? " WHERE " . $sGrid[0] : "";
                $sqlId = "SELECT * FROM (" . $sqlId . ") gridsearch" . $whr;
            } $params
                    = $sGrid[1];
            if ($this->cacheCount) {
                $tmps = crc32($whr . "data" . implode(" ", $params));
                if ($gridsrearch != $tmps) {
                    $performcount = true;
                } $gridsrearch
                        = $tmps;
            }
        } else {
            if ($this->cacheCount) {
                if ($gridsrearch != '1') {
                    $performcount = true;
                }
            }
        }
        if
        ($performcount) {
            $qryData = $this->_getcount($sqlId, $params, $summary);
            if (!isset($qryData->count))
                $qryData->count = null;
            if (!isset($qryData->COUNT))
                $qryData->COUNT = null;
            $count = $qryData->COUNT ? $qryData->COUNT : ($qryData->count ? $qryData->count : 0);
        } else {
            $count = $gridcnt;
        } if
        ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
            $page = 0;
        } if
        ($page > $total_pages)
            $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0)
            $start = 0;
        if ($this->dbtype == 'sqlsrv') {
            $difrec = abs($start - $count);
            if ($difrec < $limit) {
                $limit = $difrec;
            }
        }
        if
        (is_array($summary)) {
            unset($qryData->COUNT, $qryData->count);
            foreach ($qryData as $k => $v) {
                if ($v == null)
                    $v = 0;
                $result->userdata[$k] = $v;
            }
        }
        if
        ($this->cacheCount) {
            $result->userdata['grid_recs'] = $count;
            $result->userdata['grid_search'] = $gridsrearch;
            $result->userdata['outres'] = $performcount;
        } if
        ($this->userdata) {
            if (!isset($result->userdata))
                $result->userdata = array();
            $result->userdata = jqGridUtils::array_extend($result->userdata, $this->userdata);
        } $result
                ->records = $count;
        $result->page = $page;
        $result->total = $total_pages;
        $uselimit = true;
        if ($totalrows) {
            $totalrows = (int) $totalrows;
            if (is_int($totalrows)) {
                if ($totalrows == -1) {
                    $uselimit = false;
                } else if ($totalrows > 0) {
                    $limit = $totalrows;
                }
            }
        }
        if
        ($sidx)
            $sqlId .= " ORDER BY " . $sidx . " " . $sord;
        $ret = $this->execute($sqlId, $params, $sql, $uselimit, $limit, $start, $sidx, $sord);
        if ($ret === true) {
            $result->rows = jqGridDB::fetch_object($sql, true, $this->pdo);
            jqGridDB::closeCursor($sql);
            if (function_exists($this->customFunc))
                $result = call_user_func($this->customFunc, $result, $this->pdo);
            if ($echo) {
                $this->_gridResponse($result);
            } else {
                return $result;
            }
        } else {
            echo "Could not execute query!!!";
        } if
        ($this->debug)
            $this->debugout();
    }

    public
    function exportToExcel(array $summary=null, array $params=null, array $colmodel=null, $echo = true, $filename='exportdata.xml') {
        $sql = null;
        if ($this->ExportCommand && strlen($this->ExportCommand) > 0)
            $sqlId = $this->ExportCommand; else
            $sqlId = $this->_setSQL();
        if (!$sqlId)
            return false;
        $sidx = $this->GridParams['sort'];
        $sidx = jqGridUtils::GetParam($sidx, '');
        $sord = $this->GridParams['order'];
        $sord = jqGridUtils::GetParam($sord, '');
        $search = $this->GridParams['search'];
        $search = jqGridUtils::GetParam($search, 'false');
        $sord = preg_replace("/[^a-zA-Z0-9]/", "", $sord);
        $sidx = preg_replace("/[^a-zA-Z0-9. _,]/", "", $sidx);
        if ($search == 'true') {
            $sGrid = $this->_buildSearch($sqlId, $params);
            $whr = $sGrid[0] ? " WHERE " . $sGrid[0] : "";
            $sqlId = "SELECT * FROM (" . $sqlId . ") gridsearch" . $whr;
            $params = $sGrid[1];
        } if
        ($sidx)
            $sqlId .= " ORDER BY " . $sidx . " " . $sord;
        $ret = $this->execute($sqlId, $params, $sql, true, $this->gSQLMaxRows, 0, $sidx, $sord);
        if ($ret === true) {
            $ret = $this->rs2excel($sql, $colmodel, $echo, $filename, $summary);
            jqGridDB::closeCursor($sql);
            return $ret;
        } else
            return "Error:Could not execute the query";
    }

    public
    function querySubGrid($params, $echo=true) {
        if ($this->SubgridCommand && strlen($this->SubgridCommand) > 0) {
            $result->rows = $this->queryForObject($this->SubgridCommand, $params, true);
            if ($echo)
                $this->_gridResponse($result); else
                return $result;
        }
    }

    protected
    function _gridResponse($response) {
        if ($this->dataType == "xml") {
            if (isset($response->records)) {
                $response->rows["records"] = $response->records;
                unset($response->records);
            } if
            (isset($response->total)) {
                $response->rows["total"] = $response->total;
                unset($response->total);
            } if
            (isset($response->page)) {
                $response->rows["page"] = $response->page;
                unset($response->page);
            } if
            (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml;charset=", $this->encoding);
            } else {
                header("Content-type: text/xml;charset=" . $this->encoding);
            } echo
            jqGridUtils::toXml($response, 'root', null, $this->encoding, $this->xmlCDATA);
        } else if ($this->dataType == "json") {
            header("Content-type: text/x-json;charset=" . $this->encoding);
            if (function_exists('json_encode') && strtolower($this->encoding) == 'utf-8') {
                echo json_encode($response);
            } else {
                echo jqGridUtils::encode($response);
            }
        }
    }

    protected
    function rs2excel($rs, $colmodel=false, $echo = true, $filename='exportdata.xls', $summary=false) {
        $s = '';
        $rows = 0;
        $gSQLMaxRows = $this->gSQLMaxRows;
        if (!$rs) {
            printf('Bad Record set rs2excel');
            return false;
        } $typearr
                = array();
        $ncols = jqGridDB::columnCount($rs);
        $hdr = '<?xml version="1.0" encoding="' . $this->encoding . '"?>';
        $hdr .='<?mso-application progid="Excel.Sheet"?>';
        $hdr .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        $hdr .= '<ss:Styles>' . '<ss:Style ss:ID="1"><ss:Font ss:Bold="1"/></ss:Style>' . '<ss:Style ss:ID="sd"><NumberFormat ss:Format="Short Date"/></ss:Style>' . '<ss:Style ss:ID="ld"><NumberFormat ss:Format="General Date"/></ss:Style>' . '<ss:Style ss:ID="nmb"><NumberFormat ss:Format="General Number"/></ss:Style>' . '</ss:Styles>';
        $hdr .= '<ss:Worksheet ss:Name="Sheet1">';
        $hdr .= '<ss:Table>';
        $model = false;
        if ($colmodel && is_array($colmodel) && count($colmodel) == $ncols) {
            $model = true;
        } $hdr1
                = '<ss:Row ss:StyleID="1">';
        $aSum = array();
        $aFormula = array();
        $ahidden = array();
        $aselect = array();
        $hiddencount = 0;
        for ($i = 0; $i < $ncols; $i++) {
            $ahidden[$i] = ($model && isset($colmodel[$i]["hidden"])) ? $colmodel[$i]["hidden"] : false;
            $aselect[$i] = false;
            if ($model && isset($colmodel[$i]["formatter"])) {
                if ($colmodel[$i]["formatter"] == "select") {
                    $asl = isset($colmodel[$i]["formatoptions"]) ? $colmodel[$i]["formatoptions"] : $colmodel[$i]["editoptions"];
                    if (isset($asl["value"]))
                        $aselect[$i] = $asl["value"];
                }
            }
            if
            ($ahidden[$i]) {
                $hiddencount++;
                continue;
            } $column
                    = ($model && isset($colmodel[$i]["width"])) ? (int) $colmodel[$i]["width"] : 0;
            if ($column > 0) {
                $column = $column * 72 / 96;
                $hdr .= '<ss:Column ss:Width="' . $column . '"/>';
            } else
                $hdr .= '<ss:Column ss:AutoFitWidth="1"/>';
            $field = array();
            if ($model) {
                $fname = isset($colmodel[$i]["label"]) ? $colmodel[$i]["label"] : $colmodel[$i]["name"];
                $field["name"] = $colmodel[$i]["name"];
                $typearr[$i] = isset($colmodel[$i]["sorttype"]) ? $colmodel[$i]["sorttype"] : '';
            } else {
                $field = jqGridDB::getColumnMeta($i, $rs);
                $fname = $field["name"];
                $typearr[$i] = jqGridDB::MetaType($field, $this->dbtype);
            } if
            ($summary && is_array($summary)) {
                foreach ($summary as $key => $val) {
                    if (is_array($val)) {
                        foreach ($val as $fld => $formula) {
                            if ($field["name"] == $key) {
                                $aSum[] = $i - $hiddencount;
                                $aFormula[] = $formula;
                            }
                        }
                    } else {
                        if ($field["name"] == $key) {
                            $aSum[] = $i - $hiddencount;
                            $aFormula[] = "SUM";
                        }
                    }
                }
            }
            $fname
                    = htmlspecialchars($fname);
            if (strlen($fname) == 0)
                $fname = '';
            $hdr1 .= '<ss:Cell><ss:Data ss:Type="String">' . $fname . '</ss:Data></ss:Cell>';
        } $hdr1
                .= '</ss:Row>';
        if (!$echo)
            $html = $hdr . $hdr1;
        if ($this->dbtype == 'mysqli') {
            $fld = $rs->field_count;
            $count = 1;
            $fieldnames[0] = &$rs;
            for ($i = 0; $i < $fld; $i++) {
                $fieldnames[$i + 1] = &$res_arr[$i];
            } call_user_func_array
                    ('mysqli_stmt_bind_result', $fieldnames);
        } while ($r = jqGridDB::fetch_num($rs)) {
            if ($this->dbtype == 'mysqli')
                $r = $res_arr;
            $s .= '<ss:Row>';
            for ($i = 0; $i < $ncols; $i++) {
                if (isset($ahidden[$i]) && $ahidden[$i])
                    continue;
                $v = $r[$i];
                if (is_array($aselect[$i])) {
                    if (isset($aselect[$i][$v])) {
                        $v1 = $aselect[$i][$v];
                        if ($v1)
                            $v = $v1;
                    } $typearr
                            [$i] = 'string';
                } $type
                        = $typearr[$i];
                switch ($type) {
                    case 'date': if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL') {
                            $v = '1899-12-31T00:00:00.000';
                            $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                        } else if (!strpos($v, ':')) {
                            $v .= "T00:00:00.000";
                            $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                        } else {
                            $thous = substr($v, -4);
                            if (strpos($thous, ".") === false)
                                $v .= ".000";
                            $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                        } break
                        ;
                    case 'datetime': if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL') {
                            $v = '1899-12-31T00:00:00.000';
                            $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                        } else {
                            $thous = substr($v, -4);
                            if (strpos($thous, ".") === false)
                                $v .= ".000";
                            $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                        } break
                        ;
                    case 'numeric': case 'int': $s .= '<ss:Cell ss:StyleID="nmb"><ss:Data ss:Type="Number">' . stripslashes((trim($v))) . '</ss:Data></ss:Cell>';
                        break;
                    default: $v = htmlspecialchars(trim($v));
                        if (strlen($v) == 0)
                            $v = '';
                        $s .= '<ss:Cell><ss:Data ss:Type="String">' . stripslashes($v) . '</ss:Data></ss:Cell>';
                }
            }
            $s
                    .= '</ss:Row>';
            $rows += 1;
            if ($rows >= $gSQLMaxRows) {
                break;
            }
        }
        if
        (count($aSum) > 0 && $rows > 0) {
            $s .= '<Row>';
            foreach ($aSum as $ind => $ival) {
                $s .= '<Cell ss:StyleID="1" ss:Index="' . ($ival + 1) . '" ss:Formula="=' . $aFormula[$ind] . '(R[-' . ($rows) . ']C:R[-1]C)"><Data ss:Type="Number"></Data></Cell>';
            } $s
                    .= '</Row>';
        } if
        ($echo) {
            header('Content-Type: application/ms-excel;');
            header("Content-Disposition: attachment; filename=" . $filename);
            echo $hdr . $hdr1;
            echo $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
        } else {
            $html .= $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
            return $html;
        }
    }

    public
    function addUserData($adata) {
        if (is_array($adata))
            $this->userdata = $adata;
    }

}

class Jqgridedit extends Jqgrid {

    protected $fields = array();
    protected $primaryKey;
    public $serialKey = true;
    protected $buildfields = false;
    public $trans = true;
    public $add = true;
    public $edit = true;
    public $del = true;
    public $mtype = "POST";
    public $decodeinput = false;

    public function getPrimaryKeyId() {
        return $this->primaryKey;
    }

    public
    function setPrimaryKeyId($keyid) {
        $this->primaryKey = $keyid;
    }

    public
    function setTable($_newtable) {
        $this->table = $_newtable;
    }

    protected
    function _buildFields() {
        if ($this->table) {
            if ($this->buildfields)
                return true;
            $wh = ($this->dbtype == 'sqlite') ? "" : " WHERE 1=2";
            $sql = "SELECT * FROM " . $this->table . $wh;
            if ($this->debug)
                $this->logQuery($sql);
            $select = jqGridDB::query($this->pdo, $sql);
            if ($select) {
                $colcount = jqGridDB::columnCount($select);
                $rev = array();
                for ($i = 0; $i < $colcount; $i++) {
                    $meta = jqGridDB::getColumnMeta($i, $select);
                    $type = jqGridDB::MetaType($meta, $this->dbtype);
                    $this->fields[$meta['name']] = array('type' => $type);
                } jqGridDB
                        ::closeCursor($select);
                $this->buildfields = true;
                return true;
            } else {
                return false;
            }
        }
        return
        false;
    }

    public
    function getFields() {
        return $this->fields;
    }

    public
    function insert($data) {
        if (!$this->add)
            return false;
        if (!$this->_buildFields()) {
            die("Could not insert - fields can not be build");
        } $datefmt
                = $this->userdateformat;
        $timefmt = $this->usertimeformat;
        if ($this->serialKey)
            unset($data[$this->getPrimaryKeyId()]);
        $tableFields = array_keys($this->fields);
        $rowFields = array_intersect($tableFields, array_keys($data));
        $insertFields = array();
        $binds = array();
        $types = array();
        $v = '';
        foreach ($rowFields as $key => $val) {
            $insertFields[] = "?";
            $t = $this->fields[$val]["type"];
            $value = $data[$val];
            switch ($t) {
                case 'date': $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($datefmt, $value, $this->dbdateformat) : $value;
                    break;
                case 'datetime' : $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($timefmt, $value, $this->dbtimeformat) : $value;
                    break;
                case 'time': $v = jqGridUtils::parseDate($timefmt, $value, 'H:i:s');
                    break;
                default : $v = $value;
            } if
            ($this->decodeinput)
                $v = htmlspecialchars_decode($v);
            $types[] = $t;
            $binds[] = $v;
            unset($v);
        } $result
                = false;
        if (count($insertFields) > 0) {
            $sql = "INSERT INTO " . $this->table . " (" . implode(', ', $rowFields) . ")" . " VALUES( " . implode(', ', $insertFields) . ")";
            if ($this->debug)
                $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
            $stmt = $this->parseSql($sql, $binds, false);
            if ($stmt) {
                jqGridDB::bindValues($stmt, $binds, $types);
                if ($this->trans) {
                    jqGridDB::beginTransaction($this->pdo);
                    $test = jqGridDB::execute($stmt, $binds);
                    $result = true;
                    if ($test && $result) {
                        $result = jqGridDB::commit($this->pdo);
                    } else {
                        jqGridDB::rollBack($this->pdo);
                        $result = false;
                    }
                } else {
                    $result = jqGridDB::execute($stmt, $binds);
                } jqGridDB
                        ::closeCursor($stmt);
            } else {
                $result = false;
            }
        } else {
            $result = false;
        } if
        ($this->debug)
            $this->debugout();
        return $result;
    }

    public
    function update($data) {
        if (!$this->edit)
            return false;
        if (!$this->_buildFields()) {
            die("Could not update - fields can not be build");
        } $datefmt
                = $this->userdateformat;
        $timefmt = $this->usertimeformat;
        $custom = false;
        $tableFields = array_keys($this->fields);
        $rowFields = array_intersect($tableFields, array_keys($data));
        $updateFields = array();
        $binds = array();
        $types = array();
        $pk = $this->getPrimaryKeyId();
        foreach ($rowFields as $key => $field) {
            $t = $this->fields[$field]["type"];
            $value = $data[$field];
            switch ($t) {
                case 'date': $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($datefmt, $value, $this->dbdateformat) : $value;
                    break;
                case 'datetime' : $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($timefmt, $value, $this->dbtimeformat) : $value;
                    break;
                case 'time': $v = jqGridUtils::parseDate($timefmt, $value, 'H:i:s');
                    break;
                default : $v = $value;
            } if
            ($this->decodeinput)
                $v = htmlspecialchars_decode($v);
            if ($field != $pk) {
                $updateFields[] = $field . " = ?";
                $binds[] = $v;
                $types[] = $t;
            } else if ($field == $pk) {
                $v2 = $v;
                $t2 = $t;
            } unset
                    ($v);
        } if
        (!isset($v2))
            die("Primary value is missing");
        $binds[] = $v2;
        $types[] = $t2;
        $result = false;
        if (count($updateFields) > 0) {
            $sql = "UPDATE " . $this->table . " SET " . implode(', ', $updateFields) . " WHERE " . $pk . " = ?";
            $stmt = $this->parseSql($sql, $binds, false);
            if ($this->debug)
                $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
            if ($stmt) {
                jqGridDB::bindValues($stmt, $binds, $types);
                if ($this->trans) {
                    jqGridDB::beginTransaction($this->pdo);
                    $test = jqGridDB::execute($stmt, $binds);
                    if ($test) {
                        $result = jqGridDB::commit($this->pdo);
                        jqGridDB::closeCursor($stmt);
                    } else {
                        jqGridDB::rollBack($this->pdo);
                        $result = false;
                    }
                } else {
                    $result = jqGridDB::execute($stmt, $binds);
                    jqGridDB::closeCursor($stmt);
                }
            }
        }
        if
        ($this->debug)
            $this->debugout();
        return $result;
    }

    public
    function delete(array $data) {
        $result = false;
        if (!$this->del)
            return $result;
        $ide = null;
        $binds = array(&$ide);
        $types = array();
        if (count($data) > 0) {
            $id = $this->getPrimaryKeyId();
            if (!isset($data[$id]))
                return $result;
            $sql = "DELETE FROM " . $this->table . " WHERE " . $id . "=?";
            $stmt = $this->parseSql($sql, $binds, false);
            $delids = explode(",", $data[$id]);
            $types[0] = 'custom';
            if ($stmt) {
                if ($this->trans) {
                    jqGridDB::beginTransaction($this->pdo);
                    foreach ($delids as $i => $ide) {
                        $delids[$i] = trim($delids[$i]);
                        $binds[0] = &$delids[$i];
                        if ($this->debug)
                            $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                        jqGridDB::bindValues($stmt, $binds, $types);
                        $test = jqGridDB::execute($stmt, $binds);
                        if (!$test) {
                            jqGridDB::rollBack($this->pdo);
                            break;
                            $result = false;
                        } unset
                                ($binds[0]);
                    } if
                    ($test)
                        $result = jqGridDB::commit($this->pdo);
                } else {
                    foreach ($delids as $i => $ide) {
                        $delids[$i] = trim($delids[$i]);
                        $binds[0] = &$delids[$i];
                        if ($this->debug)
                            $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                        jqGridDB::bindValues($stmt, $binds, $types);
                        $test = jqGridDB::execute($stmt, $binds);
                        if (!$test) {
                            break;
                            $result = false;
                        } unset
                                ($binds[0]);
                    } $result
                            = true;
                } jqGridDB
                        ::closeCursor($stmt);
            }
        }
        if
        ($this->debug)
            $this->debugout();
        return $result;
    }

    public
    function editGrid(array $summary=null, array $params=null, $oper=false) {
        if (!$oper) {
            $oper = $this->GridParams["oper"];
            $oper = jqGridUtils::GetParam($oper, "grid");
        } switch
        ($oper) {
            case $this->GridParams["editoper"] : if (strlen($this->table) > 0 && !$this->primaryKey) {
                    $this->primaryKey = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                    if (!$this->primaryKey)
                        die("could not determine primary key");
                } $data
                        = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                $this->update($data);
                break;
            case $this->GridParams["addoper"] : if (strlen($this->table) > 0 && !$this->primaryKey) {
                    $this->primaryKey = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                    if (!$this->primaryKey)
                        die("could not determine primary key");
                } $data
                        = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                $this->insert($data);
                break;
            case $this->GridParams["deloper"] : if (strlen($this->table) > 0 && !$this->primaryKey) {
                    $this->primaryKey = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                    if (!$this->primaryKey)
                        die("could not determine primary key");
                } $data
                        = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                $this->delete($data);
                break;
            default : $this->queryGrid($summary, $params);
        }
    }

}

class Jqgridrender extends Jqgridedit {

    protected $gridOptions = array("width" => "650", "hoverrows" => false, "viewrecords" => true, "jsonReader" => array("repeatitems" => false, "subgrid" => array("repeatitems" => false)), "xmlReader" => array("repeatitems" => false, "subgrid" => array("repeatitems" => false)), "gridview" => true);
    public $navigator = false;
    public $toolbarfilter = false;
    public $export = true;
    public $exportfile = 'exportdata.xml';
    public $history = false;
    protected $navOptions = array("edit" => true, "add" => true, "del" => true, "search" => true, "refresh" => true, "view" => false, "excel" => true);
    protected $editOptions = array("drag" => true, "resize" => true, "closeOnEscape" => true, "dataheight" => 150);
    protected $addOptions = array("drag" => true, "resize" => true, "closeOnEscape" => true, "dataheight" => 150);
    protected $viewOptions = array("drag" => true, "resize" => true, "closeOnEscape" => true, "dataheight" => 150);
    protected $delOptions = array();
    protected $searchOptions = array("drag" => true, "closeAfterSearch" => true, "multipleSearch" => true);
    protected $filterOptions = array("stringResult" => true);
    protected $historyOptions = array();
    protected $colModel = array();
    protected $runSetCommands = true;
    protected $gridMethods = array();
    protected $customCode = "";

    public function getColModel() {
        return $this->colModel;
    }

    public
    function getGridOption($key) {
        if (array_key_exists($key, $this->gridOptions))
            return $this->gridOptions[$key]; else
            return false;
    }

    public
    function setGridOptions($aoptions) {
        if ($this->runSetCommands) {
            if (is_array($aoptions))
                $this->gridOptions = jqGridUtils::array_extend($this->gridOptions, $aoptions);
        }
    }

    public
    function setUrl($newurl) {
        if (!$this->runSetCommands)
            return false;
        if (strlen($newurl) > 0) {
            $this->setGridOptions(array("url" => $newurl, "editurl" => $newurl, "cellurl" => $newurl));
            return true;
        } return
        false;
    }

    public
    function setSubGrid($suburl='', $subnames=false, $subwidth=false, $subalign=false, $subparams=false) {
        if (!$this->runSetCommands)
            return false;
        if ($subnames && is_array($subnames)) {
            $scount = count($subnames);
            for ($i = 0; $i < $scount; $i++) {
                if (!isset($subwidth[$i]))
                    $subwidth[$i] = 100;
                if (!isset($subalign[$i]))
                    $subalign[$i] = 'center';
            } $this
                    ->setGridOptions(array("gridview" => false, "subGrid" => true, "subGridUrl" => $suburl, "subGridModel" => array(array("name" => $subnames, "width" => $subwidth, "align" => $subalign, "params" => $subparams))));
            return true;
        } return
        false;
    }

    public
    function setSubGridGrid($subgridurl, $subgridnames=null) {
        if (!$this->runSetCommands)
            return false;
        $this->setGridOptions(array("subGrid" => true, "gridview" => false));
        $setval = (is_array($subgridnames) && count($subgridnames) > 0 ) ? 'true' : 'false';
        if ($setval == 'true') {
            $anames = implode(",", $subgridnames);
        } else {
            $anames = '';
        } $subgr
                = <<<SUBGRID
function(subgridid,id)
{
	var data = {subgrid:subgridid, rowid:id};
	if('$setval' == 'true') {
		var anm= '$anames';
		anm = anm.split(",");
		var rd = jQuery(this).jqGrid('getRowData', id);
		if(rd) {
			for(var i=0; i<anm.length; i++) {
				if(rd[anm[i]]) {
					data[anm[i]] = rd[anm[i]];
				}
			}
		}
	}
    $("#"+jQuery.jgrid.jqID(subgridid)).load('$subgridurl',data);
}
SUBGRID;
        $this->setGridEvent('subGridRowExpanded', $subgr);
        return true;
    }

    public
    function setSelect($colname, $data, $formatter=true, $editing=true, $seraching=true, $defvals=array()) {
        $s1 = array();
        $prop = array();
        $oper = $this->GridParams["oper"];
        $goper = jqGridUtils::GetParam($oper, 'nooper');
        if (($goper == 'nooper' || $goper == $this->GridParams["excel"]))
            $runme = true; else
            $runme = !in_array($goper, array_values($this->GridParams));
        if (!$this->runSetCommands && !$runme)
            return false;
        if (count($this->colModel) > 0 && $runme) {
            if (is_string($data)) {
                $aset = jqGridDB::query($this->pdo, $data);
                if ($aset) {
                    while ($row = jqGridDB::fetch_num($aset)) {
                        $s1[$row[0]] = $row[1];
                    } jqGridDB
                            ::closeCursor($aset);
                }
            } else if (is_array($data)) {
                $s1 = $data;
            } if
            ($editing) {
                $prop = array_merge($prop, array('edittype' => 'select', 'editoptions' => array('value' => $s1)));
            } if
            ($formatter) {
                $prop = array_merge($prop, array('formatter' => 'select', 'editoptions' => array('value' => $s1)));
            } if
            ($seraching) {
                if (is_array($defvals) && count($defvals) > 0)
                    $s1 = $defvals + $s1;
                $prop = array_merge($prop, array("stype" => "select", "searchoptions" => array("value" => $s1)));
            } if
            (count($prop) > 0) {
                $this->setColProperty($colname, $prop);
            } return
            true;
        } return
        false;
    }

    public
    function setAutocomplete($colname, $target=false, $data='', $options=null, $editing = true, $searching=false) {
        try {
            $ac = new jqAutocomplete($this->pdo);
            if (is_string($data)) {
                $ac->SelectCommand = $data;
                $url = $this->getGridOption('url');
                if (!$url) {
                    $url = basename(__FILE__);
                } $ac
                        ->setSource($url);
            } else if (is_array($data)) {
                $ac->setSource($data);
            } if
            ($colname) {
                if ($this->runSetCommands) {
                    if (is_array($options) && count($options) > 0) {
                        if (isset($options['cache'])) {
                            $ac->cache = $options['cache'];
                            unset($options['cache']);
                        } if
                        (isset($options['searchType'])) {
                            $ac->searchType = $options['searchType'];
                            unset($options['searchType']);
                        } if
                        (isset($options['ajaxtype'])) {
                            $ac->ajaxtype = $options['ajaxtype'];
                            unset($options['ajaxtype']);
                        } if
                        (isset($options['scroll'])) {
                            $ac->scroll = $options['scroll'];
                            unset($options['scroll']);
                        } if
                        (isset($options['height'])) {
                            $ac->height = $options['height'];
                            unset($options['height']);
                        } if
                        (isset($options['itemLength'])) {
                            $ac->setLength($options['itemLength']);
                            unset($options['itemLength']);
                        } $ac
                                ->setOption($options);
                    } if
                    ($editing) {
                        $script = $ac->renderAutocomplete($colname, $target, false, false);
                        $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                        $script = "setTimeout(function(){" . $script . "},200);";
                        $this->setColProperty($colname, array("editoptions" => array("dataInit" => "js:function(el){" . $script . "}")));
                    } if
                    ($searching) {
                        $script = $ac->renderAutocomplete($colname, false, false, false);
                        $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                        $script = "setTimeout(function(){" . $script . "},100);";
                        $this->setColProperty($colname, array("searchoptions" => array("dataInit" => "js:function(el){" . $script . "}")));
                    }
                } else {
                    $ac->renderAutocomplete($colname, $target, true, true, false);
                }
            }
        } catch (Exception $e) {

        }
    }

    public
    function setDatepicker($colname, $options=null, $editing=true, $searching=true) {
        try {
            if ($colname) {
                if ($this->runSetCommands) {
                    $dp = new jqCalendar();
                    if (isset($options['buttonIcon'])) {
                        $dp->buttonIcon = $options['buttonIcon'];
                        unset($options['buttonIcon']);
                    } if
                    (isset($options['buttonOnly'])) {
                        $dp->buttonOnly = $options['buttonOnly'];
                        unset($options['buttonOnly']);
                    } if
                    (is_array($optins) && count($options) > 0) {
                        $dp->setOption($options);
                    } $ud
                            = $this->getUserDate();
                    $ud = jqGridUtils::phpTojsDate($ud);
                    $dp->setOption('dateFormat', $ud);
                    $script = $dp->renderCalendar($colname, false, false);
                    $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                    $script = "setTimeout(function(){" . $script . "},100);";
                    if ($editing) {
                        $this->setColProperty($colname, array("editoptions" => array("dataInit" => "js:function(el){" . $script . "}")));
                    } if
                    ($searching) {
                        $this->setColProperty($colname, array("searchoptions" => array("dataInit" => "js:function(el){" . $script . "}")));
                    }
                }
            }
        } catch (Exception $e) {

        }
    }

    public
    function setGridEvent($event, $code) {
        if (!$this->runSetCommands)
            return false;
        $this->gridOptions[$event] = "js:" . $code;
        return true;
    }

    public
    function setNavOptions($module, $aoptions) {
        $ret = false;
        if (!$this->runSetCommands)
            return $ret;
        switch ($module) {
            case 'navigator' : $this->navOptions = array_merge($this->navOptions, $aoptions);
                $ret = true;
                break;
            case 'add' : $this->addOptions = array_merge($this->addOptions, $aoptions);
                $ret = true;
                break;
            case 'edit' : $this->editOptions = array_merge($this->editOptions, $aoptions);
                $ret = true;
                break;
            case 'del' : $this->delOptions = array_merge($this->delOptions, $aoptions);
                $ret = true;
                break;
            case 'search' : $this->searchOptions = array_merge($this->searchOptions, $aoptions);
                $ret = true;
                break;
            case 'view' : $this->viewOptions = array_merge($this->viewOptions, $aoptions);
                $ret = true;
                break;
        } return
        $ret;
    }

    public
    function setNavEvent($module, $event, $code) {
        $ret = false;
        if (!$this->runSetCommands)
            return $ret;
        switch ($module) {
            case 'navigator' : $this->navOptions[$event] = "js:" . $code;
                $ret = true;
                break;
            case 'add' : $this->addOptions[$event] = "js:" . $code;
                $ret = true;
                break;
            case 'edit' : $this->editOptions[$event] = "js:" . $code;
                $ret = true;
                break;
            case 'del' : $this->delOptions[$event] = "js:" . $code;
                $ret = true;
                break;
            case 'search' : $this->searchOptions[$event] = "js:" . $code;
                $ret = true;
                break;
            case 'view' : $this->viewOptions[$event] = "js:" . $code;
                $ret = true;
                break;
        } return
        $ret;
    }

    public
    function setFilterOptions($aoptions) {
        if ($this->runSetCommands) {
            if (is_array($aoptions))
                $this->filterOptions = jqGridUtils::array_extend($this->filterOptions, $aoptions);
        }
    }

    public
    function callGridMethod($grid, $method, array $aoptions=null) {
        if ($this->runSetCommands) {
            $prm = '';
            if (is_array($aoptions) && count($aoptions) > 0) {
                $prm = jqGridUtils::encode($aoptions);
                $prm = substr($prm, 1);
                $prm = substr($prm, 0, -1);
                $prm = "," . $prm;
            } $this
                    ->gridMethods[] = "jQuery('" . $grid . "').jqGrid('" . $method . "'" . $prm . ");";
        }
    }

    public
    function setJSCode($code) {
        if ($this->runSetCommands) {
            $this->customCode = "js:" . $code;
        }
    }

    public
    function setColModel(array $model=null, array $params=null, array $labels=null) {
        $oper = $this->GridParams["oper"];
        $goper = jqGridUtils::GetParam($oper, 'nooper');
        if (($goper == 'nooper' || $goper == $this->GridParams["excel"]))
            $runme = true; else
            $runme = !in_array($goper, array_values($this->GridParams));
        if ($runme) {
            if (is_array($model) && count($model) > 0) {
                $this->colModel = $model;
                return true;
            } $sql
                    = null;
            $sqlId = $this->_setSQL();
            if (!$sqlId)
                return false;
            $nof = ($this->dbtype == 'sqlite' || $this->dbtype == 'db2') ? 1 : 0;
            $ret = $this->execute($sqlId, $params, $sql, true, $nof, 0);
            if ($ret === true) {
                if (is_array($labels) && count($labels) > 0)
                    $names = true; else
                    $names = false;
                $colcount = jqGridDB::columnCount($sql);
                for ($i = 0; $i < $colcount; $i++) {
                    $meta = jqGridDB::getColumnMeta($i, $sql);
                    if (strtolower($meta['name']) == 'jqgrid_row')
                        continue;
                    if ($names && array_key_exists($meta['name'], $labels))
                        $this->colModel[] = array('label' => $labels[$meta['name']], 'name' => $meta['name'], 'index' => $meta['name'], 'editable' => true, 'sorttype' => jqGridDB::MetaType($meta, $this->dbtype)); else
                        $this->colModel[] = array('name' => $meta['name'], 'index' => $meta['name'], 'editable' => true, 'sorttype' => jqGridDB::MetaType($meta, $this->dbtype));
                } jqGridDB
                        ::closeCursor($sql);
                if ($this->primaryKey)
                    $pk = $this->primaryKey; else {
                    $pk = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                    $this->primaryKey = $pk;
                } if
                ($pk) {
                    $this->setColProperty($pk, array("key" => true));
                } else {
                    $this->colModel[0] = array_merge($this->colModel[0], array("key" => true));
                }
            } else {
                return false;
            }
        }
        if
        ($goper == $this->GridParams["excel"]) {
            $this->runSetCommands = false;
        } else if (!$runme) {
            $this->runSetCommands = false;
        } return
        true;
    }

    public
    function setColProperty($colname, array $aproperties) {
        $ret = false;
        if (!is_array($aproperties))
            return $ret;
        if (count($this->colModel) > 0) {
            if (is_int($colname)) {
                $this->colModel[$colname] = jqGridUtils::array_extend($this->colModel[$colname], $aproperties);
                $ret = true;
            } else {
                foreach ($this->colModel as $key => $val) {
                    if ($val['name'] == trim($colname)) {
                        $this->colModel[$key] = jqGridUtils::array_extend($this->colModel[$key], $aproperties);
                        $ret = true;
                        break;
                    }
                }
            }
        }
        return
        $ret;
    }

    public
    function addCol(array $aproperties, $position='last') {
        if (!$this->runSetCommands)
            return false;
        if (is_array($aproperties) && count($aproperties) > 0 && strlen($position)) {
            $cmcnt = count($this->colModel);
            if ($cmcnt > 0) {
                if (strtolower($position) === 'first') {
                    array_unshift($this->colModel, $aproperties);
                } else if (strtolower($position) === 'last') {
                    array_push($this->colModel, $aproperties);
                } else if ((int) $position >= 0 && (int) $position <= $cmcnt - 2) {
                    $a = array_slice($this->colModel, 0, $position + 1);
                    $b = array_slice($this->colModel, $position + 1);
                    array_push($a, $aproperties);
                    $this->colModel = array();
                    $this->colModel = jqGridUtils::array_extend($a, $b);
                } $aproperties
                        = null;
                return true;
            }
        }
        return
        false;
    }

    public
    function renderGrid($tblelement='', $pager='', $script=true, array $summary=null, array $params=null, $createtbl=false, $createpg=false, $echo=true) {
        $oper = $this->GridParams["oper"];
        $goper = jqGridUtils::GetParam($oper, 'nooper');
        if ($goper == $this->GridParams["autocomplete"]) {
            return false;
        } else if ($goper == $this->GridParams["excel"]) {
            if (!$this->export)
                return false;
            $this->exportToExcel($summary, $params, $this->colModel, true, $this->exportfile);
        } else if (in_array($goper, array_values($this->GridParams))) {
            $this->editGrid($summary, $params, $goper);
        } else {
            if (!isset($this->gridOptions["datatype"]))
                $this->gridOptions["datatype"] = $this->dataType;
            $this->gridOptions['colModel'] = $this->colModel;
            if (isset($this->gridOptions['postData']))
                $this->gridOptions['postData'] = jqGridUtils::array_extend($this->gridOptions['postData'], array($oper => $this->GridParams["query"])); else
                $this->setGridOptions(array("postData" => array($oper => $this->GridParams["query"])));
            if (isset($this->primaryKey)) {
                $this->GridParams["id"] = $this->primaryKey;
            } $this
                    ->setGridOptions(array("prmNames" => $this->GridParams));
            $s = '';
            if ($createtbl) {
                $tmptbl = $tblelement;
                if (strpos($tblelement, "#") === false) {
                    $tblelement = "#" . $tblelement;
                } else {
                    $tmptbl = substr($tblelement, 1);
                } $s
                        .= "<table id='" . $tmptbl . "'></table>";
            } if
            ($createpg) {
                $tmppg = $pager;
                if (strpos($pager, "#") === false) {
                    $pager = "#" . $pager;
                } else {
                    $tmppg = substr($pager, 1);
                } $s
                        .= "<div id='" . $tmppg . "'></div>";
            } if
            (strlen($pager) > 0)
                $this->setGridOptions(array("pager" => $pager));
            $this->editOptions['mtype'] = $this->mtype;
            $this->addOptions['mtype'] = $this->mtype;
            $this->delOptions['mtype'] = $this->mtype;
            if ($script) {
                $s .= "<script type='text/javascript'>";
                $s .= "jQuery(document).ready(function() {";
            }
            $s .= "jQuery('" . $tblelement . "').";
            $s .= $this->history ? 'jqGridHistory' : 'jqGrid';
            $s .= "(" . jqGridUtils::encode($this->gridOptions) . ");";
            if ($this->navigator && strlen($pager) > 0) {
                $s .= "jQuery('" . $tblelement . "').jqGrid('navGrid','" . $pager . "'," . jqGridUtils::encode($this->navOptions);
                $s .= "," . jqGridUtils::encode($this->editOptions);
                $s .= "," . jqGridUtils::encode($this->addOptions);
                $s .= "," . jqGridUtils::encode($this->delOptions);
                $s .= "," . jqGridUtils::encode($this->searchOptions);
                $s .= "," . jqGridUtils::encode($this->viewOptions) . ");";
                if ($this->navOptions["excel"] == true) {
                    $eurl = $this->getGridOption('url');
                    $exexcel = <<<EXCELE
onClickButton : function(e)
{
    try {
        jQuery("$tblelement").jqGrid('excelExport',{url:'$eurl'});
    } catch (e) {
        window.location= '$eurl?oper=excel';
    }
}
EXCELE;
                    $s .= "jQuery('" . $tblelement . "').jqGrid('navButtonAdd','" . $pager . "',{caption:'',title:'Export to Excel'," . $exexcel . "});";
                }
            }
            if
            ($this->toolbarfilter) {
                $s .= "jQuery('" . $tblelement . "').jqGrid('filterToolbar'," . jqGridUtils::encode($this->filterOptions) . ");\n";
            } $gM
                    = count($this->gridMethods);
            if ($gM > 0) {
                for ($i = 0; $i < $gM; $i++) {
                    $s .= $this->gridMethods[$i] . "\n";
                }
            }
            if
            (strlen($this->customCode) > 0)
                $s .= jqGridUtils::encode($this->customCode);
            if ($script)
                $s .= " });</script>";
            if ($echo) {
                echo $s;
            } return
            $echo ? "" : $s;
        }
    }

}

class
SimpleXMLExtended extends SimpleXMLElement {

    public function addCData($cdata_text) {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }

}

class
jqGridUtils {

    public static function toXml($data, $rootNodeName = 'root', $xml=null, $encoding='utf-8', $cdata=false) {
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        } if
        ($xml == null) {
            $xml = new SimpleXMLExtended("<?xml version='1.0' encoding='" . $encoding . "'?><$rootNodeName />");
        } foreach
        ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = "row";
            } if
            (is_array($value) || is_object($value)) {
                $node = $xml->addChild($key);
                self::toXml($value, $rootNodeName, $node, $encoding, $cdata);
            } else {
                $value = htmlspecialchars($value);
                if ($cdata === true) {
                    $node = $xml->addChild($key);
                    $node->addCData($value);
                } else {
                    $xml->addChild($key, $value);
                }
            }
        }
        return
        $xml->asXML();
    }

    public
    static function quote($js, $forUrl=false) {
        if ($forUrl)
            return strtr($js, array('%' => '%25', "\t" => '\t', "\n" => '\n', "\r" => '\r', '"' => '\"', '\'' => '\\\'', '\\' => '\\\\')); else
            return strtr($js, array("\t" => '\t', "\n" => '\n', "\r" => '\r', '"' => '\"', '\'' => '\\\'', '\\' => '\\\\', "'" => '\''));
    }

    public
    static function encode($value) {
        if (is_string($value)) {
            if (strpos($value, 'js:') === 0)
                return substr($value, 3); else
                return '"' . self::quote($value) . '"';
        } else if ($value === null)
            return "null"; else if (is_bool($value))
            return $value ? "true" : "false"; else if (is_integer($value))
            return "$value"; else if (is_float($value)) {
            if ($value === -INF)
                return 'Number.NEGATIVE_INFINITY'; else if ($value === INF)
                return 'Number.POSITIVE_INFINITY'; else
                return "$value";
        } else if (is_object($value))
            return self::encode(get_object_vars($value)); else if (is_array($value)) {
            $es = array();
            if (($n = count($value)) > 0 && array_keys($value) !== range(0, $n - 1)) {
                foreach ($value as $k => $v)
                    $es[] = '"' . self::quote($k) . '":' . self::encode($v);
                return "{" . implode(',', $es) . "}";
            } else {
                foreach ($value as $v)
                    $es[] = self::encode($v);
                return "[" . implode(',', $es) . "]";
            }
        }
        else
            return "";
    }

    public
    static function decode($json) {
        $comment = false;
        $out = '$x=';
        for ($i = 0; $i < strlen($json); $i++) {
            if (!$comment) {
                if ($json[$i] == '{')
                    $out .= ' array('; else if ($json[$i] == '}')
                    $out .= ')'; else if ($json[$i] == '[')
                    $out .= ' array('; else if ($json[$i] == ']')
                    $out .= ')'; else if ($json[$i] == ':')
                    $out .= '=>'; else
                    $out .= $json[$i];
            } else
                $out .= $json[$i];
            if ($json[$i] == '"')
                $comment = !$comment;
        } eval
                ($out . ';');
        return $x;
    }

    public
    static function Strip($value) {
        if (get_magic_quotes_gpc() != 0) {
            if (is_array($value))
                if (0 !== count(array_diff_key($value, array_keys(array_keys($value))))) {
                    foreach ($value as $k => $v)
                        $tmp_val[$k] = stripslashes($v);
                    $value = $tmp_val;
                } else
                    for ($j = 0; $j < sizeof($value); $j++)
                        $value[$j] = stripslashes($value[$j]); else
                $value = stripslashes($value);
        } return
        $value;
    }

    public
    static function parseDate($format, $date, $newformat = '') {
        $m = 1;
        $d = 1;
        $y = 1970;
        $h = 0;
        $i = 0;
        $s = 0;
        $format = trim(strtolower($format));
        $date = trim($date);
        $sep = '([\\\/:_;.\s-]{1})';
        $date = preg_split($sep, $date);
        $format = preg_split($sep, $format);
        foreach ($format as $key => $formatDate) {
            if (isset($date[$key])) {
                if (!preg_match('`^([0-9]{1,4})$`', $date[$key])) {
                    return FALSE;
                } $
                        $formatDate = $date[$key];
            }
        }
        $timestamp
                = mktime($h, $i, $s, $m, $d, $y);
        if ($newformat)
            return date($newformat, $timestamp);
        return (integer) $timestamp;
    }

    public
    static function GetParam($parameter_name, $default_value = "") {
        $parameter_value = "";
        if (isset($_POST[$parameter_name]))
            $parameter_value = self::Strip($_POST[$parameter_name]); else if (isset($_GET[$parameter_name]))
            $parameter_value = self::Strip($_GET[$parameter_name]); else
            $parameter_value = $default_value;
        return $parameter_value;
    }

    public
    static function array_extend($a, $b) {
        foreach ($b as $k => $v) {
            if (is_array($v)) {
                if (!isset($a[$k])) {
                    $a[$k] = $v;
                } else {
                    $a[$k] = self::array_extend($a[$k], $v);
                }
            } else {
                $a[$k] = $v;
            }
        }
        return
        $a;
    }

    public
    static function phpTojsDate($phpdate) {
        str_replace('j', 'd', $phpdate);
        str_replace('d', 'dd', $phpdate);
        str_replace('z', 'o', $phpdate);
        str_replace('l', 'DD', $phpdate);
        str_replace('m', 'mm', $phpdate);
        str_replace('n', 'm', $phpdate);
        str_replace('F', 'MM', $phpdate);
        str_replace('Y', 'yy', $phpdate);
    }

}