<?php
//============================================================================
//ImprovedHashAlgorithm (IHA) - Generates a strong hash value from a weak password
//Copyright (C) 2007-2008  Nils Reimers (www.php-einfach.de)
//
//This library is free software; you can redistribute it and/or
//modify it under the terms of the GNU Lesser General Public
//License as published by the Free Software Foundation; either
//version 2.1 of the License, or (at your option) any later version.
//
//This library is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//Lesser General Public License for more details.
//
//You should have received a copy of the GNU Lesser General Public
//License along with this library; if not, write to the Free Software
//Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//=============================================================================


//Version of ImprovedHashAlgorithm
define("IHA_VERSION", "1.0");

class iha {

   /* Number of rounds
      A higher number of rounds increase the security,
      but it take more time for the calculation of a hash
      Default: 2500
   */
   var $rounds = 2500;

   /* Using SHA1 or MD5
      When $sha1 = true, then the SHA1 would be used instead of MD5
      Default: false
   */
   var $sha1 = false;

   /* The length of the Salt
      The salt is the part from the start to the semicolon ; of the output
      The salt is needed to prevent a dictionary attack.
      Default: 6
   */
   var $saltLng = 6;

   /* A secret password
      When $password has a value, only persons who know the password
      can generate valid password hashs.
      When the password get lost, existing password hashs cannot be verify anymore
      Default: ""
   */
   var $password = "";


   /* The Hash-Function
      This function generates and returns a more secure hash of a password.
      If $salt has no value, a random $salt would be generated.

      Usage:
         $hash = $iha->hash("secret password");
   */
   function hash($pwd, $salt="")
   {
		return $this->generateHash($pwd, $salt, $this->sha1, $this->rounds);
   }

   /* Verifies if a password is valid
      This function checks, if the value of $pwd belongs to the value of $hash
      $hash could come from a database or something else.

      Usage:
         if($iha->compare("secret password", "AAAJxA;2Rd/Ts;3qwiKTWppNtb2M4nhDrmyA=="))
            //Everything ok
         else
            //Wrong password
   */
   function compare($pwd, $hash)
   {
		//Wrong format?
      if(substr_count($hash, ";") != 2)
			return false;

      list($header,$salt,$value) = explode(";", $hash);

      $header = base64_decode($header);
      $rounds = ord($header{1})<<16 | ord($header{2})<<8 | ord($header{3});
      $flag = ord($header{0});
      $sha1 = ((($flag&0x80)>>7) == 1);


      return ($this->generateHash($pwd, $salt, $sha1, $rounds) == $hash);
   }

   /*
      Print infos about this class and makes a benchmark
   */
   function info()
   {
      echo "<b>Improved Hash Algorithm</b><br>";
      echo "Version: ".IHA_VERSION."<br>";
      echo "Algorithm: ".(($this->sha1 == true AND function_exists("sha1")) ? "SHA1" : "MD5")."<br>";
      echo "Rounds: ".$this->rounds."<br>";
      echo "Salt-Length: ".$this->saltLng."<br>";
      echo "Password: ".((empty($this->password)) ? "No" : "Yes")."<br>";

      $this->benchmark();

      echo "<br><br><br>";
   }

   /*
      Makes a benchmark of the key stretching method
      and presents how long it take to generate 1 hash.
   */
   function benchmark($num=10)
   {
      echo "Generating $num hashs: ";

      $start = (double)microtime() + time();

      for($i=0;$i<$num;$i++)
         $this->hash("Benchmark");

      $ende = (double)microtime() + time();
      $diff = round($ende-$start, 4);

      echo "Generated in ".$diff." seconds; ".($diff/$num)." per hash";

   }



   /*********************************************
         The following functions are only
               for internal usage
   *********************************************+/


	/* Generates the hash of $pwd.
	*/
    function generateHash($pwd, $salt, $sha1, $rounds)
    {
      if(empty($salt))
         $salt = $this->generateSalt();

      $header = $this->generateHeader();


      $key = $salt.$pwd.$this->password;

      if($sha1 == true AND function_exists("sha1"))
         $key = sha1($key);
      else
         $key = md5($key);


      $key = base64_encode(pack("H*",$this->keyStretching($key, $sha1, $rounds)));

      return $header.";".$salt.';'.$key;
    }


   /* The Key stretching method
      This function makes it for an attacker very time expensive to
      calculate each possible password, because he have to hash
      each password e.g. 10000 times.

      This is a non-public function
   */
   function keyStretching($key, $sha1, $rounds)
   {
      if($sha1 == true AND function_exists("sha1"))
         for($i=0;$i<$rounds; ++$i)
            $key = sha1($key);
      else
         for($i=0;$i<$rounds; ++$i)
            $key = md5($key);




      return $key;
   }


   /* This method generate a random salt, based on the current time

      This is a non-public function
   */
   function generateSalt()
   {
      $salt = base64_encode(pack("H*", md5(microtime())));
      return substr($salt, 0, $this->saltLng);
   }


   /* Generates a header for the hash, so you can identify which settings was used to
      generate the has.

      This is a non-public function
   */
   function generateHeader()
   {
      $rounds = $this->rounds;
      $flag = (($this->sha1 == true AND function_exists("sha1")) ? 1 : 0)<<7;


      return substr(base64_encode(pack("N*", $rounds | $flag<<24)),0,6);

   }

}

?>