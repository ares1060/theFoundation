<?php
/*
 * Copyright (c) 2009 David Soria Parra - edited by Matthias Eigner for TheFoundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/**
 * Gettext implementation in PHP
 *
 * @copyright (c) 2009 David Soria Parra <sn_@gmx.net>
 * @author David Soria Parra <sn_@gmx.net> edited for TheFoundation
 */
class LocalizationHelper {
    /**
     * First magic word in the MO header
     */
    const MAGIC1 = "\xde\x12\x04\x95";

    /**
     * Second magic word in the MO header
     */
    const MAGIC2 = "\x95\x04\x12\xde";

    /**
     * Initialize a new gettext class
     *
     * @param String $mofile The file to parse
     */
    public function __construct(){ }

    /**
     * Parse the MO file header and returns the table
     * offsets as described in the file header.
     *
     * If an exception occured, null is returned. This is intentionally
     * as we need to get close to ext/gettexts beahvior.
     *
     * @oaram Ressource $fp The open file handler to the MO file
     *
     * @return An array of offset
     */
    private function parseHeader($fp) {
        $magic   = fread($fp, 4);
        $data   = fread($fp, 4);
        $header = unpack("lrevision", $data);
        
        if (self::MAGIC1 != $magic
           && self::MAGIC2 != $magic) {
            return null;
        }

        if (0 != $header['revision']) {
            return null;
        }

        $data    = fread($fp, 4 * 5);
        $offsets = unpack("lnum_strings/lorig_offset/"
                          . "ltrans_offset/lhash_size/lhash_offset", $data);
        return $offsets;
    }

    /**
     * Parse and returns the string offsets in a a table. Two table can be found in
     * a mo file. The table with the translations and the table with the original
     * strings. Both contain offsets to the strings in the file.
     *
     * If an exception occured, null is returned. This is intentionally
     * as we need to get close to ext/gettexts beahvior.
     *
     * @param Ressource $fp     The open file handler to the MO file
     * @param Integer   $offset The offset to the table that should be parsed
     * @param Integer   $num    The number of strings to parse
     *
     * @return Array of offsets
     */
    private function parseOffsetTable($fp, $offset, $num) {
        if (fseek($fp, $offset, SEEK_SET) < 0) {
            return null;
        }

        $table = array();
        for ($i = 0; $i < $num; $i++) {
            $data    = fread($fp, 8);
            $table[] = unpack("lsize/loffset", $data);
        }

        return $table;
    }

    /**
     * Parse a string as referenced by an table. Returns an
     * array with the actual string.
     *
     * @param Ressource $fp    The open file handler to the MO fie
     * @param Array     $entry The entry as parsed by parseOffsetTable()
     *
     * @return Parsed string
     */
    private function parseEntry($fp, $entry) {
        if (fseek($fp, $entry['offset'], SEEK_SET) < 0) {
            return null;
        }
        if ($entry['size'] > 0) {
            return fread($fp, $entry['size']);
        }

       return '';
    }


    /**
     * Parse the MO file
     *
     * @return void
     */
    public function loadMoFile($directory, $domain='core', $lang='') {
    	if($lang=='') $lang = $GLOBALS['Localization']['language'];
    	
    	$file = $directory.'/'.$domain.'_'.$lang.'.mo';

    	$tmp = array();

        if (!file_exists($file)) {
        	//echo 'no_file';
            return false;
        }

        $filesize = filesize($file);
        if ($filesize < 4 * 7) {
        	// echo 'wrong size';
            return false;
        }

        /* check for filesize */
        $fp = fopen($file, "rb");

        $offsets = $this->parseHeader($fp);
        if (null == $offsets || $filesize < 4 * ($offsets['num_strings'] + 7)) {
            fclose($fp);
            //echo 'wrong size';
            return false;
        }

        $transTable = array();
        $table = $this->parseOffsetTable($fp, $offsets['trans_offset'],
                    $offsets['num_strings']);
        if (null == $table) {
            fclose($fp);
            //echo 'null';
            return false;
        }

        foreach ($table as $idx => $entry) {
            $transTable[$idx] = $this->parseEntry($fp, $entry);
        }

        $table = $this->parseOffsetTable($fp, $offsets['orig_offset'],
                    $offsets['num_strings']);
        foreach ($table as $idx => $entry) {
        	
            $entry = $this->parseEntry($fp, $entry);

            $formes      = explode(chr(0), $entry);
            $translation = explode(chr(0), $transTable[$idx]);
            foreach($formes as $form) {
                $tmp[$form] = $translation;
            }
        }
        
        fclose($fp);

        return $tmp;
    }
}

