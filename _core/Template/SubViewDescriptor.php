<?php
	require_once $GLOBALS['config']['root'].'_core/Template/ViewDescriptor.php';
	
	/**
	 *	This class is a container for template parsing instructions and raplace values
	 */
	class SubViewDescriptor extends ViewDescriptor {
	
		/** @var ViewDescriptor */
		protected $parentView;
	
		/** @var string */
		protected $qualifiedName;
		
		/**
		 * Creates a new SubViewDescriptor for the named dynamic in a template.
		 * @param $name The name of the dynamic area.
		 */
		function __construct($name){
			parent::__construct($name);
			$this->parentView = null;
			$this->qualifiedName = $name;
		}
		
		/**
		 *	Returns the parsed template as a string
		 *	@return string
		 */
		function render(){
			if(!isset($this->parentView)) {
				$this->sp->msg->run(array('message'=>$this->sp->ref('Localization')->translate('_SubViewDescriptor rendered without parent View', 'core'), 
										  'type'=>Messages::RUNTIME_ERROR));
				return '';
				break;
			}
			
			$rSub = array();

			//render subviews
			foreach($this->subViews as &$sub){
			    if(!isset($rSub[$sub->getQualifiedName()])) $rSub[$sub->getQualifiedName()] = '';
				$rSub[$sub->getQualifiedName()] .= $sub->render();
			}
			unset($sub);
			//remove dynamics
			foreach($this->remove as &$rem){
				if(!isset($rSub[$rem])) $rSub[$rem] = '';
			}
			unset($rem);
			//render template
			return $this->tplService->renderDynamic($this->parentView->getName(), $this->qualifiedName, $this->values, $rSub);
		}
		
		/**
		 *	Add a subView to define how dynamics within a template should be parsed.
		 *	
		 *	@param $subViewDescriptor SubViewDescriptor containing the name of the dynamic block and replacement values.
		 */
		function addSubView($subViewDescriptor){
			$subViewDescriptor->setParent($this->parentView);
			$subViewDescriptor->updateQualifiedName($this->qualifiedName);
			array_push($this->subViews, $subViewDescriptor);
		}
		
		/**
		 * Updates the qualified name by prepending the given path to the SubView's name.
		 * 
		 * @param $path string
		 */
		function updateQualifiedName($path){
			$this->qualifiedName = $path.'_'.$this->name;
			//distribute  to children
			$c = count($this->subViews);
			for($i = 0; $i < $c; $i++){
				$this->subViews[$i]->updateQualifiedName($this->qualifiedName);
			}
		}
		
		/**
		 *	Set the SubView's parent View
		 *	@param $parent ViewDescriptor
		 */
		function setParent($parent){
			$this->parentView = $parent;
			//distribute parent to children
			$c = count($this->subViews);
			for($i = 0; $i < $c; $i++){
				$this->subViews[$i]->setParent($parent);
			}
		}
		
		/**
		 * The SubView's qualified name
		 * 
		 * @return string
		 */
		function getQualifiedName(){
			return $this->qualifiedName;
		}

	}

?>