<?php


use SuperClosure\Serializer;
use Mvc_Base;
use R;

class Queuer extends Mvc_Base {
	
	static function add_callable($function, $note = ''){

		$serializer = new Serializer();
		$s_closure = $serializer->serialize($function);
		
		$callable = R::dispense('queueitem');
		
		$calleble->function = $s_closure;
		$calleble->added_at = time();
		$callable->note = $note;
		
		R::store($callable);
	}
	
	static function execute($number = 5) {
		$callables = R::findAll('queueitem','ORDER BY id DESC',['num' => $number]);
		$c=0;
		foreach (array_values($callables) as $index => $calleble) {$c++;
			if($calleble->done) {
				$c--;
				continue;
			}
			if($c >= $number ) {
				break;
			}
			$serializer = new Serializer();
			$closure = $serializer->unserialize($calleble->function);
			
			$closure();
			
			$calleble->done = true;
			$calleble->doneat = time();
			
			R::store($calleble);
		}
		
		return;
	}
	
}