<?php
class Baza{
	function __construct(){ 
		$servername = "localhost";
		$username = "root";
		$password = "1234";
		$dbname = "events";
		$conn = new mysqli($servername, $username, $password, $dbname);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}	 
		$this->DB = $conn;
	}
	function filtruj($zmienna){
		if(get_magic_quotes_gpc())
			$zmienna = stripslashes($zmienna);
		return mysql_real_escape_string(htmlspecialchars(trim($zmienna)));
	}
	
	function isLoginInDB($Login){
		$login = $this->filtruj($Login);
		$result= $this->DB->query("SELECT * FROM uzytkownik WHERE login='".$login."'");
		return $result->num_rows!==0;
	}
	
	function tagowanie($usr, $tags){
		foreach ($tags as &$tag) {
			$result = $this->DB->query("SELECT * FROM `tag` WHERE nazwa like '".$tag."'");
			if($result->num_rows===0){
				$result = $this->DB->query("INSERT INTO `tag`(`nazwa`) VALUES ('".$tag."')");
				$result = $this->DB->query("SELECT * FROM `tag` WHERE nazwa like '".$tag."'");
			}
			$row1 = $result->fetch_assoc();
			$this->DB->query("INSERT INTO `relacja_tag_uzytkownik`(`uzytkownik_id`, `tag_id`) VALUES (".$usr.",".$row1['id'].")");
		}
	}
	
	function getTags($str){
		preg_match_all('/#([^\s]+)/', $str, $matches);
		return $matches[1];
	}
	
	function registerUzytkownik($login, $pass, $pass2, $tag, $fl, $typ){
		$login = $this->filtruj($login);
		$pass = $this->filtruj($pass);
		$pass2 = $this->filtruj($pass2);
		$tag = $this->filtruj($tag);
		$fl = $this->filtruj($fl);
		$typ = $this->filtruj($typ);
		if($login==="" || $pass==="" || $pass2==="" || $tag==="" || $typ==="" ){
			return [false,"Wszystkie pola są wymagane"];
		}
		if($pass!==$pass2){
			return [false,"Podałeś dwa inne hasła"];
		}
		if($this->isLoginInDB($login)){
			return [false,"Login zajęty"];
		}
		
		$tags = $this->getTags($tag);
		
		$hashtags = implode(',', $tags);
		$this->DB->query("INSERT INTO uzytkownik (login, haslo, opis, facebook_link, typ) VALUES ('".$login."', '".$pass."', '".$hashtags."', '".$fl."',".$typ.")");
		$result= $this->DB->query("SELECT * FROM uzytkownik WHERE login='".$login."'");
		$row1 = $result->fetch_assoc();
		$this->tagowanie($row1['id'], $tags);

		
		return [true, $login, $row1['id']];		
	}
	
	function zaloguj($login, $haslo){
		$login = $this->filtruj($login);
		$haslo = $this->filtruj($haslo);
		$result = $this->DB->query("SELECT * FROM uzytkownik WHERE login = '".$login."' AND haslo='".$haslo."'");
		if($result->num_rows===0){
			return [false,"Złe dane"];
		}
		$row1 = $result->fetch_assoc();
		return [true, $login, $row1['id'] ];
	}
	function getEventsStworzonychList($usrID){
		return $this->DB->query("SELECT * FROM event WHERE uzytkownik_id=".$usrID." ORDER BY data_utowrzenia DESC");
	}
	function getEventsUdzialList($usrID){
		return $this->DB->query("Select * from event INNER JOIN relacja_event_uzytkownik ON event.id = relacja_event_uzytkownik.event_id where relacja_event_uzytkownik.uzytkownik_id = ".$usrID);
	}
	function getEventsUserTagsList($usrID){
		return $this->DB->query("Select event.* from event INNER JOIN relacja_event_tag ON event.id = relacja_event_tag.event_id INNER JOIN relacja_tag_uzytkownik ON relacja_tag_uzytkownik.tag_id = relacja_event_tag.tag_id where relacja_tag_uzytkownik.uzytkownik_id = ".$usrID);
	}
	function stworzEvent(){
		
	}
	
	
	
	
	
	
}
?>