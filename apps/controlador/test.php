<?php
class test extends Control {

	public function index()
	{
	    $num = $this->basic->roundNumber(253,100);
		var_dump($num);
		$num = $this->basic->roundNumber(2332,100);
		var_dump($num);
		$num = $this->basic->modaArray(array(1,1,1,2,1,2,2,2,3,3));
		var_dump($num);
    }

}
?>