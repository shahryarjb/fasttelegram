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
	public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function onContentPrepareData($context, $data) {
	
		if (is_object($data))
		{ 
			$articleId = isset($data->id) ? $data->id : 0;
			
			if ($articleId > 0) { 
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('message, url');
				$query->from($db->qn('#__telegram'));
				$query->where($db->qn('article_id') . ' = ' . $db->q($articleId));
				$db->setQuery($query);
				$results = $db->loadAssoc();

				// Check for a database error.
				if ($db->getErrorNum()) {
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}
				
				// show data.
				$data->rating = array();
				$data->rating['message'] = $results['message'];
				$data->rating['url'] = $results['url'];
			}
			
		}

		return true;
	}

	function onContentPrepareForm($form, $data)
	{
		
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		
		if ( $form->getName() != "com_content.article" ) {
			return true;
		}
		// Add the extra fields to the form.
		// need a seperate directory for the installer not to consider the XML a package when "discovering"
		JForm::addFormPath(dirname(__FILE__) . '/rating');
		$form->loadFile('rating', false);

		return true;
	}

	public function onContentAfterSave($context, $article, $isNew) // Article is passed by value for joomla 3.x
	{
		// sed session - > id
			$articleid = $article->id;
			$session = JFactory::getSession();
			$session->set('successfullSavecontent', "{$articleid}");

		if (!in_array($context , array('com_content.article'))) {
			return true; 
		}
		
		$jinput = JFactory::getApplication()->input;
		$form = $jinput->post->get('jform', 'null', 'array');
	
		if (is_array($form)) {
			$test['message'] = $form['rating']['message'];
			$test['url'] = $form['rating']['url'];
		}
		else {
			return true ;
		}

		$articleId	= $article->id; // get article id
		$chek = (count($this::searchRating($articleid)) > 0) ? 1 : 0 ; // search for exsit record
		$publish = ($article->publish_down != "") ? 1 : 0; // get published
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		if (!$chek){	 // ========================= insert
				$columns = array('message', 'url','published','article_id');
				$values = array($db->q($test['message']),$db->q($test['url']),$db->q($publish) ,$db->q($articleId));
				$query->insert($db->qn('#__telegram'));
				$query->columns($db->qn($columns));
				$query->values(implode(',', $values));
				$db->setQuery($query);
				$db->execute(); 
				$query->clear(); 
		}
		else { // ========================= update
				$fields = array(
					$db->qn('message') . ' = ' . $db->q($test['message']),
					$db->qn('url') . ' = ' . $db->q($test['url']),
					$db->qn('published') . ' = ' . $db->q($publish)
				);
			
				$conditions = array($db->qn('article_id') . ' = '. $db->q($articleId));
				$query->update($db->qn('#__telegram'));
				$query->set($fields);
				$query->where($conditions);		
				$db->setQuery($query);
				$db->execute(); 		
				$query->clear(); 
		}
		
		return true;
	}

	public function onContentAfterDelete($context, $article) {
		if ($article->id) {
			try
			{	
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->delete($db->qn('#__telegram'));
				$query->where($db->qn('article_id') . ' = ' . $db->q($article->id));
				$db->setQuery($query);	
				$db->execute(); 
			}
			catch (RuntimeException $e) { 
				echo $e->getMessage(); 
			}
		}
		return true;
	}

	public function searchRating($articleId)	{ 
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__telegram');
		$query->where('article_id =  ' . $db->quote($articleId));
		$db->setQuery($query);
		$result = $db->loadObjectlist();
		return $result;
	}

	function onAfterDispatch(){

		$jinput = JFactory::getApplication()->input;
		$contentcheak = $jinput->getString('option', '');

		if ($contentcheak == "com_content") {
			$application = JFactory::getApplication();
			$session = JFactory::getSession(); 

			if (($session->get('successfullSavecontent') != null)) {

				$articleid = intval($session->get( 'successfullSavecontent'));

				if ($this::searchRating($articleid)[0]->published == 1) {

					$count  = $this::searchRating($articleid)[0]; // get data 

					//send to telegram		           
					require_once JPATH_SITE .'/plugins/content/fasttelegram/telegram-bot-api.php';
					$token = $this->params->get('token');
					$channel_id = $this->params->get('channel_id');
					$bot = new telegram_bot($token);
					$testlink = JURI::current();
					if (!empty($count->message)) {
						if ($count->url != "") {
							$bot->send_photo($channel_id,$count->url,"{$count->message}\r\n لینک مطلب : \r\n" . JURI::root(). "index.php/" . $articleid);
						}else {
							$bot->send_message($channel_id,"{$count->message}\r\n لینک مطلب : \r\n" . JURI::root(). "index.php/" . $articleid);
						}
					}
					// Message in the  option = com_content
					$application->enqueueMessage("مطلب {$articleid}  ارسال شد. توجه داشته باشید اگر هر مطلب دوباره ویرایش و ذخیره شود مطلب فوق دوباره به تلگرام ارسال می گردد.", 'Warning');
				}
			}

			// clear active  session
			if ($session->isActive('successfullSavecontent')) {
				$session->clear('successfullSavecontent');
			}

		}
	} // end func onAfterDispatch


	
}
