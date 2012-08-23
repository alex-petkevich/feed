<?php

/**
 * 
 * Класс подсветки HTML-кода
 *
 * Автор: Федоров Артем 2010
 *
 */
class HTMLHighlight
{
	/**
	 * Bufer color value, for HTML tags
	 *
	 */
	private $_buf_color = '';
	
	
	/**
	 * Colors
	 *
	 */
	private $_colors = array(
		'html' => array(
			'img' => array(
				'tags' => array('IMG','STYLE'),
				'color' => '#990099'
			),
			
			'table' => array(
				'tags' => array('TABLE','TBODY','TD','TFOOT','TH','THEAD','TR'),
				'color' => '#009999'
			),
			
			'a' => array(
				'tags' => array('A'),
				'color' => '#006600'
			),
			
			'script' => array(
				'tags' => array('SCRIPT'),
				'color' => '#990000'
			),
			
			'rest' => array(
				'tags' => array(
					'!DOCTYPE','ABBR','ACRONYM','ADDRESS','APPLET','AREA','B','BASE','BASEFONT','BDO','BGSOUND',
					'BIG','BLINK','BLOCKQUOTE','BODY','BR','BUTTON','CAPTION','CENTER','CITE','CODE','COL','COLGROUP',
					'DD','DEL','DFN','DIR','DIV','DL','DT','EM','EMBED','FIELDSET','FONT','FORM','FRAME','FRAMESET',
					'H1','H2','H3','H4','H5','H6','HEAD','HR','HTML','IFRAME','INPUT','INS','ISINDEX','KBD','LABEL',
					'LEGEND','LI','LINK','MAP','MARQUEE','MENU','META','NOBR','NOEMBED','NOFRAMES','NOSCRIPT','OBJECT',
					'OL','OPTGROUP','OPTION','P','PARAM','PLAINTEXT','PRE','Q','SAMP','SELECT','SMALL',
					'SPAN','STRIKE','STRONG','SUB','SUP','TEXTAREA','TITLE','TT','U','UL','VAR','WBR','XMP','I','S'
				),
				'color' => '#000099'
			),
			
			'attribute' => '#0000FF',
			'comment' => '#999999',
		),
	);
	
	
	/**
	 * Конструктор
	 *
	 */
	public function __construct()
	{
		array_walk($this->_colors['html']['img']['tags'], array(&$this, '_html_walk'));
		array_walk($this->_colors['html']['table']['tags'], array(&$this, '_html_walk'));
		array_walk($this->_colors['html']['a']['tags'], array(&$this, '_html_walk'));
		array_walk($this->_colors['html']['script']['tags'], array(&$this, '_html_walk'));
		array_walk($this->_colors['html']['rest']['tags'], array(&$this, '_html_walk'));
	}
	
	
	/**
	 * Parse html tags
	 *
	 */
	public function html($html)
	{
		// <style>...</style>
		$html = preg_replace_callback(
			'/(<style[^>]+>)([\D0-9]*)(<\/style>)/Umi',
			create_function(
				'$matches',
				'return $matches[1].htmlspecialchars($matches[2]).$matches[3];'
			),
			$html);
		
		// <script>...<\/script>
		$html = preg_replace_callback(
			'/(<script[^>]+>)([\D0-9]*)(<\/script>)/Umi',
			create_function(
				'$matches',
				'return $matches[1].htmlspecialchars($matches[2]).$matches[3];'
			),
			$html);
		
		$this->_html_exec($html, 'rest');
		$this->_html_exec($html, 'a');
		$this->_html_exec($html, 'table');
		$this->_html_exec($html, 'img');
		$this->_html_exec($html, 'script');
		
		// multiline comments
		$html = preg_replace("/(<!--)([\D0-9]+)(-->)/Umi",
							'<span style="color:'.$this->_colors['html']['comment'].';">&lt;&#33;--$2--&gt;</span>',
							$html);
		
		// clear highlight from multiline comments
		$html = preg_replace_callback(
				'/(<span style="color:'.$this->_colors['html']['comment'].';">&lt;&#33;--)([\D0-9]+)(--&gt;<\/span>)/U',
				array(&$this, '_clear_hl'),
				$html);
		
		return $this->_line_numbers($html);
	}
	
	
	/**
	 * Execute html tags
	 *
	 */
	private function _html_exec(&$html, $type)
	{
		$tags = $this->_colors['html'][$type]['tags'];
		$color = $this->_colors['html'][$type]['color'];
		
		$this->_buf_color = $color;
		
		$tags = implode($tags, '|');
		$html = preg_replace_callback('/('.$tags.')/i', array(&$this, '_html_replace'), $html);
	}
	
	
	/**
	 * only for using in callback function
	 *
	 */
	private function _html_replace($matches)
	{
		$tag = str_replace(array('<','>'),array('&lt;','&gt;'), $matches[1]);
		$tag = preg_replace('/(")([^"]+)(")/i',
							'<span style="color:'.$this->_colors['html']['attribute'].'">&quot;$2&quot;</span>',
							$tag);
		return '<span style="color:'.$this->_buf_color.'">'.$tag.'</span>';
	}
	
	
	/**
	 * Prepare array of tags for preg replace
	 * only for using in callback function
	 *
	 */
	private function _html_walk(&$value, $key)
	{
		$value = '<'.$value.'>|<'.$value.'\s[^>]*>|<\/'.$value.'>';
	}
	
	
	/**
	 * Clear highlight from strings and comments
	 * only for using in callback functions
	 *
	 */
	private function _clear_hl($matches)
	{
		$comment = preg_replace('/(<span[^>]+>|<\/span>)/Umi', '', $matches[2]);
		return $matches[1].$comment.$matches[3].(isset($matches[4])?$matches[4]:'');
	}
	
	
	/**
	 * Добавить цифры к коду
	 *
	 */
	private function _line_numbers($code)
	{
		$code = explode(PHP_EOL, $code);
		$ret = '<pre>';
		foreach ($code as $line => $string) $ret .= rtrim($string) . '<br>';
		$ret .= '</pre>';
		return $ret;
	}
	
	
	/**
	 * Html entities
	 *
	 */
	private function _html_entities($string)
	{
		$chars = array('/', '\\', '\'');
		$ascii = array('&#47;', '&#92;', '&#39;');
		
		return str_replace($chars, $ascii, $string);
	}
}

?>