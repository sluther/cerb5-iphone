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
				
				$workers = DAO_Worker::getAllActive();
				$tpl->assign('workers', $workers);
				
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
	
	public function savePropertiesAction()
	{
		@$ticket_id = DevblocksPlatform::importGPC($_POST['ticket_id'],'integer',0);
		@$remove = DevblocksPlatform::importGPC($_POST['remove'],'array',array());
		@$next_worker_id = DevblocksPlatform::importGPC($_POST['next_worker_id'],'integer',0);
		@$ticket_reopen = DevblocksPlatform::importGPC($_POST['ticket_reopen'],'string','');
		@$unlock_date = DevblocksPlatform::importGPC($_POST['unlock_date'],'string','');
		@$subject = DevblocksPlatform::importGPC($_POST['subject'],'string','');
		@$closed = DevblocksPlatform::importGPC($_POST['closed'],'closed',0);
		
		@$ticket = DAO_Ticket::getTicket($ticket_id);
		
		if(empty($ticket_id) || empty($ticket))
			return;
		
		$fields = array();
		
		// Properties

		if(empty($next_worker_id))
			$unlock_date = "";
		
		// Status
		if(isset($closed)) {
			switch($closed) {
				case 0: // open
					if(array(0,0,0)!=array($ticket->is_waiting,$ticket->is_closed,$ticket->is_deleted)) {
						$fields[DAO_Ticket::IS_WAITING] = 0;
						$fields[DAO_Ticket::IS_CLOSED] = 0;
						$fields[DAO_Ticket::IS_DELETED] = 0;
						$fields[DAO_Ticket::DUE_DATE] = 0;
					}
					break;
				case 1: // closed
					if(array(0,1,0)!=array($ticket->is_waiting,$ticket->is_closed,$ticket->is_deleted)) {
						$fields[DAO_Ticket::IS_WAITING] = 0;
						$fields[DAO_Ticket::IS_CLOSED] = 1;
						$fields[DAO_Ticket::IS_DELETED] = 0;
					}
					
					if(isset($ticket_reopen)) {
						@$time = intval(strtotime($ticket_reopen));
						$fields[DAO_Ticket::DUE_DATE] = $time;
					}
					break;
				case 2: // waiting
					if(array(1,0,0)!=array($ticket->is_waiting,$ticket->is_closed,$ticket->is_deleted)) {
						$fields[DAO_Ticket::IS_WAITING] = 1;
						$fields[DAO_Ticket::IS_CLOSED] = 0;
						$fields[DAO_Ticket::IS_DELETED] = 0;
					}
					
					if(isset($ticket_reopen)) {
						@$time = intval(strtotime($ticket_reopen));
						$fields[DAO_Ticket::DUE_DATE] = $time;
					}
					break;
				case 3: // deleted
					if(array(0,1,1)!=array($ticket->is_waiting,$ticket->is_closed,$ticket->is_deleted)) {
						$fields[DAO_Ticket::IS_WAITING] = 0;
						$fields[DAO_Ticket::IS_CLOSED] = 1;
						$fields[DAO_Ticket::IS_DELETED] = 1;
					}
					$fields[DAO_Ticket::DUE_DATE] = 0;
					break;
			}
		}
			
		if(isset($next_worker_id))
			$fields[DAO_Ticket::NEXT_WORKER_ID] = $next_worker_id;
			
		if(isset($unlock_date)) {
			@$time = intval(strtotime($unlock_date));
			$fields[DAO_Ticket::UNLOCK_DATE] = $time;
		}

		if(!empty($subject))
			$fields[DAO_Ticket::SUBJECT] = $subject;

		if(!empty($fields)) {
			DAO_Ticket::updateTicket($ticket_id, $fields);
		}

		// Custom field saves
		@$field_ids = DevblocksPlatform::importGPC($_POST['field_ids'], 'array', array());
		DAO_CustomFieldValue::handleFormPost(ChCustomFieldSource_Ticket::ID, $ticket_id, $field_ids);
		
		// Requesters
		@$req_list = DevblocksPlatform::importGPC($_POST['add'],'string','');
		if(!empty($req_list)) {
			$req_list = DevblocksPlatform::parseCrlfString($req_list);
			$req_list = array_unique($req_list);
			
			// [TODO] This is redundant with the Requester Peek on Reply
			if(is_array($req_list) && !empty($req_list)) {
				foreach($req_list as $req) {
					if(empty($req))
						continue;
						
					$rfc_addys = imap_rfc822_parse_adrlist($req, 'localhost');
					
					foreach($rfc_addys as $rfc_addy) {
						$addy = $rfc_addy->mailbox . '@' . $rfc_addy->host;
						DAO_Ticket::createRequester($addy, $ticket_id);
					}
				}
			}
		}
		
		if(!empty($remove) && is_array($remove)) {
			foreach($remove as $address_id) {
				$addy = DAO_Address::get($address_id);
				DAO_Ticket::deleteRequester($ticket_id, $address_id);
			}
		}
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('iphone', 'tickets', 'display', $ticket->id, 'properties')));
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

class ChiPhoneTasksPage extends CerberusPageExtension {
	private $_TPL_PATH = '';
	
	const VIEW_TASKS = 'View_Task_iPhone';
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	function render() {
		$translate = DevblocksPlatform::getTranslationService();
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		
		$path = $response->path;
		array_shift($path); // iphone
		array_shift($path); // tickets
		$action = array_shift($path); // current action
		switch($action) {
			case 'display':
				$id = array_shift($path);
				
				$task = DAO_Task::get($id);
				
				$tab_manifests = DevblocksPlatform::getExtensions('cerberusweb.iphone.task.display.tab', false);
				$tpl->assign('tab_manifests', $tab_manifests);
				
				$workers = DAO_Worker::getAllActive();
				$tpl->assign('workers', $workers);
				
				$selected_tab = array_shift($path);
				$selected_tab = null != $selected_tab ? $selected_tab : 'notes'; // tab
				
				foreach($tab_manifests as $tab_mft)
				{
					if($selected_tab==$tab_mft->params['uri']) {
						$tab = DevblocksPlatform::getExtension($tab_mft->id, true);
					}
				}
				
				$tpl->assign('task', $task);
				$tpl->assign('tab', $tab);
				$tpl->assign('selected_tab', $selected_tab);				
				$tpl->display('file:' . $this->_TPL_PATH . 'tasks/display.tpl');
				break;
			default:
				$defaults = new C4_AbstractViewModel();
				$defaults->class_name = 'View_Task_iPhone';
				$defaults->id = self::VIEW_TASKS;
				$defaults->name = $translate->_('crm.tab.title');
				$defaults->renderSortBy = SearchFields_Task::DUE_DATE;
				$defaults->renderSortAsc = 1;
				$defaults->renderLimit = 10;
		
				$view = C4_AbstractViewLoader::getView(self::VIEW_TASKS, $defaults);
				
				if(isset($page)) {
					$view->renderPage = $page;
				}
						
				C4_AbstractViewLoader::setView($view->id, $view);	
				
		//		$tpl->assign('response_uri', 'activity/tasks');
				
				$tpl->assign('view', $view);
				
				$active_worker = CerberusApplication::getActiveWorker();
				
				$tpl->display('file:' . $this->_TPL_PATH . 'tasks/home.tpl');
				break;
		}
	}
	
	public function savePropertiesAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer','');
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);

		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!empty($id) && !empty($do_delete)) { // delete
			$task = DAO_Task::get($id);

			// Check privs
			if(($active_worker->hasPriv('core.tasks.actions.create') && $active_worker->id==$task->worker_id)
				|| ($active_worker->hasPriv('core.tasks.actions.update_nobody') && empty($task->worker_id)) 
				|| $active_worker->hasPriv('core.tasks.actions.update_all')) {
					DAO_Task::delete($id);
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('activity','tasks')));
					exit;
				}
			
		} else { // update
			$fields = array();
	
			// Title
			@$title = DevblocksPlatform::importGPC($_REQUEST['title'],'string','');
			$fields[DAO_Task::TITLE] = !empty($title) ? $title : 'New Task';
	
			// Completed
			@$completed = DevblocksPlatform::importGPC($_REQUEST['completed'],'integer',0);
			
			$fields[DAO_Task::IS_COMPLETED] = intval($completed);
			
			// [TODO] This shouldn't constantly update the completed date (it should compare)
			if($completed)
				$fields[DAO_Task::COMPLETED_DATE] = time();
			else
				$fields[DAO_Task::COMPLETED_DATE] = 0;
			
			// Updated Date
			$fields[DAO_Task::UPDATED_DATE] = time();
			
			// Due Date
			@$due_date = DevblocksPlatform::importGPC($_REQUEST['due_date'],'string','');
			@$fields[DAO_Task::DUE_DATE] = empty($due_date) ? 0 : intval(strtotime($due_date));		
	
			// Worker
			@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'integer',0);
			@$fields[DAO_Task::WORKER_ID] = intval($worker_id);
			
			// Save
			if(!empty($id)) {
				DAO_Task::update($id, $fields);
			
				// Custom field saves
				@$field_ids = DevblocksPlatform::importGPC($_POST['field_ids'], 'array', array());
				DAO_CustomFieldValue::handleFormPost(ChCustomFieldSource_Task::ID, $id, $field_ids);
			}
		}
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('iphone', 'tasks', 'display', $id, 'properties')));
	}
};


abstract class Extension_iPhoneDisplayTab extends DevblocksExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}
	
	function showTab() {}
	function saveTab() {}
};


class ChConversationiPhoneTicketDisplayTab extends Extension_iPhoneDisplayTab {
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

class ChPropertiesiPhoneTicketDisplayTab extends Extension_iPhoneDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		parent::__construct($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_Ticket::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(ChCustomFieldSource_Ticket::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
		$tpl->display('file:' . $this->_TPL_PATH . 'tickets/display/properties.tpl');
	}
};

class ChOtheriPhoneTicketDisplayTab extends Extension_iPhoneDisplayTab {
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

class ChMailHistoryiPhoneTicketDisplayTab extends Extension_iPhoneDisplayTab {
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

class ChNotesiPhoneTaskDisplayTab extends Extension_iPhoneDisplayTab {
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
		// array_shift($path); // activity
		array_shift($path); // tasks
		$action = array_shift($path); // current action
		
		$id = array_shift($path); // opp id

		list($notes, $null) = DAO_Note::search(
			array(
				new DevblocksSearchCriteria(SearchFields_Note::SOURCE_EXT_ID,'=',ChNotesSource_Task::ID),
				new DevblocksSearchCriteria(SearchFields_Note::SOURCE_ID,'=',$id),
			),
			25,
			0,
			DAO_Note::CREATED,
			false,
			false
		);
		// var_dump($notes);
		$tpl->assign('notes', $notes);
		$tpl->display('file:' . $this->_TPL_PATH . 'display/notes.tpl');
	}
};

class ChPropertiesiPhoneTaskDisplayTab extends Extension_iPhoneDisplayTab {
	private $_TPL_PATH = '';
	
	public function __construct($manifest) {
		$this->DevblocksExtension($manifest);
		$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/tasks/';
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$response = DevblocksPlatform::getHttpResponse();
		// are we displaying the main home page?
		
		$path = $response->path;
		array_shift($path); // iphone
		// array_shift($path); // activity
		array_shift($path); // opportunities
		$action = array_shift($path); // current action
		$id = array_shift($path); // opp id
		
		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_Task::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(ChCustomFieldSource_Task::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'display/properties.tpl');
	}
};

if(class_exists('DAO_CrmOpportunity', true)):
	class ChiPhoneOpportunitiesPage extends CerberusPageExtension {
		const VIEW_OPPS = 'opps';
		
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/opportunities/';
		}
		
		function render() {
			$tpl = DevblocksPlatform::getTemplateService();
			$response = DevblocksPlatform::getHttpResponse();
			$translate = DevblocksPlatform::getTranslationService();
			
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
					
					$defaults = new C4_AbstractViewModel();
					$defaults->class_name = 'View_CrmOpportunity_iPhone';
					$defaults->id = self::VIEW_OPPS;
					$defaults->name = $translate->_('crm.tab.title');
					$defaults->renderSortBy = SearchFields_CrmOpportunity::UPDATED_DATE;
					$defaults->renderSortAsc = 0;
					
					$view = C4_AbstractViewLoader::getView(self::VIEW_OPPS, $defaults);
					
					$tpl->assign('view', $view);
					$tpl->display('file:' . $this->_TPL_PATH . 'home.tpl');
					break;				
				default:
					break;
			}
		}
		
		public function savePropertiesAction()
		{
			@$opp_id = DevblocksPlatform::importGPC($_REQUEST['opp_id'],'integer', 0);
			@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
			@$name = DevblocksPlatform::importGPC($_REQUEST['name'],'string','');
			@$status = DevblocksPlatform::importGPC($_REQUEST['status'],'integer',0);
			@$amount = DevblocksPlatform::importGPC($_REQUEST['amount'],'string','0');
			@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'integer',0);
			@$created_date_str = DevblocksPlatform::importGPC($_REQUEST['created_date'],'string','');
			@$closed_date_str = DevblocksPlatform::importGPC($_REQUEST['closed_date'],'string','');
			
			
			
			// State
			$is_closed = (0==$status) ? 0 : 1;
			$is_won = (1==$status) ? 1 : 0;
			
			// Explode the $amount on ., to separate into dollars and cents
			$amount = explode('.', $amount);
			// Strip commas and decimals and put together the "dollars+cents"
			$amount = intval(str_replace(array(',','.'),'',$amount[0])).'.'.number_format($amount[1],0,'','');
	
			// Dates
			if(false === ($created_date = strtotime($created_date_str)))
				$created_date = time();
				
			if(false === ($closed_date = strtotime($closed_date_str)))
				$closed_date = ($is_closed) ? time() : 0;
	
			if(!$is_closed)
				$closed_date = 0;
			
			if(!empty($opp_id)) {
				$fields = array(
					DAO_CrmOpportunity::NAME => $name,
					DAO_CrmOpportunity::AMOUNT => $amount,
					DAO_CrmOpportunity::CREATED_DATE => $created_date,
					DAO_CrmOpportunity::UPDATED_DATE => time(),
					DAO_CrmOpportunity::CLOSED_DATE => $closed_date,
					DAO_CrmOpportunity::IS_CLOSED => $is_closed,
					DAO_CrmOpportunity::IS_WON => $is_won,
					DAO_CrmOpportunity::WORKER_ID => $worker_id,
				);
				
				// Email
				if(null != ($address = DAO_Address::lookupAddress($email, true)))
					$fields[DAO_CrmOpportunity::PRIMARY_EMAIL_ID] = $address->id;
				
				DAO_CrmOpportunity::update($opp_id, $fields);
				
				@$field_ids = DevblocksPlatform::importGPC($_REQUEST['field_ids'], 'array', array());
				DAO_CustomFieldValue::handleFormPost(CrmCustomFieldSource_Opportunity::ID, $opp_id, $field_ids);
			}
			
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('iphone','opportunities', 'display', $opp_id, 'properties')));
		}
	};

	class ChNotesiPhoneOpportunityDisplayTab extends Extension_iPhoneDisplayTab {
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
	
	class ChPropertiesiPhoneOpportunityDisplayTab extends Extension_iPhoneDisplayTab {
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
	
	class ChOtheriPhoneOpportunityDisplayTab extends Extension_iPhoneDisplayTab {
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
	
	class ChTasksiPhoneOpportunityDisplayTab extends Extension_iPhoneDisplayTab {
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
	
	class ChMailHistoryiPhoneOpportunityDisplayTab extends Extension_iPhoneDisplayTab {
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
	
	class View_CrmOpportunity_iPhone extends C4_AbstractView {
		const DEFAULT_ID = 'crm_opportunities';
	
		function __construct() {
			$this->id = self::DEFAULT_ID;
			$this->name = 'Opportunities';
			$this->renderLimit = 25;
			$this->renderSortBy = SearchFields_CrmOpportunity::UPDATED_DATE;
			$this->renderSortAsc = true;
	
			$this->view_columns = array(
				SearchFields_CrmOpportunity::EMAIL_ADDRESS,
				SearchFields_CrmOpportunity::ORG_NAME,
				SearchFields_CrmOpportunity::AMOUNT,
				SearchFields_CrmOpportunity::UPDATED_DATE,
				SearchFields_CrmOpportunity::WORKER_ID,
			);
			
			$this->params = array(
				SearchFields_CrmOpportunity::IS_CLOSED => new DevblocksSearchCriteria(SearchFields_CrmOpportunity::IS_CLOSED,'=',0),
			);
		}
	
		function getData() {
			$objects = DAO_CrmOpportunity::search(
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
			
			$view_path = dirname(dirname(__FILE__)) . '/templates/opportunities/';
			
			$tpl->assign('id', $this->id);
			$tpl->assign('view', $this);
			
			$results = self::getData();
			$tpl->assign('results', $results);
			
			@$ids = array_keys($results[0]);
			
			$workers = DAO_Worker::getAll();
			$tpl->assign('workers', $workers);
			
			// Custom fields
			$custom_fields = DAO_CustomField::getBySource(CrmCustomFieldSource_Opportunity::ID);
			$tpl->assign('custom_fields', $custom_fields);
			
			if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')):
				 $maxcols = 1;
			elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')):
				$maxcols = 4;
			else:
				$maxcols = 4;
			endif;
			
			$tpl->assign('maxcols', $maxcols);
			
			$tpl->assign('view_fields', $this->getColumns());
			$tpl->display('file:' . $view_path . 'view.tpl');
		}
	
		function renderCriteria($field) {
			$tpl = DevblocksPlatform::getTemplateService();
			$tpl_path = dirname(dirname(__FILE__)).'/templates/';
			$tpl->assign('id', $this->id);
	
			switch($field) {
				case SearchFields_CrmOpportunity::NAME:
				case SearchFields_CrmOpportunity::ORG_NAME:
				case SearchFields_CrmOpportunity::EMAIL_ADDRESS:
					$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__string.tpl');
					break;
					
				case SearchFields_CrmOpportunity::AMOUNT:
					$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__number.tpl');
					break;
					
				case SearchFields_CrmOpportunity::IS_CLOSED:
				case SearchFields_CrmOpportunity::IS_WON:
					$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__bool.tpl');
					break;
					
				case SearchFields_CrmOpportunity::CREATED_DATE:
				case SearchFields_CrmOpportunity::UPDATED_DATE:
				case SearchFields_CrmOpportunity::CLOSED_DATE:
					$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__date.tpl');
					break;
					
				case SearchFields_CrmOpportunity::WORKER_ID:
					$workers = DAO_Worker::getAll();
					$tpl->assign('workers', $workers);
					
					$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__worker.tpl');
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
				case SearchFields_CrmOpportunity::WORKER_ID:
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
				
				default:
					parent::renderCriteriaParam($param);
					break;
			}
		}
	
		// [TODO] change globally to getColumnFields() in AbstractView
		static function getFields() {
			$fields = SearchFields_CrmOpportunity::getFields();
			return $fields;
		}
	
		static function getSearchFields() {
			$fields = self::getFields();
			unset($fields[SearchFields_CrmOpportunity::ID]);
			unset($fields[SearchFields_CrmOpportunity::PRIMARY_EMAIL_ID]);
			unset($fields[SearchFields_CrmOpportunity::ORG_ID]);
			return $fields;
		}
	
		static function getColumns() {
			$fields = self::getFields();
			unset($fields[SearchFields_CrmOpportunity::ID]);
			unset($fields[SearchFields_CrmOpportunity::PRIMARY_EMAIL_ID]);
			unset($fields[SearchFields_CrmOpportunity::ORG_ID]);
			return $fields;
		}
	
		function doResetCriteria() {
			parent::doResetCriteria();
			
			$this->params = array(
			);
		}
		
		function doSetCriteria($field, $oper, $value) {
			$criteria = null;
	
			switch($field) {
				case SearchFields_CrmOpportunity::NAME:
				case SearchFields_CrmOpportunity::ORG_NAME:
				case SearchFields_CrmOpportunity::EMAIL_ADDRESS:
					// force wildcards if none used on a LIKE
					if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
					&& false === (strpos($value,'*'))) {
						$value = '*'.$value.'*';
					}
					$criteria = new DevblocksSearchCriteria($field, $oper, $value);
					break;
					
				case SearchFields_CrmOpportunity::AMOUNT:
					$criteria = new DevblocksSearchCriteria($field,$oper,$value);
					break;
					
				case SearchFields_CrmOpportunity::IS_CLOSED:
				case SearchFields_CrmOpportunity::IS_WON:
					@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
					$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
					break;
					
				case SearchFields_CrmOpportunity::CREATED_DATE:
				case SearchFields_CrmOpportunity::UPDATED_DATE:
				case SearchFields_CrmOpportunity::CLOSED_DATE:		
					@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
					@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');
	
					if(empty($from)) $from = 0;
					if(empty($to)) $to = 'today';
	
					$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
					break;
					
				case SearchFields_CrmOpportunity::WORKER_ID:
					@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
					$criteria = new DevblocksSearchCriteria($field,$oper,$worker_id);
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
		
		function doBulkUpdate($filter, $do, $ids=array()) {
			@set_time_limit(600); // [TODO] Temp!
		  
			$change_fields = array();
			$custom_fields = array();
	
			// Make sure we have actions
			if(empty($do))
				return;
	
			// Make sure we have checked items if we want a checked list
			if(0 == strcasecmp($filter,"checks") && empty($ids))
				return;
				
			if(is_array($do))
			foreach($do as $k => $v) {
				switch($k) {
					case 'status':
						switch(strtolower($v)) {
							case 'open':
								$change_fields[DAO_CrmOpportunity::IS_CLOSED] = 0;
								$change_fields[DAO_CrmOpportunity::IS_WON] = 0;
								$change_fields[DAO_CrmOpportunity::CLOSED_DATE] = 0;
								break;
							case 'won':
								$change_fields[DAO_CrmOpportunity::IS_CLOSED] = 1;
								$change_fields[DAO_CrmOpportunity::IS_WON] = 1;
								$change_fields[DAO_CrmOpportunity::CLOSED_DATE] = time();
								break;
							case 'lost':
								$change_fields[DAO_CrmOpportunity::IS_CLOSED] = 1;
								$change_fields[DAO_CrmOpportunity::IS_WON] = 0;
								$change_fields[DAO_CrmOpportunity::CLOSED_DATE] = time();
								break;
						}
						break;
					case 'closed_date':
						$change_fields[DAO_CrmOpportunity::CLOSED_DATE] = intval($v);
						break;
					case 'worker_id':
						$change_fields[DAO_CrmOpportunity::WORKER_ID] = intval($v);
						break;
					default:
						// Custom fields
						if(substr($k,0,3)=="cf_") {
							$custom_fields[substr($k,3)] = $v;
						}
				}
			}
	
			$pg = 0;
	
			if(empty($ids))
			do {
				list($objects, $null) = DAO_CrmOpportunity::search(
					array(),
					$this->params,
					100,
					$pg++,
					SearchFields_CrmOpportunity::ID,
					true,
					false
				);
				$ids = array_merge($ids, array_keys($objects));
				
			} while(!empty($objects));
	
			// Broadcast?
			if(isset($do['broadcast'])) {
				$tpl_builder = DevblocksPlatform::getTemplateBuilder();
				
				$params = $do['broadcast'];
				if(
					!isset($params['worker_id']) 
					|| empty($params['worker_id'])
					|| !isset($params['subject']) 
					|| empty($params['subject'])
					|| !isset($params['message']) 
					|| empty($params['message'])
					)
					break;
	
				$is_queued = (isset($params['is_queued']) && $params['is_queued']) ? true : false; 
				
				if(is_array($ids))
				foreach($ids as $opp_id) {
					try {
						CerberusContexts::getContext(CerberusContexts::CONTEXT_OPPORTUNITY, $opp_id, $tpl_labels, $tpl_tokens);
						$subject = $tpl_builder->build($params['subject'], $tpl_tokens);
						$body = $tpl_builder->build($params['message'], $tpl_tokens);
						
						$fields = array(
							DAO_MailQueue::TYPE => Model_MailQueue::TYPE_COMPOSE,
							DAO_MailQueue::TICKET_ID => 0,
							DAO_MailQueue::WORKER_ID => $params['worker_id'],
							DAO_MailQueue::UPDATED => time(),
							DAO_MailQueue::HINT_TO => $tpl_tokens['email_address'],
							DAO_MailQueue::SUBJECT => $subject,
							DAO_MailQueue::BODY => $body,
							DAO_MailQueue::PARAMS_JSON => json_encode(array(
								'to' => $tpl_tokens['email_address'],
								'group_id' => $params['group_id'],
							)),
						);
						
						if($is_queued) {
							$fields[DAO_MailQueue::IS_QUEUED] = 1;
						}
						
						$draft_id = DAO_MailQueue::create($fields);
						
					} catch (Exception $e) {
						// [TODO] ...
					}
				}
			}		
			
			$batch_total = count($ids);
			for($x=0;$x<=$batch_total;$x+=100) {
				$batch_ids = array_slice($ids,$x,100);
				DAO_CrmOpportunity::update($batch_ids, $change_fields);
				
				// Custom Fields
				self::_doBulkSetCustomFields(CrmCustomFieldSource_Opportunity::ID, $custom_fields, $batch_ids);
				
				unset($batch_ids);
			}
	
			unset($ids);
		}	
	};
		
	
endif;

if(class_exists('DAO_FeedbackEntry', true)):
	class ChiPhoneFeedbackPage extends CerberusPageExtension {
		const VIEW_FEEDBACK = 'feedback';
		
		private $_TPL_PATH = '';
		
		public function __construct($manifest) {
			$this->DevblocksExtension($manifest);
			$this->_TPL_PATH = dirname(dirname(__FILE__)) . '/templates/';
		}
		
		function render() {
			
			$tpl = DevblocksPlatform::getTemplateService();
			$translate = DevblocksPlatform::getTranslationService();
			$response = DevblocksPlatform::getHttpResponse();
			
			$path = $response->path;
			array_shift($path); // iphone
			array_shift($path); // tickets
			$action = array_shift($path); // current action
			
			switch($action) {
				case 'display':
					$feedback_id = array_shift($path);
					$feedbackEntry = DAO_FeedbackEntry::get($feedback_id); 
					if(!empty($feedbackEntry->quote_address_id)) {
						if(null != ($address = DAO_Address::get($feedbackEntry->quote_address_id))) {
							$tpl->assign('address', $address);
						}
					}
					
					
					$tpl->assign('feedbackEntry', $feedbackEntry);
					$tpl->display('file:' . $this->_TPL_PATH . 'feedback/display.tpl');
					break;
				default:
					$defaults = new C4_AbstractViewModel();
					$defaults->class_name = 'View_FeedbackEntry_iPhone';
					$defaults->id = self::VIEW_FEEDBACK;
					$defaults->name = $translate->_('feedback.activity.tab');
					$defaults->view_columns = array(
						SearchFields_FeedbackEntry::LOG_DATE,
						SearchFields_FeedbackEntry::ADDRESS_EMAIL,
						SearchFields_FeedbackEntry::SOURCE_URL,
						SearchFields_FeedbackEntry::QUOTE_MOOD,
					);
					$defaults->renderSortBy = SearchFields_FeedbackEntry::LOG_DATE;
					$defaults->renderSortAsc = 0;
					
					$view = C4_AbstractViewLoader::getView(self::VIEW_FEEDBACK, $defaults);
					
					$tpl->assign('view', $view);
					$tpl->display('file:' . $this->_TPL_PATH . 'feedback/home.tpl');
					break;
			}
		}
		
		public function saveFeedbackAction()
		{
						
			$active_worker = CerberusApplication::getActiveWorker();
		
			// Make sure we're an active worker
			if(empty($active_worker) || empty($active_worker->id))
				return;
			@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
			@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
				
			@$email = DevblocksPlatform::importGPC($_POST['email'],'string','');
			@$mood = DevblocksPlatform::importGPC($_POST['mood'],'integer',0);
			@$quote = DevblocksPlatform::importGPC($_POST['quote'],'string','');
			@$url = DevblocksPlatform::importGPC($_POST['url'],'string','');
			@$source_extension_id = DevblocksPlatform::importGPC($_POST['source_extension_id'],'string','');
			@$source_id = DevblocksPlatform::importGPC($_POST['source_id'],'integer',0);
			
			// Translate email string into addy id, if exists
			$address_id = 0;
			if(!empty($email)) {
				if(null != ($author_address = DAO_Address::lookupAddress($email, true)))
					$address_id = $author_address->id;
			}
	
			// Delete entries
			if(!empty($id) && !empty($do_delete)) {
				if(null != ($entry = DAO_FeedbackEntry::get($id))) {
					// Only superusers and owners can delete entries
					if($active_worker->is_superuser || $active_worker->id == $entry->worker_id) {
						DAO_FeedbackEntry::delete($id);
					}
				}
				
				return;
			}
			
			// New or modify
			$fields = array(
				DAO_FeedbackEntry::QUOTE_MOOD => intval($mood),
				DAO_FeedbackEntry::QUOTE_TEXT => $quote,
				DAO_FeedbackEntry::QUOTE_ADDRESS_ID => intval($address_id),
				DAO_FeedbackEntry::SOURCE_URL => $url,
			);
	
			// Only on new
			if(empty($id)) {
				$fields[DAO_FeedbackEntry::LOG_DATE] = time();
				$fields[DAO_FeedbackEntry::WORKER_ID] = $active_worker->id;
			}
			
			if(empty($id)) { // create
				$id = DAO_FeedbackEntry::create($fields);
				
				// Post-create actions
				if(!empty($source_extension_id) && !empty($source_id))
				switch($source_extension_id) {
					case 'feedback.source.ticket':
						// Create a ticket comment about the feedback (to prevent dupes)
						if(null == ($worker_address = DAO_Address::lookupAddress($active_worker->email)))
							break;
							
						$comment_text = sprintf(
							"== Capture Feedback ==\n".
							"Author: %s\n".
							"Mood: %s\n".
							"\n".
							"%s\n",
							(!empty($author_address) ? $author_address->email : 'Anonymous'),
							(empty($mood) ? 'Neutral' : (1==$mood ? 'Praise' : 'Criticism')),
							$quote
						);
						$fields = array(
							DAO_TicketComment::ADDRESS_ID => $worker_address->id,
							DAO_TicketComment::COMMENT => $comment_text,
							DAO_TicketComment::CREATED => time(),
							DAO_TicketComment::TICKET_ID => intval($source_id),
						);
						DAO_TicketComment::create($fields);
						break;
				}
				
			} else { // modify
				DAO_FeedbackEntry::update($id, $fields);
			}
			
			// Custom field saves
			@$field_ids = DevblocksPlatform::importGPC($_POST['field_ids'], 'array', array());
			DAO_CustomFieldValue::handleFormPost(ChCustomFieldSource_FeedbackEntry::ID, $id, $field_ids);
			
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('iphone','feedback', 'display', $id)));	
		}
	};
	
	
	
class View_FeedbackEntry_iPhone extends C4_AbstractView {
	const DEFAULT_ID = 'feedback_entries';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('common.search_results');
		$this->renderLimit = 10;
		$this->renderSortBy = SearchFields_FeedbackEntry::LOG_DATE;
		$this->renderSortAsc = false;

		$this->view_columns = array(
			SearchFields_FeedbackEntry::LOG_DATE,
			SearchFields_FeedbackEntry::ADDRESS_EMAIL,
			SearchFields_FeedbackEntry::SOURCE_URL,
		);

		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_FeedbackEntry::search(
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
		$view_path = dirname(dirname(__FILE__)) . '/templates/feedback/';
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);
		
		$results = self::getData();
		$tpl->assign('results', $results);
		
		@$ids = array_keys($results[0]);
		
		// get the user agent
		// get the user agent
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')):
			 $maxcols = 1;
		elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')):
			$maxcols = 4;
		else:
			$maxcols = 4;
		endif;
		
		$tpl->assign('maxcols', $maxcols);
		
		// Custom fields
		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_FeedbackEntry::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . $view_path . 'view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		switch($field) {
			case SearchFields_FeedbackEntry::QUOTE_TEXT:
			case SearchFields_FeedbackEntry::SOURCE_URL:
			case SearchFields_FeedbackEntry::ADDRESS_EMAIL:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_FeedbackEntry::ID:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_FeedbackEntry::LOG_DATE:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__date.tpl');
				break;
			case SearchFields_FeedbackEntry::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$tpl->assign('workers', $workers);
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__worker.tpl');
				break;
			case SearchFields_FeedbackEntry::QUOTE_MOOD:
				// [TODO] Translations
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.feedback/templates/feedback/criteria/quote_mood.tpl');
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
			case SearchFields_FeedbackEntry::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$strings = array();

				foreach($values as $val) {
					if(0==$val) {
						$strings[] = "Nobody";
					} else {
						if(!isset($workers[$val]))
							continue;
						$strings[] = $workers[$val]->getName();
					}
				}
				echo implode(", ", $strings);
				break;

			case SearchFields_FeedbackEntry::QUOTE_MOOD:
				$strings = array();

				// [TODO] Translations
				foreach($values as $val) {
					switch($val) {
						case 0:
							$strings[] = "Neutral";
							break;
						case 1:
							$strings[] = "Praise";
							break;
						case 2:
							$strings[] = "Criticism";
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
		return SearchFields_FeedbackEntry::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_FeedbackEntry::ID]);
		unset($fields[SearchFields_FeedbackEntry::QUOTE_ADDRESS_ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		unset($fields[SearchFields_FeedbackEntry::ID]);
		unset($fields[SearchFields_FeedbackEntry::QUOTE_ADDRESS_ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
			SearchFields_FeedbackEntry::LOG_DATE => new DevblocksSearchCriteria(SearchFields_FeedbackEntry::LOG_DATE,DevblocksSearchCriteria::OPER_BETWEEN,array('-1 month','now')),
		);
	}
	
	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_FeedbackEntry::QUOTE_TEXT:
			case SearchFields_FeedbackEntry::SOURCE_URL:
			case SearchFields_FeedbackEntry::ADDRESS_EMAIL:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_FeedbackEntry::ID:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case SearchFields_FeedbackEntry::LOG_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
			case SearchFields_FeedbackEntry::WORKER_ID:
				@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$worker_id);
				break;
			case SearchFields_FeedbackEntry::QUOTE_MOOD:
				@$moods = DevblocksPlatform::importGPC($_REQUEST['moods'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$moods);
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
	
	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(0);
	  
		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;
			
		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
					break;
			}
		}

		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_FeedbackEntry::search(
				array(),
				$this->params,
				100,
				$pg++,
				SearchFields_FeedbackEntry::ID,
				true,
				false
			);
			 
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			DAO_FeedbackEntry::update($batch_ids, $change_fields);

			// Custom Fields
			self::_doBulkSetCustomFields(ChCustomFieldSource_FeedbackEntry::ID, $custom_fields, $batch_ids);

			unset($batch_ids);
		}

		unset($ids);
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
		
		// get the user agent
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')):
			 $maxcols = 1;
		elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')):
			$maxcols = 4;
		else:
			$maxcols = 4;
		endif;
		
		$tpl->assign('maxcols', $maxcols);
		
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

class View_Task_iPhone extends C4_AbstractView {
	const DEFAULT_ID = 'tasks';
	const DEFAULT_TITLE = 'All Open Tasks';

	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = self::DEFAULT_TITLE;
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Task::DUE_DATE;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Task::SOURCE_EXTENSION,
			SearchFields_Task::UPDATED_DATE,
			SearchFields_Task::DUE_DATE,
			SearchFields_Task::WORKER_ID,
			);
		
		$this->params = array(
			SearchFields_Task::IS_COMPLETED => new DevblocksSearchCriteria(SearchFields_Task::IS_COMPLETED,'=',0),
		);
	}

	function getData() {
		$objects = DAO_Task::search(
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
		$view_path = dirname(dirname(__FILE__)) . '/templates/tasks/';
		$tpl->assign('view_path',$view_path);
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);

		$tpl->assign('timestamp_now', time());

		// Pull the results so we can do some row introspection
		$results = $this->getData();
		$tpl->assign('results', $results);

//		$source_renderers = DevblocksPlatform::getExtensions('cerberusweb.task.source', true);
		
		// Make a list of unique source_extension and load their renderers
		$source_extensions = array();
		if(is_array($results) && isset($results[0]))
		foreach($results[0] as $rows) {
			$source_extension = $rows[SearchFields_Task::SOURCE_EXTENSION];
			if(!isset($source_extensions[$source_extension]) 
				&& !empty($source_extension)
				&& null != ($mft = DevblocksPlatform::getExtension($source_extension))) {
				$source_extensions[$source_extension] = $mft->createInstance();
			} 
		}
		$tpl->assign('source_renderers', $source_extensions);
		
		// Custom fields
		$custom_fields = DAO_CustomField::getBySource(ChCustomFieldSource_Task::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		// get the user agent
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')):
			 $maxcols = 1;
		elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')):
			$maxcols = 4;
		else:
			$maxcols = 4;
		endif;
		
		$tpl->assign('maxcols', $maxcols);
		
		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . $view_path . 'view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = APP_PATH . '/features/cerberusweb.core/templates/';
		$tpl->assign('id', $this->id);
		
		switch($field) {
			case SearchFields_Task::TITLE:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__string.tpl');
				break;
				
			case SearchFields_Task::SOURCE_EXTENSION:
				$source_renderers = DevblocksPlatform::getExtensions('cerberusweb.task.source', true);
				$tpl->assign('sources', $source_renderers);
				$tpl->display('file:' . $tpl_path . 'tasks/criteria/source.tpl');
				break;
				
			case SearchFields_Task::IS_COMPLETED:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__bool.tpl');
				break;
				
			case SearchFields_Task::UPDATED_DATE:
			case SearchFields_Task::DUE_DATE:
			case SearchFields_Task::COMPLETED_DATE:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__date.tpl');
				break;
				
			case SearchFields_Task::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$tpl->assign('workers', $workers);
				
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__worker.tpl');
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
		$translate = DevblocksPlatform::getTranslationService();
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			case SearchFields_Task::WORKER_ID:
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
				
			case SearchFields_Task::SOURCE_EXTENSION:
				$sources = $ext = DevblocksPlatform::getExtensions('cerberusweb.task.source', true);			
				$strings = array();
				
				foreach($values as $val) {
					if(!isset($sources[$val]))
						continue;
					else
						$strings[] = $sources[$val]->getSourceName();
				}
				echo implode(", ", $strings);
				break;
			
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_Task::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_Task::ID]);
		unset($fields[SearchFields_Task::SOURCE_ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		unset($fields[SearchFields_Task::ID]);
		unset($fields[SearchFields_Task::SOURCE_ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
			SearchFields_Task::IS_COMPLETED => new DevblocksSearchCriteria(SearchFields_Task::IS_COMPLETED,'=',0)
		);
	}
	
	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_Task::TITLE:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
				
			case SearchFields_Task::SOURCE_EXTENSION:
				@$sources = DevblocksPlatform::importGPC($_REQUEST['sources'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$sources);
				break;
				
			case SearchFields_Task::UPDATED_DATE:
			case SearchFields_Task::COMPLETED_DATE:
			case SearchFields_Task::DUE_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;

			case SearchFields_Task::IS_COMPLETED:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			case SearchFields_Task::WORKER_ID:
				@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$worker_id);
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
	
	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(600); // [TODO] Temp!
	  
		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;
			
		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				case 'due':
					@$date = strtotime($v);
					$change_fields[DAO_Task::DUE_DATE] = intval($date);
					break;
				case 'status':
					if(1==intval($v)) { // completed
						$change_fields[DAO_Task::IS_COMPLETED] = 1;
						$change_fields[DAO_Task::COMPLETED_DATE] = time();
					} else { // active
						$change_fields[DAO_Task::IS_COMPLETED] = 0;
						$change_fields[DAO_Task::COMPLETED_DATE] = 0;
					}
					break;
				case 'worker_id':
					$change_fields[DAO_Task::WORKER_ID] = intval($v);
					break;
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_Task::search(
				array(),
				$this->params,
				100,
				$pg++,
				SearchFields_Task::ID,
				true,
				false
			);
			 
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			DAO_Task::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(ChCustomFieldSource_Task::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};


?>