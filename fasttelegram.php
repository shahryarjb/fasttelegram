<?php
/**
 * @package		Joomla.Site
 * @subpackage	plg_content_contentforcrocodile
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');
jimport( 'joomla.form.form' );
require_once JPATH_SITE .'/plugins/content/fasttelegram/telegram-bot-api.php';

/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgContentFasttelegram extends JPlugin
{

	function onContentPrepareForm($form, $data)
	{

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		//------------------load form from file-----------------------------
		JForm::addFormPath(dirname(__FILE__) . '/fasttelegram');
		$form->loadFile('fasttelegram', false);
		//-----------------get data from form--------------------------------
		
		
			
		foreach ($data as $field) {
			if(gettype($field) == "array") {
				foreach($field as $key => $value) {
					if($key == "message") {
						$message = $value;
					}
					if($key == "url") {
						$url = $value;
					}
				}
			}
		}	
		if(isset($message)) {
					$db = JFactory::getDbo();
					$db->setQuery("INSERT INTO #__telegram SET message='" .$message."',article_id=" . $data->id . ",url='" . $url . "'");
					$db->execute();
				} // end if




		return true;
	}


	public function onContentPrepare($context, &$article, &$params, $limitstart) {
		//----------------------------------check if user is in front page------------------------------
    // $app = JFactory::getApplication();
    // $menu = $app->getMenu();
    // if ($menu->getActive() == $menu->getDefault()) {
    //------------------------show label in front page------------------------------------------
		//--------------------------get data from #__crocodilecontent-------------------------------------------
		$db = JFactory::getDbo();
		$query = $db->getQuery(true); 
		
			$query->select('co.*');
			$query->from('#__telegram as co');
			$query->where('published = 0');
			$query->leftJoin('#__content as c ON co.article_id=c.id');	  
			$db->setQuery($query);
			$varDB = $db->loadAssocList();
		//----------------------------------------------------------------------------------------------
		for($i = 0; $i < 10; $i++) {
            if(isset($varDB[$i]["message"])) {
				 if($article->id == $varDB[$i]["article_id"]) {

				$article->text = $varDB[$i]["message"]. $article->text;
				$token = $this->params->get('token');
				$channel_id = $this->params->get('channel_id');
				$link = JURI::root()."index.php/".$article->id; 
				$link = preg_replace('#^http?://#', '', $link);
				$linkk = "http://www." . $link;
				//$link = "http://www.localhost/joomla3/index.php/60";
				$bot = new telegram_bot($token);
				if($varDB[$i]["url"] == null){
					$bot->send_message($channel_id,$varDB[$i]["message"]. "  ".$linkk);
					
				}
				if(isset($varDB[$i]["url"])) {
					$bot->send_photo($channel_id,$varDB[$i]["url"],$varDB[$i]["message"]);
				}
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->update('#__telegram');
					$query->set("published=1"); 
					$query->where('article_id=' . $article->id); 
					$db->setQuery($query);
					$db->query();
				 }
			}
		}
		//}
	}
}


//$token = "286077226:AAFw7lRchn_aDouZOT9JGbLmuneG6ep7pYo";
//				$channel_id = "@testmina";