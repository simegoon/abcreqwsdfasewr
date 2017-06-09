<?php

/***
扩展 DOMDocument DOMElement 为 DOMDocumentEx DOMElementEx 类，使其支持使用css select语法获取元素
***/

class CssSelectAttr
{
	public $name;
	public $op;
	public $value;
	function __construct($name,$op,$value)
	{
		$this->name = $name;
		$this->op =  $op;
		$this->value = $value;
	}
	function isMatch($value)
	{
		return $this->value == trim($value);
	}
}


class CssSelectAttrClass extends CssSelectAttr
{
	function isMatch($value)
	{
		return in_array($this->value,explode(" ",trim($value)));
	}
}

class CssSelect
{
	public $tagName;
	public $attrs = array();
	function __construct($tagName="*")
	{
		$this->tagName = $tagName;
	}
	function addAttr($name,$op,$value)
	{
		if($name == "class"){
			$this->attrs[] = new CssSelectAttrClass($name,$op,$value);
		}
		else if($name == "tagName"){
			$this->tagName = $value;
		}
		else{
			$this->attrs[] = new CssSelectAttr($name,$op,$value);
		}
	}
}

function getElementsBySelect($dom,$attrs)
{
	$dom_list = array();

    foreach ($dom->getElementsByTagName($attrs->tagName) as $item)  
    {
    	$match = true;
    	foreach($attrs->attrs as $attr){
        	if(!$attr->isMatch($item->getAttribute($attr->name)))
        	{
        		$match = false;
        		break;
        	}
        }
        if($match)$dom_list[] = new DOMElementEx($item);
    }
   	return $dom_list;
}

function getElementsBySelects($dom,$attrs)
{
	$dom_list = array($dom);
	$dom_list_new = array();
	foreach($attrs as $attr){
		foreach($dom_list as $dom_item)
    		$dom_list_new = array_merge($dom_list_new,getElementsBySelect($dom_item,$attr));
    	$dom_list = $dom_list_new;
    	$dom_list_new = array();
    }
   	return $dom_list;
}

function parse_selector($selector_string)
{
	$selector_list = explode(" ",$selector_string);
	$ret_selector = array();
	foreach($selector_list as $selector)
	{
		$selector = preg_replace("/\.([\w- ]+)/is", "[class=$1]", $selector);
		$selector = preg_replace("/\#([\w-]+)/is", "[id=$1]", $selector);
		$selector = preg_replace("/^([\w-]+)/is", "[tagName=$1]", $selector);	
		$pattern = "/\[([\w-:]+)([!*^$]?=)([\w-: ]+)\]/is";
    	preg_match_all($pattern, trim($selector).' ', $matches, PREG_SET_ORDER);
    	
		$select = new CssSelect;
    	foreach($matches as $m){
    		$select->addAttr( $m[1], $m[2], $m[3]);
    	}
    	$ret_selector[] = $select;
	}
	return $ret_selector;
}

class DOMElementEx
{
	public $dom;
	public function __construct ($dom_element=false){
		if($dom_element)
        	$this->dom = $dom_element;
        else
        	$this->dom = new DOMElement("___null___");
    }
    function isnull()
    {
    	return $this->dom->localName == "___null___";
    }
    function __call($name, $args)
    {
    	if (isset($this->dom) && method_exists($this->dom, $name))
        {
            return call_user_func_array(array($this->dom, $name), $args);
        }
        die("methon not exit $name");
    }
    function __get($name)
    {
    	return $this->dom->$name;
    }
    function __set($name,$value)
    {
    	return $this->dom->$name = $value;
    }
	public function getElement($css_select)
	{
		$ret = $this->getElements($css_select);
		//为了最大通用性，返回一个kong的元素
		if(count($ret)==0){
			return new DOMElementEx();
		}
		return current($ret);
	}

	public function getElements($css_select)
	{
		$selects = parse_selector($css_select);
	   	return getElementsBySelects($this,$selects);
	}
}

class DOMDocumentEx extends DOMDocument
{
	public function getElement($css_select)
	{
		$ret = $this->getElements($css_select);
		//为了最大通用性，返回一个kong的元素
		if(count($ret)==0){
			return new DOMElementEx();
		}
		return current($ret);
	}
	// 为简化处理和防止解析出错，不解析script和style
	public function loadHTML($content, $options = NULL)
	{
		$content=preg_replace("/&(?!(?:apos|quot|[gl]t|amp);|#)/", "&amp;", $content);
		$content=preg_replace("/<script[\s\S]*?<\/script>/i", "", $content);
		$content=preg_replace("/<style[\s\S]*?<\/style>/i", "", $content);
		parent::loadHTML($content);
	}

	public function getElements($css_select)
	{
		$selects = parse_selector($css_select);
		return getElementsBySelects($this,$selects);
	}
}


?>
