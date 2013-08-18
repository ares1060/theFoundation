<?php
	/**
	 *	This class is a container for template parsing instructions and replace values
	 */
	class ViewDescriptor {

		protected $sp;
		protected $tplService;
		protected $values;
		protected $subViews;
		protected $remove;
		protected $name;
	
		/**
		 * Creates a new ViewDescriptor for the template of the given name.
		 * If the template is in a subfolder teh full realtive path inside the template's main folder has to be included in the name.
		 * @param $name The name of the template.
		 */
		function __construct($name){
			$this->sp = $GLOBALS['ServiceProvider'];
			$this->tplService = $this->sp->ref('Template');
			$this->values = array();
			$this->subViews = array();
			$this->remove = array();
			$this->name = $name;
		}
		
		/**
		 *	Returns the parsed template as a string
		 *	@return string
		 */
		function render(){
			$rSub = array();
			//render subviews
			foreach($this->subViews as &$sub){
			    if(!isset($rSub[$sub->getName()])) $rSub[$sub->getName()] = '';
				$rSub[$sub->getName()] .= $sub->render();
			}
			unset($sub);
			//remove dynamics
			foreach($this->remove as &$rem){
				if(!isset($rSub[$rem])) $rSub[$rem] = '';
			}
			unset($rem);
			//render template
			return $this->tplService->renderTemplate($this->name, $this->values, $rSub);
		}
		
		/**
		 *	Add a replace value
		 *  e.g. 'myValue' would replace the placeholder '{@pp:myValue}' within the template.
		 *	
		 *	@param $name The name of the placeholder.
		 *	@param $value The value the placeholder shall be replaced with.
		 */
		function addValue($name, $value){
			$this->values[$name] = $value;
		}
		
		/**
		 *	Add multiple replace values
		 *  e.g. 'myValue' would replace the placeholder '{@pp:myValue}' within the template.
		 *	
		 *	@param $assocArray An associative array. If a the the same key already exists it will be replaced by the assocArray's value. 
		 */
		function addValues($assocArray){
			$this->values = array_merge($this->values, $assocArray);
		}
		
		/**
		 *	Add a subView to define how dynamics within a template should be parsed.
		 *	
		 *	@param $subViewDescriptor A SubViewDescripotr containing the name of the dynamic block and replacement values.
		 */
		function addSubView($subViewDescriptor){
			$subViewDescriptor->setParent($this);
			array_push($this->subViews, $subViewDescriptor);
		}
		
		/**
		 *	Createas and adds a SubViewDescriptor for the given name
		 *	@param string $name The name of the sub view
		 * 	@param array $values An optional associative array for adding values to the SubView
		 *	@return SubViewDescriptor The SubViewDescriptor of the sub view
		 */
		function showSubView($name, $values = array()){
			$sub = new SubViewDescriptor($name);
			$sub->addValues($values);
			$this->addSubView($sub);
			return $sub;
		}
		
		/**
		 *	Removes the dynamic block with the given name from the template. 
		 *	If there are no parsing or removing instructions for a dynamic it will not be removed in the parsed template.
		 *
		 *	@param string $name The name of the dynamic block to be removed.
		 */
		function removeSubView($name){
			$this->remove[count($this->remove)] = $name;
		}
		
		/**
		 * An alias for removeSubView
		 * @see removeSubView
		 */
		function hideSubView($name){
			$this->removeSubView($name);
		}
		
		/**
		 * 	Returns the name of the view described by this object.
		 *	@return string
		 */
		function getName(){
			return $this->name;
		}
	}

?>