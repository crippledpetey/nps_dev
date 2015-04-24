<?php

class NPS_HipChat_IndexController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		//create connection
		$this->setConnection();

		//set page variables
		$this->setNPSClassVars();

		//start hipchat integration
		$this->hipChat = startHipChat();

		//function array to control output of primary content
		$displayModes = array(
			'npsHipChatWelcomePage',
			'listChatRooms',
		);

		$primaryContent = '<style>' . file_get_contents(Mage::getBaseDir('base') . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'NPS' . DS . 'HipChat' . DS . 'Helper' . DS . 'HipChatStyle.css') . '</style>';
		$primaryContent .= '<div id="nps-hipchat-manager-container">' . call_user_func(array($this, $displayModes[$this->hcf])) . '</div>';

		//run all pre head commands
		$this->requestFunctions();

		//load the layout
		$this->loadLayout();

		//set the menu item active
		$this->_setActiveMenu('system/nps_hipchat_menu');

		//set left block
		$leftBlock = $this->getLayout()
		                  ->createBlock('core/text')
		                  ->setText($this->leftColumnHtml());

		//compile the lyout
		$block = $this->getLayout()
		              ->createBlock('core/text', 'nps-hipchat-control-panel')
		              ->setText($primaryContent);

		//add content block to layout
		$this->_addLeft($leftBlock);
		$this->_addContent($block);

		//render the layout
		$this->renderLayout();
	}
/**
PAGE LOAD FUNCTIONS THAT CONTROL UPDATES
 */
	private function setConnection() {
		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();
	}
	public function requestFunctions() {

		//set default refresh values
		$refresh = false;
		$append_url = null;

		//if refresh is true then reload the page to prevent duplicate posting
		if ($refresh) {
			session_write_close();
			if (!empty($append_url)) {$append_url = '?' . $append_url;}
			Mage::app()->getFrontController()->getResponse()->setRedirect($_SERVER['REQUEST_URI'] . $append_url);
		}
	}

/**
HTML OUTPUT MEHTODS
 */
	private function leftColumnHtml() {

		//set url parts
		$url = explode('?', $_SERVER['PHP_SELF']);
		$url_base = $url[0];
		if (!empty($url[1])) {
			$params = explode('&', $url[1]);
		} else {
			$params = array();
		}

		//title and list start
		$html = '<h2 style="border-bottom: 1px dotted #d9d9d9;font-size:15px;">Hip Chat Manager</h2>';
		$html .= '<ul id="nps-hipchat-manager-nav">';

		//set nav links
		$html .= '<a href="' . $url_base . '?hcf=1" title="Hip Chat Room List"><li class="' . $this->active(1, $this->hcf) . '">List Chat Rooms</li></a>';

		//insert separator
		$html .= '<li class="separator"></li>';

		//close the list
		$html .= '</ul>';

		return $html;
	}

	private function npsHipChatWelcomePage() {
		$html = '<h1>Hip Chat Manager</h1>';
		$html .= '<p>Please select a function from the left</p>';

		//$this->toChatRoom('Code Releases', 'Just testing another message to see if it\'s working after moving the HipChat instantiator to the primary functions file');

		return $html;
	}
	private function listChatRooms() {
		$html = '<h1>Hip Chat - Chat Room Manager</h1>';

		// list rooms
		$html .= '	<ul id="hip-chat-room-list">';
		foreach ($this->hipChat->get_rooms() as $room) {
			$html .= "<li><strong>" . $room->name . "</strong> <span class='note'>(id: " . $room->room_id . ")</li>";
		}
		$html .= '	</ul>';

		return $html;
	}

/**
HIP CHAT INTERACTIONS
 */
	public function toChatRoom($room, $message) {
		// send a message to the specified room
		$this->hipChat->message_room($room, 'API', $message);

	}
/**
DATABASE AND OTHER UPDATE METHODS CALLED BY  $this->requestFunctions()
 */

/**
INFASTRUCTURE METHODS
 */

	private function setNPSClassVars() {
		if (isset($_GET['hcf'])) {
			$this->hcf = $_GET['hcf'];
		} else {
			$this->hcf = 0;
		}
	}
	public function checked($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' checked ';
			}
		} else {
			return false;
		}
	}
	public function selected($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' selected ';
			}
		} else {
			return false;
		}
	}
	public function active($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' active ';
			}
		} else {
			return false;
		}
	}

}
?>