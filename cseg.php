<?php
class CSeg
{
    protected $_dictHashTable;
    protected $_compile;
    protected $_compile_path;

    public function __construct()
    {
        $this->_dictHashTable = array();
        $this->_compile = false;
        $this->_compile_path = './';
    }

    public function setCompile($compile, $compile_path = './')
    {
        $this->_compile = $compile;
        $this->_compile_path = $compile_path;
    }

    public function loadDicts($dicts)
    {
        $hash = substr(sha1(implode('', $dicts)), 0, 8);
        $dump_file_name = $this->_compile_path . $hash . '.dd';
        if($this->_compile)
        {
            if(file_exists($dump_file_name))
            {
                $fp_dump_file = fopen($dump_file_name, 'r');
                $this->_dictHashTable = unserialize(fread($fp_dump_file, filesize($dump_file_name)));
                fclose($fp_dump_file);
                return;
            }
        }

        foreach($dicts as $file)
        {
            $fp = fopen($file, 'r');
            while(! feof($fp))
            {
                $word = fgets($fp);
                $word = substr($word, 0, strlen($word) - 1);
                $key = mb_substr($word, 0, 1, 'utf-8');

                if(! $this->_dictHashTable[$key])
                {
                    $this->_dictHashTable[$key] = array();
                }
                array_push($this->_dictHashTable[$key], $word);
            }
            fclose($fp);
        }

        function cmp($a, $b)
        {
            return strlen($b) - strlen($a);
        }

        foreach($this->_dictHashTable as $k => $v)
        {
            usort($this->_dictHashTable[$k], 'cmp');
        }

        if($this->_compile)
        {
            $fp_dump_file = fopen($dump_file_name, 'w');
            fwrite($fp_dump_file, serialize($this->_dictHashTable));
            fclose($fp_dump_file);
        }
    }

    public function segment($text)
    {
        $words = array();
        $len = mb_strlen($text, 'utf-8');

        $i = 0;
        while($i < $len)
        {
            $firstChar = mb_substr($text, $i, 1, 'utf-8');
            $wordList = $this->_dictHashTable[$firstChar];

            $found = false;
            for($j = 0; $wordList[$j]; $j++)
            {
                $slice = mb_substr($text, $i, mb_strlen($wordList[$j], 'utf-8'), 'utf-8');
                if($wordList[$j] === $slice)
                {
                    array_push($words, $slice);
                    $i += mb_strlen($slice, 'utf-8');
                    $found = true;
                    continue;
                }
            }

            if(! $found)
            {
                array_push($words, $firstChar);
                $i++;
            }
        }

        return $words;
    }
}
