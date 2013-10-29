<?php

Class Conf
{

   private $_domain;
   private $_userdb;
   private $_passdb;
   private $_hostdb;
   private $_db;

   static $_instance;

   private function __construct(){
      //getting the value of every session, to connect the db
      $this->_userdb=$_SESSION['user'];
      $this->_passdb=$_SESSION['pass'];
      $this->_hostdb=$_SESSION['host'];
      $this->_db=$_SESSION['name'];
   }

   private function __clone(){ }

   public static function getInstance(){
      if (!(self::$_instance instanceof self)){
         self::$_instance=new self();
      }
      return self::$_instance;
   }

   public function getUserDB(){
      $var=$this->_userdb;
      return $var;
   }

   public function getHostDB(){
      $var=$this->_hostdb;
      return $var;
   }

   public function getPassDB(){
      $var=$this->_passdb;
      return $var;
   }

   public function getDB(){
      $var=$this->_db;
      return $var;
   }

}

?>
