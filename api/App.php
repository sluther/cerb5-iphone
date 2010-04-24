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
		DevblocksPlatform::redirect(DevblocksHttpResponse(array('iphone', 'login')));
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
		array_shift($path); // tickets
		$action = array_shift($path); // current action
		$id = array_shift($path); // ticket id
		
		switch($action) {
			case 'overview':
				$tpl->display('file:' . $this->_TPL_PATH . 'tickets/overview.tpl');
				break;
			case 'display':
				$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.ticket.display.tab', false);
				$tpl->assign('tab_manifests', $tab_manifests);
				$tpl->assign('ticket_id', $id);
				$selected_tab = array_shift($path);
				$selected_tab = null != $selected_tab ? $selected_tab : 'conversation'; // tab
				
				foreach($tab_manifests as $tab_mft)
				{
					if($selected_tab==$tab_mft->params['uri']) {
						$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
					}
				}
				
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
	
	public function overviewAction() {
		
	}
	
	public function displayAction() {
		
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

abstract class Extension_iPhoneActivityPage extends DevblocksExtension {
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
		$tpl->assign('ticket', $ticket);
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
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->display('file:' . $this->_TPL_PATH . 'tickets/display/other.tpl');
	}
};

class ChTasksiPhoneActivityPage extends Extension_iPhoneActivityPage {
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

?>