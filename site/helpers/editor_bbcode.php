<?php
class ObEditorBBcode {
	public $config = array('bold','italic','underline','hypelink','image','list','color','quote','youtube','source','cannedresponses','style'=>'height:250px;width:100%;');
	public function __construct( $config = null ) {
		if( $config ){
			$this->config = $config;
		}
		$doc = JFactory::getDocument();
		$js_ct 		= "var obhd_root_url = '".JURI::root()."components/com_obhelpdesk/assets/editor/wysiwyg-bbcode/';\n";
		
		$css_url 	= JURI::base().'components/com_obhelpdesk/assets/editor/wysiwyg-bbcode/editor.css';
		$js_url 	= JURI::base().'components/com_obhelpdesk/assets/editor/wysiwyg-bbcode/editor.js';
		$doc->addScriptDeclaration( $js_ct );
		$doc->addScript( $js_url );
		$doc->addStyleSheet( $css_url );
	}

	public function display($eid, $value='',$config=null){
		if( !$config ) $config = $this->config; 
		$html = '<div class="richeditor">';
		$html .= '<div class="editbar">';
		$html .= in_array( 'bold',$config ) ? '<button title="bold" onclick="doClick(\'bold\', \''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_bold"><b>B</b></button>':'';
		$html .= in_array( 'italic', $config ) ? '<button title="italic" onclick="doClick(\'italic\', \''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_italic"><i>I</i></button>':'';
		$html .= in_array( 'underline', $config ) ? '<button title="underline" onclick="doClick(\'underline\', \''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_underline"><u>U</u></button>':'';
		$html .= in_array( 'hypelink', $config  ) ? '<button title="hyperlink" onclick="doLink(\''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_hyperlink"></button>':'';
		$html .= in_array( 'image', $config ) ? '<button title="image" onclick="doImage(\''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_image"></button>':'';
		$html .= in_array( 'list', $config ) ? '<button title="list" onclick="doClick(\'InsertUnorderedList\', \''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_list"></button>':'';
		$html .= in_array( 'color', $config ) ? '<button title="color" onclick="showColorGrid2(\'none\', \'\', \''.$eid.'\' )" type="button" class="wysiwyg_bbcode_btn_color"></button><span id="colorpicker201_'.$eid.'" class="colorpicker201"></span>':'';
		$html .= in_array( 'quote', $config ) ? '<button title="quote" onclick="doQuote(\''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_quote"></button>':'';
// 		$html .= in_array( 'youtube', $config ) ? '<button title="youtube" onclick="InsertYoutube(\''.$eid.'\');" type="button" class="wysiwyg_bbcode_btn_youtube"></button>':'';
// 		$html .= in_array( 'source', $config ) ? '<button title="switch to source" type="button" onclick="javascript:SwitchEditor(\''.$eid.'\')" class="wysiwyg_bbcode_btn_source"></button>':'';
//  		$html .= in_array( 'cannedresponses', $config ) ? '<button title="Canned Responses" type="button" class="wysiwyg_bbcode_btn_cannedresponses modal_jform_replytemplate">'.JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE').'</button>':'';
//  		$html .= in_array( 'cannedresponses', $config ) ? '<button title="Canned Responses" type="button" rel="{handler: \'iframe\', size: {x: 800, y: 500}}" class="wysiwyg_bbcode_btn_cannedresponses modal_jform_replytemplate modal">'.JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE').'</button>':'';
 		$html .= in_array( 'cannedresponses', $config ) ? '<button title="'.JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE').'" type="button" onclick="javascript:opentListReplyTemplate();" class="wysiwyg_bbcode_btn_cannedresponses modal_jform_replytemplate">'.JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE').'</button>':'';
 		/*
 		<a class="btn btn-small btn modal_jform_replytemplate" title="Canned Responses" href="index.php?option=com_obhelpdesk&amp;view=replytemplates&amp;tid=1&amp;layout=modal&amp;tmpl=component&amp;field=jform_replytemplate" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
			Canned Responses</a>
 		 */
 		
 		$style = isset($config['style'])?'style="'.$config['style'].'"':'style="height:250px;width:100%;"';
 		
		$html .= '</div>';
		$html .= '<div class="editor-container">';
		$html .= '<textarea name="'.$eid.'" id="'.$eid.'" '.$style.'>'.$value.'</textarea>';
		$html .= '</div>';
		$html .= '</div>';

		$doc = JFactory::getDocument();

		$js_content = 'window.addEvent("domready", function() {';
		$js_content .= '	    initEditor("'.$eid.'", true);';
		$js_content .= '});';
		$doc->addScriptDeclaration($js_content);
		return $html;
	}
}