<?php 
defined('JPATH_PLATFORM') or die;

class obHelpDeskMail
{
	var $htmlmsg;
	var $plainmsg;
	var $charset;
	var $attachments;
	var $header;
	
	var $mbox;
	var $mid;
	var $structure;
	
	//loai bo phan reply cua message
	function gmailReply()
	{
		$text = $this->htmlmsg ? $this->htmlmsg : $this->plainmsg;
		$result = preg_split('/<body[^>]+>|<\/body>/i', $text);
		if(isset($result[1]) && $result[1]){
			$text = $result[1];
		}
		$pos = strpos($text, "<div class=\"gmail_quote\">");
		if ($pos === false) {

		} else {
			$text = substr($text,0, $pos);
			return $text;
		}

		###################################
		$pos = strpos($text, "<div class=\"moz-cite-prefix\">");
		if ($pos === false) {

		} else {
			$text = substr($text,0, $pos);
			return $text;
		}
		return $text;
	}
	
	function obHelpDeskMail($mbox, $mid)
	{
		$this->mbox = $mbox;
		$this->mid = $mid;
		
		$this->structure = imap_fetchstructure($this->mbox, $this->mid);		
		if (empty($this->structure))
			return false;
		
		$this->_getMessage();
		$this->_getAttachments();
		$this->_decodeHeaders();
		
		if ($this->charset != 'UTF-8')
		{
			$this->plainmsg = iconv($this->charset, 'UTF-8', $this->plainmsg);
			$this->htmlmsg = iconv($this->charset, 'UTF-8', $this->htmlmsg);
		}
	}
	
	function _decodeHeaders()
	{
		$headers = imap_headerinfo($this->mbox, $this->mid);
		if (empty($headers))
			return false;
		
		foreach ($headers as $header => $value)
			if (!is_array($value))
			{
				$obj = imap_mime_header_decode($value);
				$obj = $obj[0];
				
				$obj->charset = strtoupper($obj->charset);
				
				if ($obj->charset != 'DEFAULT' && $obj->charset != 'UTF-8')
					$obj->text = iconv($obj->charset, 'UTF-8', $obj->text);
				
				$headers->$header = $obj;
			}
		
		$this->header= $headers;
	}
	
	function _getAttachments()
	{
		if (!isset($this->structure->parts)) return;
		if (!count($this->structure->parts)) return;
		
		$parts = count($this->structure->parts);
		for ($i=0; $i<$parts; $i++)
		{
			$is_attachment = false;
			
			$new_attachment = array(
				'filename' => '',
				'name' => '',
				'contents' => ''
			);
			
			if ($this->structure->parts[$i]->ifdparameters)
				foreach ($this->structure->parts[$i]->dparameters as $object)
					if (strtolower($object->attribute) == 'filename')
					{
						$is_attachment = true;
						$new_attachment['filename'] = $object->value;
					}
			
			if ($this->structure->parts[$i]->ifparameters)
				foreach ($this->structure->parts[$i]->parameters as $object)
					if (strtolower($object->attribute) == 'name')
					{
						$is_attachment = true;
						$new_attachment['filename'] = $object->value;
					}
			
			if ($is_attachment)
			{
				$new_attachment['contents'] = imap_fetchbody($this->mbox, $this->mid, $i+1);
				
				// 3 = BASE64
				if ($this->structure->parts[$i]->encoding == 3)
					$new_attachment['contents'] = base64_decode($new_attachment['contents']);
				// 4 = QUOTED-PRINTABLE
				elseif ($this->structure->parts[$i]->encoding == 4)
					$new_attachment['contents'] = quoted_printable_decode($new_attachment['contents']);
				
				if ($is_attachment)
					$this->attachments[] = $new_attachment;
			}
		}
	}

	function _getMessage()
	{		
		$this->htmlmsg = $this->plainmsg = $this->charset = '';
		$this->attachments = array();

		// BODY
		// not multipart
		if (empty($this->structure->parts))
			$this->_getPart($this->structure, 0);
		else
			// multipart: iterate through each part
			foreach ($this->structure->parts as $partno0 => $p)
				$this->_getPart($p, $partno0+1);
	}
	
	function _getPart($p, $partno)
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc if multipart, 0 if not multipart

		// DECODE DATA
		if ($partno)
			$data = imap_fetchbody($this->mbox, $this->mid, $partno);
		else
			$data = imap_body($this->mbox, $this->mid);
			
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding == 4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding == 3)
			$data = base64_decode($data);
		// no need to decode 7-bit, 8-bit, or binary

		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if (!empty($p->parameters))
			foreach ($p->parameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;
		if (!empty($p->dparameters))
			foreach ($p->dparameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;

		// TEXT
		if ($p->type == 0 && $data)
		{
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype)=='plain')
				$this->plainmsg .= trim($data) ."\n\n";
			else
				$this->htmlmsg .= $data .'<br /><br />';
			$this->charset = $params['charset'];  // assume all parts are same charset
		}

		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type == 2 && $data)
			$this->plainmsg .= trim($data) ."\n\n";

		// SUBPART RECURSION
		if (!empty($p->parts))
			foreach ($p->parts as $partno0 => $p2)
				$this->_getPart($p2, $partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
	}
}