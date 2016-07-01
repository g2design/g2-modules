<?php


use SuperClosure\Serializer;
use Mvc_Base;
use R;

class Queuer extends Mvc_Base {
	
	static function add_callable($function, $note = ''){

		$serializer = new Serializer();
		$s_closure = $serializer->serialize($function);
		$callable = R::dispense('queueitem');
		
		$callable->callser = $s_closure;
		$callable->added_at = time();
		$callable->note = $note;
		$callable->status = 'open';
		
		R::store($callable);
	}
	
	static function execute($number = 5) {
		if(!is_numeric($number) ) {
			throw new Exception( 'Number must be numeric' );
		}
		
		$callables = R::findAll('queueitem','status = "open" ORDER BY id DESC LIMIT '. $number);
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
			$closure = $serializer->unserialize($calleble->callser);
			
////			$calleble->status = 'busy';
//			R::store($calleble);
			
			$closure();
		
			
			$calleble->status = 'done';
			$calleble->done = true;
			$calleble->doneat = time();
			
			R::store($calleble);
		}
		
		return;
	}
	
}