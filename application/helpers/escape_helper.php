<?

/**
 * Javascript Escape
 *
 * Helps escape quotes, backslashes and new lines
 *
 * @param string @string
 * @return string
 */
function javascript_escape($string = ''){
    return strtr($string, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
}