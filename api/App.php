<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerberus Helpdesk Team
 * 
 * Sure, it would be so easy to just cheat and edit this file to use the 
 * software without paying for it.  But we trust you anyway.  In fact, we're 
 * writing this software for you! 
 * 
 * Quality software backed by a dedicated team takes money to develop.  We 
 * don't want to be out of the office bagging groceries when you call up 
 * needing a helping hand.  We'd rather spend our free time coding your 
 * feature requests than mowing the neighbors' lawns for rent money. 
 * 
 * We've never believed in encoding our source code out of paranoia over not 
 * getting paid.  We want you to have the full source code and be able to 
 * make the tweaks your organization requires to get more done -- despite 
 * having less of everything than you might need (time, people, money, 
 * energy).  We shouldn't be your bottleneck.
 * 
 * We've been building our expertise with this project since January 2002.  We 
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to 
 * let us take over your shared e-mail headache is a worthwhile investment.  
 * It will give you a sense of control over your in-box that you probably 
 * haven't had since spammers found you in a game of "E-mail Address 
 * Battleship".  Miss. Miss. You sunk my in-box!
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas and the warm fuzzy 
 * feeling of feeding a couple obsessed developers who want to help you get 
 * more done than 'the other guy'.
 *
 * - Jeff Standen, Mike Fogg, Brenan Cavish, Darren Sugita, Dan Hildebrandt
 * 		and Joe Geck.
 *   WEBGROUP MEDIA LLC. - Developers of Cerberus Helpdesk
 */

class ChiPhonePageController extends DevblocksControllerExtension {
    const ID = 'cerberusweb.controller.iphone';
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
    
	/**
	 * Enter description here...
	 *
	 * @param string $uri
	 * @return string $id
	 */
	public function _getPageIdByUri($uri) {
        $pages = DevblocksPlatform::getExtensions('cerberusweb.iphone.page', false);
        foreach($pages as $manifest) { /* @var $manifest DevblocksExtensionManifest */
            if(0 == strcasecmp($uri,$manifest->params['uri'])) {
                return $manifest->id;
            }
        }
        return NULL;
	}    
    
	public function handleRequest(DevblocksHttpRequest $request) { /* @var $request DevblocksHttpRequest */
		$path = $request->path;
		$prefixUri = array_shift($path);		// $uri should be "iphone"
		$controller = array_shift($path);	// sub controller to take (login, display, etc)

        $page_id = $this->_getPageIdByUri($controller);
		
        $pages = DevblocksPlatform::getExtensions('cerberusweb.iphone.page', true);
        @$page = $pages[$page_id]; /* @var $page CerberusPageExtension */
		
		if(empty($page)) {
			switch($controller) {
//				case "portal":
//					die(); // 404
//					break;
//	        		
				default:
					return;
					break;
			}
		}
		
		$pageAction = DevblocksPlatform::importGPC($_REQUEST['pageAction']);
		@$action = null != $pageAction ? $pageAction . 'Action' : array_shift($path) . 'Action';
		
		switch($action) {
	        case NULL:
	            // [TODO] Index/page render
	            break;
	            
	        default:
			    // Default action, call arg as a method suffixed with Action
			    if($page->isVisible()) {
					if(method_exists($page,$action)) {
						call_user_func(array(&$page, $action)); // [TODO] Pass HttpRequest as arg?
					}
				} else {
					// if Ajax [TODO] percolate isAjax from platform to handleRequest
					// die("Access denied.  Session expired?");
				}

	            break;
	    }		
	}
	
	public function writeResponse(DevblocksHttpResponse $response) { /* @var $response DevblocksHttpResponse */
	    $path = $response->path;
	    $uri_prefix = array_shift($path); // should be mobile
	    
		// [JAS]: Ajax? // [TODO] Explore outputting whitespace here for Safari
//	    if(empty($path))
//			return;

		$tpl = DevblocksPlatform::getTemplateService();
		$session = DevblocksPlatform::getSessionService();
		$settings = DevblocksPlatform::getPluginSettingsService();
		$translate = DevblocksPlatform::getTranslationService();
		$visit = $session->getVisit();

		$controller = array_shift($path);

		$pages = DevblocksPlatform::getExtensions('cerberusweb.iphone.page', true);

		// Default page [TODO] This is supposed to come from framework.config.php
		if(empty($controller)) 
			$controller = 'home';

	    // [JAS]: Require us to always be logged in for Cerberus pages
	    // [TODO] This should probably consult with the page itself for ::authenticated()
		if(empty($visit))
			$controller = 'login';
		
		$page_id = $this->_getPageIdByUri($controller);
		@$page = DevblocksPlatform::getExtension($page_id, true); /* @var $page CerberusPageExtension */
        
        if(empty($page)) {
   		    header("Status: 404");
        	return; // [TODO] 404
		}
        
	    
		// [TODO] Reimplement
		if(!empty($visit) && !is_null($visit->getWorker())) {
		    DAO_Worker::logActivity($page->getActivity());
		}
		
		// [JAS]: Listeners (Step-by-step guided tour, etc.)
	    $listenerManifests = DevblocksPlatform::getExtensions('devblocks.listener.http');
	    foreach($listenerManifests as $listenerManifest) { /* @var $listenerManifest DevblocksExtensionManifest */
	         $inst = $listenerManifest->createInstance(); /* @var $inst DevblocksHttpRequestListenerExtension */
	         $inst->run($response, $tpl);
	    }
		
        // [JAS]: Variables provided to all page templates
		$tpl->assign('settings', $settings);
		$tpl->assign('session', $_SESSION);
		$tpl->assign('translate', $translate);
		$tpl->assign('visit', $visit);
		
	    $active_worker = CerberusApplication::getActiveWorker();
	    $tpl->assign('active_worker', $active_worker);
	
	    if(!empty($active_worker)) {
	    	$active_worker_memberships = $active_worker->getMemberships();
	    	$tpl->assign('active_worker_memberships', $active_worker_memberships);
	    }
		
		$tpl->assign('pages',$pages);		
		$tpl->assign('page',$page);

		$tpl->assign('response_uri', implode('/', $response->path));
		
		$tpl->assign('core_tpl', $this->_TPL_PATH);
		
		// Timings
		$tpl->assign('render_time', (microtime(true) - DevblocksPlatform::getStartTime()));
		if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage')) {
			$tpl->assign('render_memory', memory_get_usage() - DevblocksPlatform::getStartMemory());
			$tpl->assign('render_peak_memory', memory_get_peak_usage() - DevblocksPlatform::getStartPeakMemory());
		}
		
//		var_dump($path);
		$tpl->display($this->_TPL_PATH.'index.tpl');
	}
};

class ChiPhoneHomePage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->display('file:' . $this->_TPL_PATH . 'home.tpl');
	}
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

class ChiPhoneLoginPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	
	public function render() {
		
		// draws HTML form of controls needed for login information
		$tpl = DevblocksPlatform::getTemplateService();
		
		// add translations for calls from classes that aren't Page Extensions (mobile plugin, specifically)
		$translate = DevblocksPlatform::getTranslationService();
		$tpl->assign('translate', $translate);
		
		$request = DevblocksPlatform::getHttpRequest();
		

		$original_path = (sizeof($request->path)==0) ? 'login' : implode(',',$request->path);

		$tpl->assign('original_path', $original_path);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'login.tpl');
	}
	
	public function authenticateAction() {
		//echo "authing!";
		@$email = DevblocksPlatform::importGPC($_REQUEST['email']);
		@$password = DevblocksPlatform::importGPC($_REQUEST['password']);
	    @$original_path = DevblocksPlatform::importGPC($_REQUEST['original_path']);
	    
		// log the worker in
		$worker = DAO_Worker::login($email, $password);
		
		if(!is_null($worker)) {
			$session = DevblocksPlatform::getSessionService();
			$visit = new CerberusVisit();
			$visit->setWorker($worker);

			$session->setVisit($visit);
			
			// turn original path into an array...			
			$original_path = explode(',', $original_path);
			
			// redirect
			$devblocks_response = new DevblocksHttpResponse($original_path);	
		} else {
			$devblocks_response = new DevblocksHttpResponse(array('iphone', 'login'));
			//return false;
		}
		DevblocksPlatform::redirect($devblocks_response);
	}
	
	public function signoffAction() {
		$session = DevblocksPlatform::getSessionService();
		$visit = $session->getVisit();
		
		DAO_Worker::logActivity(new Model_Activity(null));
		
		$session->clear();
//		DevblocksHttpResponse::
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('iphone', 'login')));
	}
	
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

class ChiPhoneTicketsPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	const VIEW_TICKET_OVERVIEW = 'mail_overview';
	const VIEW_TICKET_WORKFLOW = 'mail_workflow';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	public function render() {
		$translate = DevblocksPlatform::getTranslationService();
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
			
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // tickets
		$action = array_shift($path); // current action
		
		
		$active_worker = CerberusApplication::getActiveWorker();
		
		$memberships = $active_worker->getMemberships();
		
		switch($action) {
			case 'overview':
				$page = array_shift($path); // page
				
				$defaults = new C4_AbstractViewModel();
				$defaults->class_name = 'View_Ticket_iPhone';
				$defaults->id = self::VIEW_TICKET_OVERVIEW;
				$defaults->name = $translate->_('crm.tab.title');
				$defaults->renderSortBy = SearchFields_Ticket::TICKET_UPDATED_DATE;
				$defaults->renderSortAsc = 0;
				$defaults->renderLimit = 10;
				
				$view = C4_AbstractViewLoader::getView(self::VIEW_TICKET_OVERVIEW, $defaults);
				
				$view->params = array(
					SearchFields_Ticket::TICKET_CLOSED => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_CLOSED,'=',CerberusTicketStatus::OPEN),
					SearchFields_Ticket::TICKET_WAITING => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_WAITING,'=',0),
					SearchFields_Ticket::TICKET_TEAM_ID => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_TEAM_ID,'in',array_keys($memberships)),
				);
				if(isset($page)) {
					$view->renderPage = $page;
				}
				
				C4_AbstractViewLoader::setView($view->id, $view);
				
				$uri = "tickets/overview";
				$tpl->assign('uri', $uri);
				
				$tpl->assign('view', $view);
				$tpl->display('file:' . $this->_TPL_PATH . 'tickets/overview.tpl');
				break;
			case 'display':
				$id = array_shift($path); // ticket id
				$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.ticket.display.tab', false);
				$tpl->assign('tab_manifests', $tab_manifests);

				$ticket = DAO_Ticket::getTicket($id);
				
				$selected_tab = array_shift($path);
				$selected_tab = null != $selected_tab ? $selected_tab : 'conversation'; // tab
				
				foreach($tab_manifests as $tab_mft)
				{
					if($selected_tab==$tab_mft->params['uri']) {
						$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
					}
				}
				
				$tpl->assign('ticket', $ticket);
				$tpl->assign('tab', $tab);
				$tpl->assign('selected_tab', $selected_tab);
				$tpl->display('file:' . $this->_TPL_PATH . 'tickets/display.tpl');
				break;
			case 'properties':
				$tpl->display('file:' . $this->_TPL_PATH . 'tickets/properties.tpl');
				break;
			case null:
				$tpl->display('file:' . $this->_TPL_PATH . 'tickets/home.tpl');
				break;				
			default:
				break;
		}
	}
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

class ChiPhoneActivityPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // activity
		$action = array_shift($path);
		
		switch($action) {
			case null:
				$actions = DevblocksPlatform::getExtensions('cerberusweb.iphone.activity.page');
				$tpl->assign('actions', $actions);
				$tpl->display('file:' . $this->_TPL_PATH . 'activity/home.tpl');
				break;
			default:
				// custom actions
				
				if(null !== $action_mft = DevblocksPlatform::getExtension($action . '.iphone.activity.page', true)) {
					
					$action_mft->render();	
				}
				break;
		}
	}
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

class ChiPhoneResearchPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // activity
		$action = array_shift($path);
		
		switch($action) {
			case 'tasks':
				$tpl->display('file:' . $this->_TPL_PATH . 'activity/tasks.tpl');
				break;
			case null:
				$actions = DevblocksPlatform::getExtensions('cerberusweb.iphone.activity.tab');
				$tpl->assign('actions', $actions);
				$tpl->display('file:' . $this->_TPL_PATH . 'research/home.tpl');
				break;
			default:
				// custom actions
				if(null !== $action_mft = DevblocksPlatform::getExtension('iphone.research.' . $action, true)) {
					$action_mft->showTab();	
				}
			
				break;
		}
	}
	
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

class ChiPhoneConfigPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	public function isVisible() { return true; }
	public function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // activity
		$action = array_shift($path);
		
		switch($action) {
			case 'tasks':
				$tpl->display('file:' . $this->_TPL_PATH . 'activity/tasks.tpl');
				break;
			case null:
				$actions = DevblocksPlatform::getExtensions('cerberusweb.iphone.activity.tab');
				$tpl->assign('actions', $actions);
				$tpl->display('file:' . $this->_TPL_PATH . 'activity/home.tpl');
				break;
			default:
				// custom actions
//				$actions = DevblocksPlatform::getExtensions('cerberusweb.iphone.activity.tab');
				if(null !== $action_mft = DevblocksPlatform::getExtension('iphone.activity.' . $action, true)) {
					$action_mft->showTab();	
				}
			
				break;
		}
	}
	
	
	/**
	 * @return Model_Activity
	 */
	public function getActivity() {
        return new Model_Activity('activity.default');
	}
	
};

abstract class Extension_iPhoneTicketDisplayTab extends DevblocksExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}
	
	function showTab() {}
	function saveTab() {}
};

abstract class Extension_iPhoneOpportunityDisplayTab extends DevblocksExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}
	
	function showTab() {}
	function saveTab() {}
};

class ChConversationiPhoneTicketDisplayTab extends Extension_iPhoneTicketDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	function showTab() {
		
		$response = DevblocksPlatform::getHttpResponse();
				
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // tickets
		array_shift($path); // current ('display')
		$id = array_shift($path); // ticket id
		
		@$active_worker = CerberusApplication::getActiveWorker();
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('expand_all', $expand_all);
		
		$ticket = DAO_Ticket::getTicket($id);

		$tpl->assign('requesters', $ticket->getRequesters());

		// Drafts
		$drafts = DAO_MailQueue::getWhere(sprintf("%s = %d AND %s = %s",
			DAO_MailQueue::TICKET_ID,
			$id,
			DAO_MailQueue::TYPE,
			C4_ORMHelper::qstr(Model_MailQueue::TYPE_TICKET_REPLY)
		));
		
		if(!empty($drafts))
			$tpl->assign('drafts', $drafts);
		
		// Only unqueued drafts
		$pending_drafts = array();
		
		if(!empty($drafts) && is_array($drafts))
		foreach($drafts as $draft_id => $draft) {
			if(!$draft->is_queued)
				$pending_drafts[$draft_id] = $draft;
		}
		
		if(!empty($pending_drafts))
			$tpl->assign('pending_drafts', $pending_drafts);
		
		// Messages
		$messages = $ticket->getMessages();
		
		arsort($messages);
				
		$tpl->assign('latest_message_id',key($messages));
		$tpl->assign('messages', $messages);

		// Thread comments and messages on the same level
		$convo_timeline = array();

		// Track senders and their orgs
		$message_senders = array();
		$message_sender_orgs = array();

		// Loop messages
		foreach($messages as $message_id => $message) { /* @var $message Model_Message */
			$key = $message->created_date . '_m' . $message_id;
			// build a chrono index of messages
			$convo_timeline[$key] = array('m',$message_id);
			
			// If we haven't cached this sender address yet
			if(!isset($message_senders[$message->address_id])) {
				if(null != ($sender_addy = DAO_Address::get($message->address_id))) {
					$message_senders[$sender_addy->id] = $sender_addy;	

					// If we haven't cached this sender org yet
					if(!isset($message_sender_orgs[$sender_addy->contact_org_id])) {
						if(null != ($sender_org = DAO_ContactOrg::get($sender_addy->contact_org_id))) {
							$message_sender_orgs[$sender_org->id] = $sender_org;
						}
					}
				}
			}
		}
		
		$tpl->assign('message_senders', $message_senders);
		$tpl->assign('message_sender_orgs', $message_sender_orgs);
		
		@$mail_inline_comments = DAO_WorkerPref::get($active_worker->id,'mail_inline_comments',1);
		
		if($mail_inline_comments) { // if inline comments are enabled
			$comments = DAO_TicketComment::getByTicketId($id);
			arsort($comments);
			$tpl->assign('comments', $comments);
			
			// build a chrono index of comments
			foreach($comments as $comment_id => $comment) { /* @var $comment Model_TicketComment */
				$key = $comment->created . '_c' . $comment_id;
				$convo_timeline[$key] = array('c',$comment_id);
			}
		}
		
		// Thread drafts into conversation
		if(!empty($drafts)) {
			foreach($drafts as $draft_id => $draft) { /* @var $draft Model_MailQueue */
				$key = $draft->updated . '_d' . $draft_id;
				$convo_timeline[$key] = array('d', $draft_id);
			}
		}
		
		// sort the timeline
		if(!$expand_all) {
			krsort($convo_timeline);
		} else {
			ksort($convo_timeline);
		}
		$tpl->assign('convo_timeline', $convo_timeline);
		
		// Message Notes
		$notes = DAO_MessageNote::getByTicketId($id);
		$message_notes = array();
		// Index notes by message id
		if(is_array($notes))
		foreach($notes as $note) {
			if(!isset($message_notes[$note->message_id]))
				$message_notes[$note->message_id] = array();
			$message_notes[$note->message_id][$note->id] = $note;
		}
		$tpl->assign('message_notes', $message_notes);
		
		// Message toolbar items
		$messageToolbarItems = DevblocksPlatform::getExtensions('cerberusweb.message.toolbaritem', true);
		if(!empty($messageToolbarItems))
			$tpl->assign('message_toolbaritems', $messageToolbarItems);

		// Workers
		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'tickets/display/conversation.tpl');
	}
};

class ChPropertiesiPhoneTicketDisplayTab extends Extension_iPhoneTicketDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
		
		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_Ticket::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(ChCustomFieldSource_Ticket::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('file:' . $this->_TPL_PATH . 'tickets/display/properties.tpl');
	}
};

class ChOtheriPhoneTicketDisplayTab extends Extension_iPhoneTicketDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/tickets/';
	}
	
	function showTab() {
		
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		
		$path = $response->path;
		
		array_shift($path); // iphone
//		array_shift($path); // activity
		array_shift($path); // opportunities
		$action = array_shift($path); // current action
		$id = array_shift($path); // ticket id
		array_shift($path); // other
		$sub_tab = array_shift($path); // sub tab
		
		$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.ticket.other.tab', false);
		$tpl->assign('tab_manifests', $tab_manifests);
		
//		var_dump($tab_manifests);
//		var_dump($sub_tab);
		$tpl->assign('opp_id', $id);
					
		foreach($tab_manifests as $tab_mft)
		{
			if($sub_tab==$tab_mft->params['uri']) {
				$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
			}
		}
		
		$tpl->assign('sub_tab', $tab);
		$tpl->display('file:' . $this->_TPL_PATH . 'display/other.tpl');
	}

};

class ChMailHistoryiPhoneTicketDisplayTab extends Extension_iPhoneTicketDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/tickets/';
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		$translate = DevblocksPlatform::getTranslationService();
		// are we displaying the main home page?
		
		$path = $response->path;
		
		array_shift($path); // iphone
		array_shift($path); // tickets
		$action = array_shift($path); // current action (display)
		$id = array_shift($path); // ticket id
		array_shift($path); // other
		$sub_tab = array_shift($path); // mailhistory
		$page = array_shift($path); // page
		$ticket = DAO_Ticket::getTicket($id);

		$defaults = new C4_AbstractViewModel();
		$defaults->class_name = 'View_Ticket_iPhone';
		$defaults->id = 'iphone_opp_contact_history';
		$defaults->name = $translate->_('addy_book.history.view.title');
		$defaults->view_columns = array(
			SearchFields_Ticket::TICKET_LAST_ACTION_CODE,
			SearchFields_Ticket::TICKET_CREATED_DATE,
			SearchFields_Ticket::TICKET_TEAM_ID,
			SearchFields_Ticket::TICKET_CATEGORY_ID,
		);

		$defaults->renderLimit = 10;
		$defaults->renderSortBy = SearchFields_Ticket::TICKET_CREATED_DATE;
		$defaults->renderSortAsc = false;
		
		
		$view = C4_AbstractViewLoader::getView('iphone_opp_contact_history', $defaults);

		$params[SearchFields_Ticket::REQUESTER_ADDRESS] = new DevblocksSearchCriteria(SearchFields_Ticket::REQUESTER_ADDRESS, '=', $address);
		$searchView->params = $params;
		
		if(isset($page)) {
			$view->renderPage = $page;
		}
		
		C4_AbstractViewLoader::setView($view->id, $view);
		
		$uri = "tickets/display/$id/other/mailhistory";
		$tpl->assign('uri', $uri);
		
		
		$tpl->assign('view', $view);
		$tpl->assign('tickets', $tickets);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'display/sub_tabs/mailhistory.tpl');
	}
};

class ChiPhoneTasksPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('file:' . $this->_TPL_PATH . 'activity/tasks.tpl');
	}
};

if(class_exists('DAO_CrmOpportunity', true)):
	class ChiPhoneOpportunitiesPage extends CerberusPageExtension {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function render() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			// are we displaying the main home page?
				
			$path = $response->path;
			array_shift($path); // iphone
//			array_shift($path); // activity
			array_shift($path); // opportunities
			$action = array_shift($path); // current action
			
			$id = array_shift($path); // opp id
			
			switch($action) {
				case 'display':
					$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.opportunity.display.tab', false);
					
					$tpl->assign('tab_manifests', $tab_manifests);
					$tpl->assign('opp_id', $id);
					$selected_tab = array_shift($path);
					$selected_tab = null != $selected_tab ? $selected_tab : 'notes'; // tab
					
					foreach($tab_manifests as $tab_mft)
					{
						if($selected_tab==$tab_mft->params['uri']) {
							$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
						}
					}
					
					$tpl->assign('tab', $tab);
					$tpl->assign('selected_tab', $selected_tab);
					
					$opp = DAO_CrmOpportunity::get($id);
					$tpl->assign('opp', $opp);
					
					$address = DAO_Address::get($opp->primary_email_id);
					$tpl->assign('address', $address);
					
					$workers = DAO_Worker::getAllActive();
					$tpl->assign('workers', $workers);
					
					$tpl->display('file:' . $this->_TPL_PATH . 'display.tpl');
					break;
				case null:
					$opportunities = DAO_CrmOpportunity::getWhere();
					$tpl->assign('opportunities', $opportunities);
					$tpl->display('file:' . $this->_TPL_PATH . 'home.tpl');
					break;				
				default:
					break;
			}
		}
	};

	class ChNotesiPhoneOpportunityDisplayTab extends Extension_iPhoneOpportunityDisplayTab {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function showTab() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			// are we displaying the main home page?
				
			$path = $response->path;
			array_shift($path); // iphone
//			array_shift($path); // activity
			array_shift($path); // opportunities
			$action = array_shift($path); // current action
			
			$id = array_shift($path); // opp id
	
			list($notes, $null) = DAO_Note::search(
				array(
					new DevblocksSearchCriteria(SearchFields_Note::SOURCE_EXT_ID,'=',CrmNotesSource_Opportunity::ID),
					new DevblocksSearchCriteria(SearchFields_Note::SOURCE_ID,'=',$id),
				),
				25,
				0,
				DAO_Note::CREATED,
				false,
				false
			);
//			var_dump($notes);
			$tpl->assign('notes', $notes);
			$tpl->display('file:' . $this->_TPL_PATH . 'display/notes.tpl');
		}
	};
	
	class ChPropertiesiPhoneOpportunityDisplayTab extends Extension_iPhoneOpportunityDisplayTab {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function showTab() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			// are we displaying the main home page?
			
			$path = $response->path;
			array_shift($path); // iphone
//			array_shift($path); // activity
			array_shift($path); // opportunities
			$action = array_shift($path); // current action
			$id = array_shift($path); // opp id
			
			$custom_fields = DAO_CustomField::getBySource(CrmCustomFieldSource_Opportunity::ID);
			$tpl->assign('custom_fields', $custom_fields);
			
			$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(CrmCustomFieldSource_Opportunity::ID, $id);
			if(isset($custom_field_values[$id]))
				$tpl->assign('custom_field_values', $custom_field_values[$id]);
			
			$tpl->display('file:' . $this->_TPL_PATH . 'display/properties.tpl');
		}
	};
	
	class ChOtheriPhoneOpportunityDisplayTab extends Extension_iPhoneOpportunityDisplayTab {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function showTab() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			// are we displaying the main home page?
			
			$path = $response->path;
			
			array_shift($path); // iphone
			array_shift($path); // opportunities
			$action = array_shift($path); // current action
			$id = array_shift($path); // opp id
			array_shift($path); // other
			$sub_tab = array_shift($path); // sub tab
			
			$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.opportunity.other.tab', false);
			$tpl->assign('tab_manifests', $tab_manifests);
			
			$tpl->assign('opp_id', $id);
						
			foreach($tab_manifests as $tab_mft)
			{
				if($sub_tab==$tab_mft->params['uri']) {
					$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
				}
			}
			
			$tpl->assign('sub_tab', $tab);
			$tpl->display('file:' . $this->_TPL_PATH . 'display/other.tpl');
		}
	};
	
	class ChTasksiPhoneOpportunityDisplayTab extends Extension_iPhoneOpportunityDisplayTab {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function showTab() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			// are we displaying the main home page?
			
			$path = $response->path;
			
			array_shift($path); // iphone
			array_shift($path); // opportunities
			$action = array_shift($path); // current action (display)
			$id = array_shift($path); // opp id
			array_shift($path); // other
			$sub_tab = array_shift($path); // tasks
			
			$tpl->display('file:' . $this->_TPL_PATH . 'display/sub_tabs/tasks.tpl');
		}
	};
	
	class ChMailHistoryiPhoneOpportunityDisplayTab extends Extension_iPhoneOpportunityDisplayTab {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function showTab() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			$translate = DevblocksPlatform::getTranslationService();
			// are we displaying the main home page?
			
			$path = $response->path;
			
			array_shift($path); // iphone
			array_shift($path); // opportunities
			$action = array_shift($path); // current action (display)
			$id = array_shift($path); // opp id
			array_shift($path); // other
			$sub_tab = array_shift($path); // mailhistory
			$page = array_shift($path); // page
			
			$address = $tpl->getVariable('address')->value;
//			$address = DAO_Address::getByEmail($address->email);

			$defaults = new C4_AbstractViewModel();
			$defaults->class_name = 'View_Ticket_iPhone';
			$defaults->id = 'iphone_opp_contact_history';
			$defaults->name = $translate->_('addy_book.history.view.title');
			$defaults->view_columns = array(
				SearchFields_Ticket::TICKET_LAST_ACTION_CODE,
				SearchFields_Ticket::TICKET_CREATED_DATE,
				SearchFields_Ticket::TICKET_TEAM_ID,
				SearchFields_Ticket::TICKET_CATEGORY_ID,
			);

			$defaults->renderLimit = 10;
			$defaults->renderSortBy = SearchFields_Ticket::TICKET_CREATED_DATE;
			$defaults->renderSortAsc = false;
			
			$view = C4_AbstractViewLoader::getView('iphone_opp_contact_history', $defaults);

			$params[SearchFields_Ticket::REQUESTER_ADDRESS] = new DevblocksSearchCriteria(SearchFields_Ticket::REQUESTER_ADDRESS, '=', $address->email);
			$view->params = $params;
			if(isset($page)) {
				$view->renderPage = $page;
			}
			
			C4_AbstractViewLoader::setView($view->id, $view);
			
			$uri = "opportunities/display/$id/other/mailhistory";
			$tpl->assign('uri', $uri);
			$tpl->assign('view', $view);
			$tpl->assign('tickets', $tickets);
			
			$tpl->display('file:' . $this->_TPL_PATH . 'display/sub_tabs/mailhistory.tpl');
		}
	};
	
endif;

if(class_exists('DAO_FeedbackEntry', true)):
	class ChiPhoneFeedbackPage extends CerberusPageExtension {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
		}
		
		function render() {
			$tpl = DevblocksPlatform::getTemplateService();
			$feedback = DAO_FeedbackEntry::getWhere();
//			var_dump($feedback);
			$tpl->assign('feedbackentries', $feedback);
			$tpl->display('file:' . $this->_TPL_PATH . 'activity/feedback.tpl');
		}
	};
endif;

if(class_exists('DAO_TimeTrackingActivity')):
	class ChiPhoneTimeTrackingPage extends CerberusPageExtension {
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
		}
		
		function render() {
			$tpl = DevblocksPlatform::getTemplateService();
			
			$tpl->display('file:' . $this->_TPL_PATH . 'activity/timetracking.tpl');
		}
	};
endif;



class View_Ticket_iPhone extends C4_AbstractView {
	const DEFAULT_ID = 'tickets_workspace';

	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = 'Tickets';
		$this->renderLimit = 10;
		$this->renderSortBy = SearchFields_Ticket::TICKET_UPDATED_DATE;
		$this->renderSortAsc = false;

		$this->view_columns = array(
			SearchFields_Ticket::TICKET_LAST_ACTION_CODE,
			SearchFields_Ticket::TICKET_UPDATED_DATE,
			SearchFields_Ticket::TICKET_TEAM_ID,
			SearchFields_Ticket::TICKET_CATEGORY_ID,
			SearchFields_Ticket::TICKET_SPAM_SCORE,
		);
	}

	function getData() {
		$objects = DAO_Ticket::search(
			$this->view_columns,
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		return $objects;
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		
		$view_path = dirname(dirname(__FILE__)) . '/templates/tickets/';
		$tpl->assign('view_path',$view_path);
		$tpl->assign('view', $this);

		$visit = CerberusApplication::getVisit();

		$results = self::getData();
		$tpl->assign('results', $results);
		
		@$ids = array_keys($results[0]);
		
		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);

		$teams = DAO_Group::getAll();
		$tpl->assign('teams', $teams);

		$buckets = DAO_Bucket::getAll();
		$tpl->assign('buckets', $buckets);

		$team_categories = DAO_Bucket::getTeams();
		$tpl->assign('team_categories', $team_categories);

		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_Ticket::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		// Undo?
		$last_action = View_Ticket::getLastAction($this->id);
		$tpl->assign('last_action', $last_action);
		if(!empty($last_action) && !is_null($last_action->ticket_ids)) {
			$tpl->assign('last_action_count', count($last_action->ticket_ids));
		}

		$tpl->assign('timestamp_now', time());
		
		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . $view_path . 'view.tpl');
	}

	function doResetCriteria() {
		$active_worker = CerberusApplication::getActiveWorker(); /* @var $active_worker Model_Worker */
		$active_worker_memberships = $active_worker->getMemberships();
		
		$this->params = array(
			SearchFields_Ticket::TICKET_CLOSED => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_CLOSED,'=',0),
			SearchFields_Ticket::TICKET_TEAM_ID => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_TEAM_ID,'in',array_keys($active_worker_memberships)), // censor
		);
		$this->renderPage = 0;
	}
	
	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/cerberusweb.core/templates/';

		switch($field) {
			case SearchFields_Ticket::TICKET_ID:
			case SearchFields_Ticket::TICKET_MASK:
			case SearchFields_Ticket::TICKET_SUBJECT:
			case SearchFields_Ticket::TICKET_FIRST_WROTE:
			case SearchFields_Ticket::TICKET_LAST_WROTE:
			case SearchFields_Ticket::REQUESTER_ADDRESS:
			case SearchFields_Ticket::TICKET_INTERESTING_WORDS:
			case SearchFields_Ticket::ORG_NAME:
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__string.tpl');
				break;

			case SearchFields_Ticket::TICKET_FIRST_WROTE_SPAM:
			case SearchFields_Ticket::TICKET_FIRST_WROTE_NONSPAM:
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__number.tpl');
				break;
					
			case SearchFields_Ticket::TICKET_WAITING:
			case SearchFields_Ticket::TICKET_DELETED:
			case SearchFields_Ticket::TICKET_CLOSED:
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__bool.tpl');
				break;
					
			case SearchFields_Ticket::TICKET_CREATED_DATE:
			case SearchFields_Ticket::TICKET_UPDATED_DATE:
			case SearchFields_Ticket::TICKET_DUE_DATE:
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__date.tpl');
				break;
					
			case SearchFields_Ticket::TICKET_SPAM_TRAINING:
				$tpl->display('file:' . $tpl_path . 'tickets/search/criteria/ticket_spam_training.tpl');
				break;
				
			case SearchFields_Ticket::TICKET_SPAM_SCORE:
				$tpl->display('file:' . $tpl_path . 'tickets/search/criteria/ticket_spam_score.tpl');
				break;

			case SearchFields_Ticket::TICKET_LAST_ACTION_CODE:
				$tpl->display('file:' . $tpl_path . 'tickets/search/criteria/ticket_last_action.tpl');
				break;

			case SearchFields_Ticket::TICKET_NEXT_WORKER_ID:
			case SearchFields_Ticket::TICKET_LAST_WORKER_ID:
				$workers = DAO_Worker::getAll();
				$tpl->assign('workers', $workers);
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__worker.tpl');
				break;
					
			case SearchFields_Ticket::TICKET_TEAM_ID:
				$teams = DAO_Group::getAll();
				$tpl->assign('teams', $teams);

				$team_categories = DAO_Bucket::getTeams();
				$tpl->assign('team_categories', $team_categories);

				$tpl->display('file:' . $tpl_path . 'tickets/search/criteria/ticket_team.tpl');
				break;

			case SearchFields_Ticket::FULLTEXT_MESSAGE_CONTENT:
				$tpl->display('file:' . $tpl_path . 'internal/views/criteria/__fulltext.tpl');
				break;
				
			default:
				// Custom Fields
				if('cf_' == substr($field,0,3)) {
					$this->_renderCriteriaCustomField($tpl, substr($field,3));
				} else {
					echo ' ';
				}
				break;
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			case SearchFields_Ticket::TICKET_LAST_WORKER_ID:
			case SearchFields_Ticket::TICKET_NEXT_WORKER_ID:
				$workers = DAO_Worker::getAll();
				$strings = array();

				foreach($values as $val) {
					if(empty($val))
					$strings[] = "Nobody";
					elseif(!isset($workers[$val]))
					continue;
					else
					$strings[] = $workers[$val]->getName();
				}
				echo implode(", ", $strings);
				break;

			case SearchFields_Ticket::TICKET_TEAM_ID:
				$teams = DAO_Group::getAll();
				$strings = array();

				foreach($values as $val) {
					if(!isset($teams[$val]))
					continue;

					$strings[] = $teams[$val]->name;
				}
				echo implode(", ", $strings);
				break;
					
			case SearchFields_Ticket::TICKET_CATEGORY_ID:
				$buckets = DAO_Bucket::getAll();
				$strings = array();

				foreach($values as $val) {
					if(0==$val) {
						$strings[] = "Inbox";
					} elseif(!isset($buckets[$val])) {
						continue;
					} else {
						$strings[] = $buckets[$val]->name;
					}
				}
				echo implode(", ", $strings);
				break;

			case SearchFields_Ticket::TICKET_LAST_ACTION_CODE:
				$strings = array();

				foreach($values as $val) {
					switch($val) {
						case 'O':
							$strings[] = "New Ticket";
							break;
						case 'R':
							$strings[] = "Customer Reply";
							break;
						case 'W':
							$strings[] = "Worker Reply";
							break;
					}
				}
				echo implode(", ", $strings);
				break;

			case SearchFields_Ticket::TICKET_SPAM_TRAINING:
				$strings = array();

				foreach($values as $val) {
					switch($val) {
						case 'S':
							$strings[] = "Spam";
							break;
						case 'N':
							$strings[] = "Not Spam";
							break;
						default:
							$strings[] = "Not Trained";
							break;
					}
				}
				echo implode(", ", $strings);
				break;

			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_Ticket::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_Ticket::TICKET_CATEGORY_ID]);
		unset($fields[SearchFields_Ticket::TICKET_UNLOCK_DATE]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		unset($fields[SearchFields_Ticket::REQUESTER_ID]);
		unset($fields[SearchFields_Ticket::REQUESTER_ADDRESS]);
		unset($fields[SearchFields_Ticket::TICKET_UNLOCK_DATE]);
		unset($fields[SearchFields_Ticket::TICKET_INTERESTING_WORDS]);
		return $fields;
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_Ticket::TICKET_ID:
			case SearchFields_Ticket::TICKET_MASK:
			case SearchFields_Ticket::TICKET_SUBJECT:
			case SearchFields_Ticket::TICKET_FIRST_WROTE:
			case SearchFields_Ticket::TICKET_LAST_WROTE:
			case SearchFields_Ticket::REQUESTER_ADDRESS:
			case SearchFields_Ticket::TICKET_INTERESTING_WORDS:
			case SearchFields_Ticket::ORG_NAME:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;

			case SearchFields_Ticket::TICKET_WAITING:
			case SearchFields_Ticket::TICKET_DELETED:
			case SearchFields_Ticket::TICKET_CLOSED:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			case SearchFields_Ticket::TICKET_FIRST_WROTE_SPAM:
			case SearchFields_Ticket::TICKET_FIRST_WROTE_NONSPAM:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;

			case SearchFields_Ticket::TICKET_CREATED_DATE:
			case SearchFields_Ticket::TICKET_UPDATED_DATE:
			case SearchFields_Ticket::TICKET_DUE_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from) || (!is_numeric($from) && @false === strtotime(str_replace('.','-',$from))))
					$from = 0;
					
				if(empty($to) || (!is_numeric($to) && @false === strtotime(str_replace('.','-',$to))))
					$to = 'now';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;

			case SearchFields_Ticket::TICKET_SPAM_SCORE:
				@$score = DevblocksPlatform::importGPC($_REQUEST['score'],'integer',null);
				if(!is_null($score) && is_numeric($score)) {
					$criteria = new DevblocksSearchCriteria($field,$oper,intval($score)/100);
				}
				break;

			case SearchFields_Ticket::TICKET_SPAM_TRAINING:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;

			case SearchFields_Ticket::TICKET_LAST_ACTION_CODE:
				@$last_action_code = DevblocksPlatform::importGPC($_REQUEST['last_action'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$last_action_code);
				break;

			case SearchFields_Ticket::TICKET_LAST_WORKER_ID:
			case SearchFields_Ticket::TICKET_NEXT_WORKER_ID:
				@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$worker_id);
				break;
				

			case SearchFields_Ticket::TICKET_TEAM_ID:
				@$team_ids = DevblocksPlatform::importGPC($_REQUEST['team_id'],'array');
				@$bucket_ids = DevblocksPlatform::importGPC($_REQUEST['bucket_id'],'array');

				if(!empty($team_ids))
				$this->params[SearchFields_Ticket::TICKET_TEAM_ID] = new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_TEAM_ID,$oper,$team_ids);
				if(!empty($bucket_ids))
				$this->params[SearchFields_Ticket::TICKET_CATEGORY_ID] = new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_CATEGORY_ID,$oper,$bucket_ids);

				break;
				
			case SearchFields_Ticket::FULLTEXT_MESSAGE_CONTENT:
				@$scope = DevblocksPlatform::importGPC($_REQUEST['scope'],'string','expert');
				$criteria = new DevblocksSearchCriteria($field,DevblocksSearchCriteria::OPER_FULLTEXT,array($value,$scope));
				break;
				
			default:
				// Custom Fields
				if(substr($field,0,3)=='cf_') {
					$criteria = $this->_doSetCriteriaCustomField($field, substr($field,3));
				}
				break;
		}

		if(!empty($criteria)) {
			$this->params[$field] = $criteria;
			$this->renderPage = 0;
		}
	}

	/**
	 * @param array
	 * @param array
	 * @return boolean
	 * [TODO] Find a better home for this?
	 */
	function doBulkUpdate($filter, $filter_param, $data, $do, $ticket_ids=array()) {
		@set_time_limit(600);
	  
		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ticket_ids))
			return;
		
		$rule = new Model_GroupInboxFilter();
		$rule->actions = $do;
	  
		$params = $this->params;

		if(empty($filter)) {
			$data[] = '*'; // All, just to permit a loop in foreach($data ...)
		}

		switch($filter) {
			default:
			case 'subject':
			case 'sender':
			case 'header':
				if(is_array($data))
				foreach($data as $v) {
					$new_params = array();
					$do_header = null;
		    
					switch($filter) {
						case 'subject':
							$new_params = array(
								new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_SUBJECT,DevblocksSearchCriteria::OPER_LIKE,$v)
							);
							$do_header = 'subject';
							$ticket_ids = array();
							break;
						case 'sender':
							$new_params = array(
								new DevblocksSearchCriteria(SearchFields_Ticket::SENDER_ADDRESS,DevblocksSearchCriteria::OPER_LIKE,$v)
							);
							$do_header = 'from';
							$ticket_ids = array();
							break;
						case 'header':
							$new_params = array(
								// [TODO] It will eventually come up that we need multiple header matches (which need to be pair grouped as OR)
								new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_MESSAGE_HEADER,DevblocksSearchCriteria::OPER_EQ,$filter_param),
								new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_MESSAGE_HEADER_VALUE,DevblocksSearchCriteria::OPER_EQ,$v)
							);
							$ticket_ids = array();
							break;
					}

					$new_params = array_merge($new_params, $params);
					$pg = 0;

					if(empty($ticket_ids)) {
						do {
							list($tickets,$null) = DAO_Ticket::search(
								array(),
								$new_params,
								100,
								$pg++,
								SearchFields_Ticket::TICKET_ID,
								true,
								false
							);
							 
							$ticket_ids = array_merge($ticket_ids, array_keys($tickets));
							 
						} while(!empty($tickets));
					}
			   
					$batch_total = count($ticket_ids);
					for($x=0;$x<=$batch_total;$x+=200) {
						$batch_ids = array_slice($ticket_ids,$x,200);
						$rule->run($batch_ids);
						unset($batch_ids);
					}
				}

				break;
		}

		unset($ticket_ids);
	}

	static function createSearchView() {
		$active_worker = CerberusApplication::getActiveWorker();
		$memberships = $active_worker->getMemberships();
		$translate = DevblocksPlatform::getTranslationService();
		
		$view = new View_Ticket();
		$view->id = CerberusApplication::VIEW_SEARCH;
		$view->name = $translate->_('common.search_results');
		$view->view_columns = array(
			SearchFields_Ticket::TICKET_LAST_ACTION_CODE,
			SearchFields_Ticket::TICKET_UPDATED_DATE,
			SearchFields_Ticket::TICKET_TEAM_ID,
			SearchFields_Ticket::TICKET_CATEGORY_ID,
			SearchFields_Ticket::TICKET_SPAM_SCORE,
		);
		$view->params = array(
			SearchFields_Ticket::TICKET_CLOSED => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_CLOSED,DevblocksSearchCriteria::OPER_EQ,0),
			SearchFields_Ticket::TICKET_TEAM_ID => new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_TEAM_ID,'in',array_keys($memberships)), // censor
		);
		$view->renderLimit = 100;
		$view->renderPage = 0;
		$view->renderSortBy = null; // SearchFields_Ticket::TICKET_UPDATED_DATE
		$view->renderSortAsc = 0;

		return $view;
	}

	static public function setLastAction($view_id, Model_TicketViewLastAction $last_action=null) {
		$visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
		$view_last_actions = $visit->get(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
	  
		if(!is_null($last_action) && !empty($last_action->ticket_ids)) {
			$view_last_actions[$view_id] = $last_action;
		} else {
			if(isset($view_last_actions[$view_id])) {
				unset($view_last_actions[$view_id]);
			}
		}
	  
		$visit->set(CerberusVisit::KEY_VIEW_LAST_ACTION,$view_last_actions);
	}

	/**
	 * @param string $view_id
	 * @return Model_TicketViewLastAction
	 */
	static public function getLastAction($view_id) {
		$visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
		$view_last_actions = $visit->get(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
		return (isset($view_last_actions[$view_id]) ? $view_last_actions[$view_id] : null);
	}

	static public function clearLastActions() {
		$visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
		$visit->set(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
	}
};

?>