<?php

class Connection{

	private $host;
	private $user;
	private $password;
	private $port;
	private $database;
	private $link;
	
	function __construct(){
		$this->setConnect();
		$this->connect();
	}
	
	private function setConnect(){
		require("configuracion.php");
		$this->host=$servidor;
		$this->user=$usuario;
		$this->password=$clave;
		$this->port=$puerto;
		$this->database=$baseDatos;
	}
	private function connect(){
		$this->link=mysqli_connect(
		$this->host,
		$this->user,
		$this->password,
		$this->database);
		
		if(!$this->link){
			echo mysqli_error($this->link);
		}
	}
	
	public function closeConect(){
		mysqli_close($this->link);
	}
	
	public function getConnect(){
		return $this->link;
	}
	
	public function execute($sql)
    {
        $ejecutar=mysqli_query($this->getConnect(),$sql);
        if($ejecutar){
                return $ejecutar;
        }
        else{
            die(mysqli_error($this->getConnect()));
        }
    }
}


?>