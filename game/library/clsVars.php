<?php

class clsVars
{
    /**
     * @param $var
     * @return clsVars
     */
    static public function get($var)
    {
        return new self($var, $_GET);
    }

    /**
     * @param $var
     * @return clsVars
     */
    static public function post($var)
    {
        return new self($var, $_POST);
    }

    /**
     * @param $var
     * @return clsVars
     */
    static public function cookie($var)
    {
        return new self($var, $_COOKIE);
    }

    /**
     * @param $var
     * @return clsVars
     */
    static public function server($var)
    {
        return new self($var, $_SERVER);
    }

    /**
     * @param $var
     * @return clsVars
     */
    static public function session($var)
    {
        return new self($var, $_SESSION);
    }

    /**
     * @param $var
     * @return clsVars
     */
    static public function request($var)
    {
        return new self($var, $_REQUEST);
    }

    /**
     * @var array
     */
    static private $_vars = array();

    /**
     * @param $name
     * @param $value
     *
     * @return bool
     */
    static public function set($name, $value)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                self::$_vars[$k] = $v;
            }
        } else {
            self::$_vars[$name] = $value;
        }
        return TRUE;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    static public function fetch($name)
    {
        return isset(self::$_vars[$name]) ? self::$_vars[$name] : NULL;
    }

    /**
     * @params string $name1
     * @params string $name2
     * @params string $name3
     * ....
     * @return array
     */
    static public function multi()
    {
        $result = array();
        foreach (func_get_args() as $name) {
            $result[$name] = self::fetch($name);
        }
        return $result;
    }

    /**
     * @return array
     */
    static public function fetchAll()
    {
        return self::$_vars;
    }

    /**
     * 从参数中得到的源字符串
     * @var null|string|array
     */
    private $_original = NULL;

    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, $data)
    {
        $this->_original = isset($data[$name]) ? $data[$name] : NULL;
    }

    /**
     * 将内容转换成整型
     * @return int
     */
    public function intval()
    {
        return intval($this->_original);
    }

    /**
     * 将内容转换成浮点型
     * @return float
     */
    public function floatval()
    {
        return floatval($this->_original);
    }

    /**
     * 去除前后空格
     * @return string
     */
    public function trim()
    {
        return trim($this->_original);
    }


    /**
     * json字符串转成数组、对象后返回
     * @param bool $assoc : 是否返回数组
     * @return mixed
     */
    public function json_decode($assoc = TRUE)
    {
        return @json_decode(stripslashes($this->_original), $assoc);
    }

    /**
     * 返回原始内容
     * @return array|null|string
     */
    public function original()
    {
        return $this->_original;
    }

    /**
     * 返回原始内容
     * @return array|null|string
     */
    public function value()
    {
        return $this->_original;
    }

    /**
     * 对内容进行切割，并返回切割后的数组
     * @param string $seperator
     * @return array
     */
    public function split($seperator = ',')
    {
        return explode($seperator, $this->_original);
    }

    /**
     * 对内容进行切割，并返回处理后的数值内容数组
     * @param string $seperator
     * @param int $min
     * @param bool $uniq
     * @return array
     */
    public function toIds($seperator = ',', $min = 0, $uniq = FALSE)
    {
        $data = $this->split($seperator);
        $minCheck = !is_null($min);
        if ($minCheck) {
            $min = intval($min);
        }
        foreach ($data as &$v) {
            $v = intval($v);
            if ($minCheck) {
                if ($v < $min) {
                    unset($v);
                }
            }
        }
        if ($uniq) {
            $data = array_unique($data);
        }
        return $data;
    }

    /**
     * 截取字符串（按字符）
     * @param int $len
     * @param int $start
     * @param string $charset
     * @return string
     */
    public function zcut($len, $start = 0, $charset = 'utf-8')
    {
        return \clsTools::cut($this->_original, $len, $start, $charset);
    }

    /**
     * 截取字符串（按字节）
     * @param int $len
     * @param int $start
     * @return string
     */
    public function cut($len, $start = 0)
    {
        return substr($this->_original, $start, $len);
    }

    /**
     * 替换内容并返回结果，允许正则替换
     * @param string $find
     * @param string $repl
     * @return string
     */
    public function replace($find, $repl)
    {
        if ($find{0} == "/") {
            return preg_replace($find, $repl, $this->_original);
        }
        return str_replace($find, $repl, $this->_original);
    }

    /**
     * 正则匹配结果，并输出
     * @param string $reg
     * @param bool $all
     * @return bool|array
     */
    public function match($reg, $all = FALSE)
    {
        $result = FALSE;
        if ($all) {
            if (preg_match_all($reg, $this->_original, $result)) {
                return $result;
            }
        } elseif (preg_match($reg, $this->_original, $result)) {
            return $result;
        }
        return FALSE;
    }

    /**
     * 判断参数值是否是已知数组中的内容
     * @param array $arr
     * @return string
     */
    public function in($arr)
    {
        return in_array($this->_original, $arr) ? $this->_original : FALSE;
    }

    /**
     * 返回参数中所有字母/数字/下划线
     * @return string
     */
    public function letters()
    {
        return $this->replace('/\W+/is', '');
    }

    /**
     * 尚未实现
     * 判断是否是邮件格式，是则返回，否则返回false
     * @return bool|string
     */
    public function toEmail()
    {
        if (TRUE) {
            return '';
        }
        return FALSE;
    }

    /**
     * 尚未实现
     * 判断是否是电话格式，并返回
     * @return bool|string
     */
    public function toPhone()
    {
        if (TRUE) {
            return '';
        }
        return FALSE;
    }

    /**
     * 尚未实现
     * 判断是否是手机格式，并返回
     * @return bool|string
     */
    public function toMobile()
    {
        if (TRUE) {
            return '';
        }
        return FALSE;
    }

    /**
     * 判断是否是身份证，并返回
     * @param bool $age
     * @return bool|string
     */
    public function toIdcard($age = FALSE)
    {
        if (TRUE) {
            return $age;
        }
        return FALSE;
    }

    /**
     * 判断是否是日期格式并输出
     * @param bool $int
     * @return bool|int|string
     */
    public function toDate($int = TRUE)
    {
        if (strtotime($this->_original) === FALSE) {
            return FALSE;
        }
        $date = explode("-", $this->_original);
        if (count($date) != 3) {
            return FALSE;
        }
        $day = sprintf("%04d-%02d-%02d", intval($date[0]), intval($date[1]), intval($date[2]));
        $time = strtotime($day);
        if (date("Y-m-d", $time) != $day) {
            return FALSE;
        }
        return $int ? $time : $day;
    }

    /**
     * 格式化html标签并返回
     * @return string
     */
    public function htmlencode()
    {
        return htmlspecialchars($this->_original);
    }

    /**
     * 过滤html标签
     * @param string|null $tagsAllow
     * @return string
     */
    public function htmlstrip($tagsAllow = NULL)
    {
        return strip_tags($this->_original, $tagsAllow);
    }

    /**
     * 字符转义并返回
     * @return array|null|string
     */
    public function slashes()
    {
        if (get_magic_quotes_gpc()) {
            return $this->_original;
        }
        return addslashes($this->_original);
    }

    /**
     * 把HTML实体转换为字符
     * @param $len : 截取字符个数长度
     * @return string
     */
    public function entitydecode($len = 0)
    {
        $data = stripslashes(trim($this->_original));
        $data = preg_replace_callback('|&#\d{2,5};|', array(&$this, 'utf8EntityDecode'), $data);
        if ($len > 0) {
            $data = \clsTools::cut($data, $len, 0, 'utf-8');
        }
        return addslashes($data);
    }

    /**
     * 单个html实体转换
     * @param $matches
     * @return string
     */
    public function utf8EntityDecode($matches)
    {
        $convmap = array(0x0, 0x10000, 0, 0xfffff);
        return mb_decode_numericentity($matches[0], $convmap, 'UTF-8');
    }
}
