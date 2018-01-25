<?php

if(class_exists('Memcached', false)){
    class clsMemcached extends Memcached {

    }
}else{
    class clsMemcached extends Memcache {
        public function addServers($options){
            foreach($options as $opt){
                $this->addserver($opt['host'], $opt['port']);
            }
        }
        public function set($key,$value,$expire = 0)
        {
            return parent::set($key,$value,0,$expire);
        }

        public function add($key,$value,$expire = 0)
        {
            return parent::add($key,$value,0,$expire);
        }
    }
}