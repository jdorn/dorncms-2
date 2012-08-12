<?php
namespace DornCMS\Twig;

/**
 * Custom parser implementation.
 * This parses a template into sections and is used to provide a template
 * editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class Parser implements \Twig_ParserInterface
{
	protected $stream;
	
    /**
     * Converts a token stream to an array of sections
     *
     * @param Twig_TokenStream $stream A token stream instance
     *
     * @return array An array of sections
     */
    public function parse(\Twig_TokenStream $stream)
    {
		$this->stream = $stream;
		
		$return = array();
		
		$source = '';
		
		while(1) {
			try {
				$token = $stream->next();
			}
			catch(\Exception $e) {
				if($source) $return[] = $source;
				break;
			}
			
			//if this is the start of a twig code block (i.e. "{%")
			if($token->getType() === \Twig_Token::BLOCK_START_TYPE) {
				if($source) $return[] = $source;
				$source = $this->getTokenValue($token);
				
				//get the type of code block
				$next = $stream->next();
				$source .= $this->getTokenValue($next);
				
				if(trim($next->getValue()) === 'block') {
					//get name of block
					$name = null;
					$source .= $this->parseUntil(\Twig_Token::BLOCK_END_TYPE, $name);
					
					$body = null;
					$source .= $this->parseUntilTag('endblock',$name,$body);
					
					$return[] = new BlockSection($name, $source, $body);
					$source = '';
				}
				elseif(trim($next->getValue()) === 'extends') {
					$name = null;
					$source .= $this->parseUntil(\Twig_Token::BLOCK_END_TYPE, $name);
					
					$return[] = new ExtendsSection($source, $name);
					$source = '';
				} 
			}
			else {
				$source .= $this->getTokenValue($token);
			}
		}
		
		return $return;
	}
	
	protected function parseUntil($token_type, &$not_including = null) {
		$source = '';
		$buffer = '';
		
		while(1) {
			$source .= $buffer;
			$buffer = '';
			
			$token = $this->stream->next();
			$buffer .= $this->getTokenValue($token);
			
			//if this is the tag we're looking for
			if($token->getType() === $token_type) {
				$not_including = $source;
				
				return $source . $buffer;
			}
		}
	}
	
	protected function parseUntilTag($tagname, $tagvalue, &$not_including = null) {
		$source = '';
		$buffer = '';
		
		while(1) {
			$source .= $buffer;
			$buffer = '';
			
			$token = $this->stream->next();
			$buffer .= $this->getTokenValue($token);
			
			//if this is the start of a twig code block (i.e. "{%")
			if($token->getType() === \Twig_Token::BLOCK_START_TYPE) {
				$next = $this->stream->next();
				$buffer .= $this->getTokenValue($next);
				
				if(trim($next->getValue()) === $tagname) {
					$value = null;
					$buffer .= $this->parseUntil(\Twig_Token::BLOCK_END_TYPE, $value);
					
					if($value === $tagvalue) {
						$not_including = $source;
						
						return $source . $buffer;
					}
				}
			}
		}
	}
	
	protected function getTokenValue($token) {
		switch($token->getType()) {
			case \Twig_Token::BLOCK_START_TYPE:
				return '{% ';
			case \Twig_Token::BLOCK_END_TYPE:
				return '%} ';
			case \Twig_Token::VAR_START_TYPE:
				return '{{ ';
			case \Twig_Token::VAR_END_TYPE:
				return '}} ';
			case \Twig_Token::EOF_TYPE:
				return '';
			case \Twig_Token::STRING_TYPE:
				return '"'.addslashes($token->getValue()).'" ';
			default:
				return $token->getValue().' ';
		}
	}
}
