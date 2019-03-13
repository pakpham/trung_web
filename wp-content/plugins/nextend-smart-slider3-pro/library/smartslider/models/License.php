<?php

class N2SmartsliderLicenseModel {

    private $key;

    public function __construct() {
        $this->key = N2Base::getApplication('smartslider')->storage->get('license', 'key');
    
    }

    public static function getInstance() {
        static $ins;
        if (!$ins) {
            $ins = new N2SmartsliderLicenseModel();
        }

        return $ins;
    }

    public function hasKey() {
        return true;
    }

    public function maybeActive() {
        $lastActive = N2Base::getApplication('smartslider')->storage->get('license', 'isActive');
        if ($lastActive && $lastActive > strtotime("-1 week")) {
            return true;
        }

        return false;
    }

    public function getKey() {
        return $this->key;
    }

    public function setKey($licenseKey) {
        N2Base::getApplication('smartslider')->storage->set('license', 'key', $licenseKey);
        if ($licenseKey == '') {
            N2Base::getApplication('smartslider')->storage->set('license', 'isActive', 0);
        }
        $this->key = $licenseKey;
    
    }

    public function checkKey($license, $action = 'licensecheck') {
        return 'OK';
    }

    public function isActive($cacheAccepted = true) {
        return 'OK';
    }

    public function deAuthorize() {
        if ($this->hasKey()) {
            $this->checkKey($this->key, 'licensedeauthorize');
            $this->setKey('');
            N2Message::notice('Smart Slider deauthorized on this site!');

            return 'OK';
        }

        return false;
    }
}