<?php
    abstract class AUIWidget {
    	
    	const TPL_ROOT = '_services/UIWidgets/widgets/';
    	
    	/**
    	 * Renders the corresponding template.
    	 * @return The rendered html
    	 */
        public abstract function render();
        
    	protected function parseValue($value){
			$firstPos = strpos($value, '{@');
			
			if($firstPos !== false){
				$out = array('attributes' => '');
				$text = substr($value, 0, $firstPos);
				$rest = substr($value, $firstPos);
				
				$parts = explode('{@', $rest);
				
				foreach($parts as &$chunk){
					$split = explode('}', $chunk, 2);
					$subsplit = explode(':', $split[0], 2);
					if(count($subsplit) == 2){
						$out['attributes'] .= ' '.$subsplit[0].'="'.$subsplit[1].'"';
					} else {
						$text .= @$split[0];
					}
					$text .= @$split[1];
				}
				unset($chunk);
				
				
				$out['value'] = $text;
				return $out;
			}
			
			return array('value' => $value);
		}
    }
?>