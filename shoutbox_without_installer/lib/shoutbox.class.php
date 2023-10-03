<?php

/**
 * Show all errors except notices
 */
error_reporting(E_ALL  & ~E_NOTICE);


/**
 * Shoutbox
 *
 * @version     1.4
 * @copyright   Copyright (c) 2014 NellyB
 */
class Shoutbox
{

    /**
     * Options array for configuration
     *
     * @var array
     */
    private $_options = array(
        'db_server'         => 'localhost',
        'db_charset'       => 'utf8',
        'db_user'            => '',
        'db_password'     => '',
        'db_name'          => 'shoutbox',
        'db_prefix'          => 'shoutbox_',
        'charset'            => 'utf-8',
        'heading'            => 'Shoutbox-Title',
        'refresh'             => 2.5,
        'spam_protect'    => 1,
        'enable_bbcode'  => true,
        'enable_smilies'   => true
    );
    
    /**
     * The database link holder
     *
     * @var ressource
     */
    private $_db = null;
    
    /**
     * Class constructor. Create a new shoutbox
     *
     * @param array $options
     * @return bool
     */
    public function __construct($options = null)
    {
        // Merge options if they were set
        if (!is_null($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        
        // Disable magic quotes on old PHP versions
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->disableMagicQuotes();
        }
        
        // Connect to database
        $this->connectDatabase();
    }
    
    /**
     * Get the connection to the database
     *
     * @param none
     * @return ressource
     */
    private function connectDatabase()
    {
        // Connect to database and select database
        $this->_db = @mysqli_connect($this->_options['db_server'], $this->_options['db_user'], $this->_options['db_password'], $this->_options['db_name']);
        
        // Check database connection
        if (!$this->_db) {
            throw new Exception('Unable to connect to the database, please check your configuration.');
        }
        
        // Guarantee for right charset
        mysqli_set_charset($this->_db, $this->_options['db_charset']);
    }
    
    /**
     * Disable magic quotes
     *
     * @param none
     * @return none
     */
    private function disableMagicQuotes()
    {
        // Check if magic quotes are enabled and remove if necessary the slashes of the global variable values
        if (get_magic_quotes_gpc()) {
            $_GET    = $this->removeMagicQuotes($_GET);
            $_POST   = $this->removeMagicQuotes($_POST);
            $_SERVER = $this->removeMagicQuotes($_SERVER);
            $_COOKIE = $this->removeMagicQuotes($_COOKIE);
            @ini_set('magic_quotes_gpc', 0);
        }
        
        // Check if the magic quotes runtime is active and remove it if necessary
        if (get_magic_quotes_runtime()) {
            set_magic_quotes_runtime(0);
        }
    }
    
    /**
     * Removes the slashes
     *
     * @param array $data
     * @return array
     */
    private function removeMagicQuotes($data)
    {
        // Pass every element and remove the slashes
        foreach ($data as $key => $value) {
            // If it is an array, pass is again, otherwise remove the slashes
            if (is_array($value)) {
                $data[$key] = $this->removeMagicQuotes($value);
            }
            else {
                $data[$key] = stripslashes($value);
            }
        }
        
        return $data;
    }
    
    /**
     * Insert data into database
     *
     * @param array $rows
     * @param array $data
     * @return void
     */
    public function dbInsert($rows, $data)
    {
        // Create mysql row string
        $mysqlRows = '';
        foreach ($rows as $row) {
            $mysqlRows .= '`' . $row . '`, ';
        }
        // Remove the last ", "
        $mysqlRows = mb_substr($mysqlRows, 0, -2, $this->_options['charset']);
        
        // Create mysql data string
        $mysqlValues = '';
        foreach ($data as $value) {
            $mysqlValues .= '"' . $this->dbClean($value) . '", ';
        }
        // Remove the last ", "
        $mysqlValues = mb_substr($mysqlValues, 0, -2, $this->_options['charset']);

        // Now insert
        mysqli_query($this->_db, 'INSERT INTO `' . $this->_options['db_prefix'] . 'shouts` (' . $mysqlRows . ') VALUES (' . $mysqlValues . ')');
    }
    
    /**
     * Get new messages
     *
     * @param int $lastID
     * @return string
     */
    public function dbGetMessages($lastID)
    {
        settype($lastID, 'integer');
        // If shoutbox just loaded get the last id
        if (empty($lastID)) {
            $result = mysqli_fetch_object(mysqli_query($this->_db, 'SELECT COUNT(`id`)-10 AS `lastID` FROM `' . $this->_options['db_prefix'] . 'shouts`'));
            $lastID = ($result->lastID) ? $result->lastID : 0;
        }
        
        // Get database data and then echo html
        $output = '';
        $result = mysqli_query($this->_db, 'SELECT `id`, `name`, `message`, `time` FROM `' . $this->_options['db_prefix'] . 'shouts` WHERE `id` > "' . (int) $lastID . '" ORDER BY `id` ASC');
        // Fetch new shouts from the database
        while ($row = mysqli_fetch_object($result)) {
            if ($row->name == 'Bot') {
                $output .= '<div id="' . $row->id . '" class="bot"><span class="time" title="' . date('d.m.Y - H:i:s', $row->time) . '">(' . date('H:i', $row->time) . ')</span> ' .
                                 '<span class="name">' . $this->cleanInput($row->name) . '</span>: <span class="message">' . $this->dealOutput($row->message) . '</span></div>';
            }
            else {
                $output .= '<div id="' . $row->id . '"><span class="time" title="' . date('d.m.Y - H:i:s', $row->time) . '">(' . date('H:i', $row->time) . ')</span> ' .
                                 '<span class="name">' . $this->cleanInput($row->name) . '</span>: <span class="message">' . $this->dealOutput($row->message) . '</span></div>';
            }
        }
        mysqli_free_result($result);
        
        return $output;
    }
    
    /**
     * Deal with the output
     *
     * @param string $data
     * @return string
     */
    public function dealOutput($data)
    {
        return $this->replaceSmilies($this->convertLinebreaks($this->setWordwrap($this->bbcode($this->cleanInput($data)))));
    }
    
    /**
     * Clean the input data for database
     *
     * @param string $data
     * @return string
     */
    public function dbClean($data)
    {
        return mysqli_real_escape_string($this->_db, $data);
    }
    
    /**
     * Clean the input data
     *
     * @param string $data
     * @return string
     */
    public function cleanInput($data)
    {
        return trim(htmlspecialchars(mb_convert_encoding($data, $this->_options['charset'], mb_detect_encoding($data)), ENT_QUOTES, $this->_options['charset']));
    }
    
    /**
     * BBCode
     *
     * @param string $data
     * @return string
     */
    public function bbcode($data)
    {
        if ($this->_options['enable_bbcode']) {
            // Replace [b][/b]
            $data = preg_replace('/\[b\](.*?)\[\/b\]/', '<b>$1</b>', $data);
            // Replace [i][/i]
            $data = preg_replace('/\[i\](.*?)\[\/i\]/', '<i>$1</i>', $data);
            // Replace [url=][/url]
            $data = preg_replace('/\[url=([^ ]+).*\](.*)\[\/url\]/', '<a href="$1" target="_blank" rel="nofollow">$2</a>', $data);
            // Replace [url][/url]
            $data = preg_replace('/\[url\](.*)\[\/url\]/', '<a href="$1" target="_blank" rel="nofollow">$1</a>', $data);
        }
        
        return $data;
    }
    
    /**
     * Replace e.g. ":-)" with a image smilie
     *
     * @param string $text
     * @return string
     */
    private function replaceSmilies($text)
    {
        if ($this->_options['enable_smilies']) {
            // Image tag for the smilies
            $image_tag = array('<img src="media/smilies/', '" alt="" />');
            
            // Array of smilies
            $smilies = array(
                ':-)' => $image_tag[0] . '1.png' . $image_tag[1],
                ';-)' => $image_tag[0] . '2.png' . $image_tag[1],
                ':)'  => $image_tag[0] . '1.png' . $image_tag[1],
                ';)'  => $image_tag[0] . '2.png' . $image_tag[1],
                ':D'  => $image_tag[0] . '3.png' . $image_tag[1],
                ':-D' => $image_tag[0] . '3.png' . $image_tag[1],
                ':P'  => $image_tag[0] . '4.png' . $image_tag[1],
                ';P'  => $image_tag[0] . '5.png' . $image_tag[1],
                ';D'  => $image_tag[0] . '6.png' . $image_tag[1],
                ':('  => $image_tag[0] . '31.png' . $image_tag[1],
                ':-(' => $image_tag[0] . '31.png' . $image_tag[1],
                '=)'  => $image_tag[0] . '7.png' . $image_tag[1],
                '=D'  => $image_tag[0] . '3.png' . $image_tag[1],
                '=P'  => $image_tag[0] . '4.png' . $image_tag[1],
                'xD'  => $image_tag[0] . '8.png' . $image_tag[1],
                ':S'  => $image_tag[0] . '17.png' . $image_tag[1],
                'xS'  => $image_tag[0] . '29.png' . $image_tag[1],
                ':O'  => $image_tag[0] . '14.png' . $image_tag[1],
                '=O'  => $image_tag[0] . '14.png' . $image_tag[1],
                '=/'  => $image_tag[0] . '20.png' . $image_tag[1],
                '=]'  => $image_tag[0] . '21.png' . $image_tag[1],
                '$_$' => $image_tag[0] . '26.png' . $image_tag[1],
                'o_O' => $image_tag[0] . '28.png' . $image_tag[1]
            );
            // Now replace it
            $text = str_ireplace(array_keys($smilies), array_values($smilies), $text);
        }
        
        return $text;
    }
    
    /**
     * Smiliar to the nl2br php function
     *
     * @param string $text
     * @return string
     */
    private function convertLinebreaks($text)
    {
        // Convert \n to linebreaks and delete them then
        $text = str_replace("\n", '', nl2br($text));
        
        return $text;
    }
    
    /**
     * Send charset header to client
     *
     * @param string $contentType
     * @return void
     */
    public function sendContentTypeHeader($contentType)
    {
        header('Content-Type: ' . $contentType . '; charset=' . $this->getCharset());
    }
    
    /**
     * Set wordwrap
     *
     * @param string $text
     * @return string
     */
    private function setWordwrap($text)
    {
        return wordwrap($text, 88, "\n", true);
    }
    
    /**
     * Get the heading
     *
     * @param none
     * @return string
     */
    public function getHeading()
    {
        return $this->_options['heading'];
    }
    
    /**
     * Get the charset
     *
     * @param none
     * @return string
     */
    public function getCharset()
    {
        return $this->_options['charset'];
    }
    
    /**
     * Get the refresh rate
     *
     * @param none
     * @return float
     */
    public function getRefreshRate()
    {
        return (float) $this->_options['refresh'];
    }
    
    /**
     * Get the base URL for requests
     *
     * @param none
     * @return float
     */
    public function getBaseURL()
    {
         $protocol = 'http' . (($this->isHTTPs()) ? 's' : '') . '://';
         $baseURL = $protocol . $this->cleanInput($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
         
         return $baseURL;
    }
    
    /**
     * Get if shoutbox is accessed via HTTPS
     *
     * @param none
     * @return bool
     */
    public function isHTTPs()
    {
         return (bool) isset($_SERVER['HTTPS']);
    }
    
    /**
     * Get the spam protection rate
     *
     * @param none
     * @return int
     */
    public function getSpamProtectionRate()
    {
        return (int) $this->_options['spam_protect'];
    }
    
    /**
     * Get prepopulate name if isset via $_GET parameter
     *
     * @param none
     * @return string
     */
    public function getPrepopulateName()
    {
        $name = (isset($_GET['name'])) ? $this->cleanInput(strip_tags($_GET['name'])) : '';
        return $name;
    }
    
    /**
     * Check if it is an xmlhttprequest
     *
     * @param none
     * @return boolean
     */
    public function isAjax()
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;
    }
    
    /**
     * Check if request was made from mobile device or forced via parameter
     *
     * @param none
     * @return boolean
     */
    public function isMobile()
    {
        return (preg_match('#(Android|BlackBerry|Cellphone|iPhone|iPod|HTC|Nokia|Opera Mobi|Palm|SonyEricsson|Symbian|UP.Browser|UP.Link|webOS|Windows CE|WinWAP|Maemo|phone)#', $_SERVER['HTTP_USER_AGENT']) || isset($_GET['mobile']));
    }
    
    /**
     * Disable clone
     *
     * @return void
     */
    protected function __clone()
    {}

}
