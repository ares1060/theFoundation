<?php
/**
* Password strength indicator class (BETA)
*
* Version        : 1.0.0.0
* Author        : Dicky Kurniawan (xrvel)
* Last modified    : January 9th 2009
* Website        : http://dev.xrvel.com or http://xrvel.com
*/

class TextFunctionsPasswordStrength {
    private $password = '';
    private $passwordFlag = array();
    private $passwordInfo = array();
    private $passwordLength = 0;
    private $scorePrecision = 2;

    function PasswordStrength() {
    }

    public function calculateAll() {
        $this->initAll();

        $this->calculateLength();
        $this->calculateComplexity();
        $this->calculateCharSetComplexity();
        ksort($this->passwordInfo);

        $this->passwordInfo['password'] = $this->password;

        $total = 0;
        $scoreCount = 0;
        $keys = array_keys($this->passwordInfo['details']);
        foreach ($keys as $key) {
            if (preg_match('/Score+$/', $key)) {
                $total += intval($this->passwordInfo['details'][$key]);
                $scoreCount ++;
            }
        }
        $averageScore = round($total / $scoreCount, $this->scorePrecision);
        $scoreInfo = $this->getScoreInfo($averageScore);
        $scoreInfoNumber = $this->getScoreInfoNumber($averageScore);
        
        $this->passwordInfo['totalScore'] = $total;
        //$this->passwordInfo['scoreCount'] = $scoreCount;
        $this->passwordInfo['averageScore'] = $averageScore;
        $this->passwordInfo['averageScoreInfo'] = $scoreInfo;
        $this->passwordInfo['averageScoreInfoNumber'] = $scoreInfoNumber;
    }

    public function getInfo() {
        return $this->passwordInfo;
    }
    
    public function scorePwd($pwd) {
    	$this->setPassword($pwd);
    	$this->calculateAll();
    	return $this->getInfo();
    }

    public function setPassword($password) {
        $this->password = $password;
        $this->passwordLength = strlen($password);
    }

    /////////////////////////

    private function calculateCharSetComplexity() {
        $password = $this->password;
        $len = strlen($password);
        $char = '';
        $lastChar = '';
        $differentCount = 0;
        $score = 0;

        if ($len <= 3) {
            $score = 2;
        } else {
            for ($i = 0; $i < $len; $i++) {
                $char = substr($password, $i, 1);
                if ($i > 0) {
                    $lastChar = substr($password, $i-1, 1);
                }
    
                if ($char != $lastChar) {// current char is different with the previous char
                    $differentCount++;
                }
            }
            if ($len <= 5) {
                $score = 10;
            } else if ($differentCount == 1) {// only one character type
                $score = 1;
                // adjust length score
                $this->passwordInfo['details']['lengthScore'] = min(min(floor(10 * $this->passwordLength / 10), 20), $this->passwordInfo['details']['lengthScore']);
            } else if ($differentCount == 2) {// only one character type
                $score = 5;
                // adjust length score
                $this->passwordInfo['details']['lengthScore'] = min(min(floor(20 * $this->passwordLength / 10), 40), $this->passwordInfo['details']['lengthScore']);
            } else if ($differentCount == 3) {// only one character type
                $score = 10;
                // adjust length score
                $this->passwordInfo['details']['lengthScore'] = min(min(floor(30 * $this->passwordLength / 10), 50), $this->passwordInfo['details']['lengthScore']);
            } else {
                $score = round(max($this->passwordInfo['details']['lengthScore'] / 10, $differentCount / $len * 100), $this->scorePrecision);
            }
        }

        $this->passwordInfo['details']['characterSetComplexityScore'] = $score;
    }

    private function calculateComplexity() {
        $password = $this->password;
        $score = 0;
        if (preg_match('/^([0-9]+)+$/', $password)) {// only numerics
            $score = 10;
            $this->passwordFlag['charSet'] = 'numeric';
        } else if (preg_match('/^([a-z]+)+$/', $password)) {// only alpha
            $score = 30;
            $this->passwordFlag['charSet'] = 'alpha';
        } else if (preg_match('/^([a-z0-9]+)+$/i', $password)) {// if alpha numeric
            if (preg_match('/^([a-z]+)([0-9]+)+$/i', $password, $match)) {// if alpha and followed by numerics
                $alpha = $match[1];
                $numeric = $match[2];
                $numericLength = strlen($numeric);
                if ($numeric == 111 || $numeric == 123) {
                    $score = 31;
                    $this->passwordFlag['commonNumeric'] = true;
                } else if ($numericLength == 1) {
                    $score = 30;
                } else if ($numericLength <= 3) {
                    $score = 35;
                } else if ($numericLength <= 5) {
                    $score = 40;
                } else if ($numericLength <= 10) {
                    $score = 50;
                } else {
                    $score = 60;
                }
                $this->passwordFlag['charSet'] = 'alphanumeric1';
            } else if (preg_match('/^([0-9]+)([a-z]+)+$/i', $password, $match)) {// if numerics and followed by alpha
                $numeric = $match[1];
                $alpha = $match[2];
                $numericLength = strlen($numeric);
                if ($numeric == 111 || $numeric == 123) {
                    $score = 35;
                    $this->passwordFlag['commonNumeric'] = true;
                } else if ($numericLength == 1) {
                    $score = 30;
                } else if ($numericLength <= 3) {
                    $score = 35;
                } else if ($numericLength <= 5) {
                    $score = 40;
                } else if ($numericLength <= 10) {
                    $score = 50;
                } else {
                    $score = 60;
                }
                $this->passwordFlag['charSet'] = 'alphanumeric2';
            } else {// mixed positions
                $score = 80;
                $this->passwordFlag['charSet'] = 'alphanumeric3';
            }
        } else {
            $score = 100;
            $this->passwordFlag['charSet'] = 'alphanumericothers';
        }
        $this->passwordInfo['details']['characterSetScore'] = $score;
    }

    private function calculateLength() {
        $len = strlen($this->password);
        $score = 0;
        if ($len == 0) {
            //
        } else if ($len <= 3) {
            $score = 1;
        } else if ($len <= 4) {
            $score = 2;
        } else if ($len <= 5) {
            $score = 10;

        } else if ($len <= 6) {
            $score = 20;
        } else if ($len <= 8) {
            $score = 30;
        } else if ($len <= 10) {
            $score = 45;
        } else if ($len <= 15) {
            $score = 75;
        } else if ($len <= 18) {
            $score = 80;
        } else if ($len <= 20) {
            $score = 90;
        } else {
            $score = 100;
        }
        $this->passwordInfo['details']['lengthScore'] = $score;
    }

    private function getScoreInfo($score) {
        if ($score <= 15) {
            $scoreInfo = 'Very Bad';
        } else if ($score <= 35) {
            $scoreInfo = 'Bad';
        } else if ($score <= 45) {
            $scoreInfo = 'Medium - Bad';
        } else if ($score <= 55) {
            $scoreInfo = 'Medium';
        } else if ($score <= 65) {
            $scoreInfo = 'Medium - Good';
        } else if ($score <= 75) {
            $scoreInfo = 'Good';
        } else if ($score <= 90) {
            $scoreInfo = 'Very Good';
        } else if ($score <= 100) {
            $scoreInfo = 'Excellent';
        }
        return $scoreInfo;
    }
    
	private function getScoreInfoNumber($score) {
        if ($score <= 15) {
            $scoreInfo = 0;
        } else if ($score <= 35) {
            $scoreInfo = 1;
        } else if ($score <= 45) {
            $scoreInfo = 2;
        } else if ($score <= 55) {
            $scoreInfo = 3;
        } else if ($score <= 65) {
            $scoreInfo = 4;
        } else if ($score <= 75) {
            $scoreInfo = 5;
        } else if ($score <= 90) {
            $scoreInfo = 6;
        } else if ($score <= 100) {
            $scoreInfo = 7;
        }
        return $scoreInfo;
    }

    private function initAll() {
        $this->passwordFlag = array();
        $this->passwordInfo = array();
        //$this->passwordLength = 0;
    }
}
?>