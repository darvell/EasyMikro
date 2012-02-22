<?php
/*
Copyright (c) 2011, Darvell Long
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met: 

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer. 
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those
of the authors and should not be interpreted as representing official policies, 
either expressed or implied, of the FreeBSD Project.
*/

require('routeros_api.class.php');

class EasyMikro
{

  private $APIObject;
  private $IsConnected;
  private $ActiveUserID;

  // Create the connection on init and destroy on object destruction, keeps it simple, stupid.
  public function __construct($IPAddress,$Username,$Password,$Debug=false)
  {
    $this->APIObject = new routeros_api();
    $this->APIObject->debug = $Debug;
    $this->IsConnected = false;

    if($this->APIObject->connect($IPAddress,$Username,$Password))
    {
      $this->IsConnected = true;
    }
  }

  public function __destruct()
  {
    if($IsConnected)
    {
      $this->APIObject->disconnect();
    }
  }

  private function GetUserID($username)
  {
    $this->APIObject->write("/ppp/secret/getall",false);
    $this->APIObject->write("?name=".$username,true);

    $resultArray = $this->APIObject->parse_response($this->APIObject->read(false));

    if(sizeof($resultArray) == 0)
    {
      return null;
    }
    else
    {
      return $resultArray[0]['.id'];
    }
  }


  public function Connected()
  {
    return $this->IsConnected;
  }

  public function UserExists($username)
  {
    if($this->GetUserID($username) != null)
    {
      return true;
    }
    return false;
  }

  public function SetActiveUser($username)
  {
    $this->ActiveUserID = $this->GetUserID($username);
    if(isset($this->ActiveUserID))
    {
      return true;
    }
    return false;
  }

  public function AddUser($username,$password,$profile,$comment='No Comment',$staticip=null)
  {
    $userInfoArray = array(
      "name"     => $username,
          "password" => $password,
          "comment"  => $comment,
          "profile"  => $profile);
  
    $this->APIObject->comm("/ppp/secret/add",$userInfoArray);
  }

  public function ModifyUserProfile($profile)
  {
    $this->APIObject->write('/ppp/secret/set',false);
    $this->APIObject->write('=.id='.$this->ActiveUserID,false);
    $this->APIObject->write('=profile='.$profile,true);
    $this->APIObject->read(false);
  }

  public function SetUserIP($ip)
  {
    $this->APIObject->write('/ppp/secret/set',false);
    $this->APIObject->write('=.id='.$this->ActiveUserID,false);
    $this->APIObject->write('=remote-address='.$ip,true);
    $this->APIObject->read(false);
  }

  public function UnsetUserIP()
  {
    $this->APIObject->write('/ppp/secret/unset',false);
    $this->APIObject->write('=.id='.$this->ActiveUserID,false);
    $this->APIObject->write('=value-name=remote-address',true);
    $this->APIObject->read(false);
  }

  public function ModifyUserDisabledStatus($disabled)
  {
    if(isset($this->ActiveUserID))
    {
          $this->APIObject->comm("/ppp/secret/disable",array(".id" => $this->ActiveUserID));
    }
  }

  public function BootUser()
  {
    if(isset($this->ActiveUserID))
    {
      $this->APIObject->comm("/ppp/active/remove",array(".id" => $this->ActiveUserID));
    }
  }

  public function DeleteUser()
  {
    if(isset($this->ActiveUserID))
    {
      $this->APIObject->comm("/ppp/secret/remove",array(".id" => $this->ActiveUserID));
    }
  }
}