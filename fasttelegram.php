<?php
/**
 * @package		Joomla.Site
 * @subpackage	plg_content_contentforcrocodile
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

class plgContentFasttelegram extends JPlugin
{
/**
	 * Example after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 * @param   object   $article  A JTableContent object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean   true if function not enabled, is in frontend or is new. Else true or
	 *                    false depending on success of save function.
	 *
	 * @since   1.6
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
			// sed session - > id
			$articleid = $article->id;
			$session = JFactory::getSession();
			$session->set('successfullSavecontent', "{$articleid}");
     

	}// end func onContentAfterSave

	function onAfterDispatch(){

		$jinput = JFactory::getApplication()->input;
		$contentcheak = $jinput->getString('option', '');

			if ($contentcheak == "com_content") {
				$application = JFactory::getApplication();
	 			$session = JFactory::getSession(); 

		 	if (($session->get('successfullSavecontent') != null)) {

		 		$articleid = intval($session->get( 'successfullSavecontent'));

		 		// cheak articleid in DB
				$db     = JFactory::getDbo();
			            $query  = $db->getQuery(true);
			            $query->select('*');
			            $query->from($db->qn('#__telegram'));
			            $query->where($db->qn('article_id')." = ".$db->q($articleid));
			            $query->where('published = 0');
			            $db->setQuery($query);
			            $count  = $db->loadRow();

		 		//send to telegram
			           if ($count != null) {		           
				 	require_once JPATH_SITE .'/plugins/content/fasttelegram/telegram-bot-api.php';
					// $channel_id = "@testtrangell";
					// $token = "280854533:AAFMycAWCbxGkM9LvMrsMTCIGghrzMLIRtw";
					$token = $this->params->get('token');
					$channel_id = $this->params->get('channel_id');
					$bot = new telegram_bot($token);
					$testlink = JURI::current();
					if (!empty($count[4])) {
						$bot->send_photo($channel_id,$count[4],"{$count[1]}\r\n لینک مطلب : \r\n" . JURI::root(). "index.php/" . $articleid);
					}else {
						$bot->send_message($channel_id,"{$count[1]}\r\n لینک مطلب : \r\n" . JURI::root(). "index.php/" . $articleid);
					}
					// Message in the  option = com_content
					$application->enqueueMessage("مطلب {$articleid} به تلگرام ارسال شد. توجه کنید هر مطلب فقط یک بار ارسال می گردد.", 'Warning');

					// update after send telegram
					$query = $db->getQuery(true);
					$query->clear();
					$query->update('#__telegram');
					$query->set($db->qn('published').' = 1'); 
					$query->where($db->qn('article_id')." = ".$db->q($articleid));
					$db->setQuery((string)$query);
					$db->query();
				}
			}

			// clear active  session
			if ($session->isActive('successfullSavecontent')) {
			        $session->clear('successfullSavecontent');
			}

		}
	} // end func onAfterDispatch


	function onContentPrepareForm($form, $data) {

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
	}// end func onContentPrepareForm
}