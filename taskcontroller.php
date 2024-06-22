<?php
class TaskController extends LegitController
{
    private $common;
    private $bid;
    private $wallet;
    private $transactionsController;
    private $ratings;
    private $reviews;
    private $messages;
    private $Order;
    private $userController;
    private $user;

    public function __construct($model, $controller, $action)
    {
        parent::__construct($model, $controller, $action);
        $this->common	= new Common();
        $this->bid		= new Bid();
        $this->wallet	= new Wallet();
        $this->ratings	= new Ratings();
        $this->reviews	= new Reviews();
        $this->messages	= new Messages();
        $this->Order	= new Order();
        $this->user	= new user();

        $this->transactionsController = new TransactionsController('transactions', 'transactions', 'getLoadWalletForm');
        $this->userController = new UserController('user', 'user', 'getLoginForm');
    }

    /* Function to list customer's orders */
    public function my_orders($filter='')
    {
        $post = Syspage::getPostedVar();
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            redirectUser(generateUrl('page_not_found'));
        }/* die( Utilities::getLabel( 'E_Invalid_request' ) ); */
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!in_array($filter, array('mine', 'ongoing', 'completed', 'cancelled', 'invites'))) {
            $filter = 'mine';
        }

        $first_order = Common::is_first_order_done();
        if ($first_order == false) {
            $data = $this->Task->getCustomerOrders('mine', 1);
            foreach ($data['orders'] as $value) {
                Message::addMessage(Utilities::getLabel('S_Complete_Order_Process_Notify_Message') . ' <a href='.generateUrl('task', 'add', array($value['task_id'])).'>' . Utilities::getLabel('L_click_here') . '</a>');
            }
        }
       
        $this->set('filter', $filter);
        $this->set('arr_messages', $this->messages->getConversationHistory($data['bid_id']));
        $this->set('arr_orders', $this->Task->getCustomerOrders('mine', 1));
        $this->set('arr_ongoing_orders', $this->Task->getCustomerOrders('ongoing', 1));
        $this->set('arr_disputed', $this->Task->getCustomerOrders('disputed', 1));
        $this->set('arr_delivered', $this->Task->getCustomerOrders('delivered', 1));
        $this->set('arr_completed_orders', $this->Task->getCustomerOrders('completed', 1));
        $this->set('arr_cancelled_orders', $this->Task->getCustomerOrders('cancelled', 1));
        $this->set('arr_invites', $this->Task->getPrivateOrders());

        $this->_template->render();
    }

    /* Function to list customer's orders */
    public function my_papers($filter='')
    {
        $post = Syspage::getPostedVar();
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            redirectUser(generateUrl('page_not_found'));
        }/* die( Utilities::getLabel( 'E_Invalid_request' ) ); */
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!in_array($filter, array('mine', 'ongoing', 'completed', 'cancelled', 'invites'))) {
            $filter = 'mine';
        }

        $first_order = Common::is_first_order_done();
        if ($first_order == false) {
            $data = $this->Task->getCustomerOrders('mine', 1);
            foreach ($data['orders'] as $value) {
                Message::addMessage(Utilities::getLabel('S_Complete_Order_Process_Notify_Message') . ' <a href='.generateUrl('task', 'add', array($value['task_id'])).'>' . Utilities::getLabel('L_click_here') . '</a>');
            }
        }
       
        $this->set('filter', $filter);
        $this->set('arr_messages', $this->messages->getConversationHistory($data['bid_id']));
        $this->set('arr_orders', $this->Task->getCustomerOrders('mine_papers', 1));
        $this->set('arr_ongoing_orders', $this->Task->getCustomerOrders('ongoing_papers', 1));
        $this->set('arr_completed_orders', $this->Task->getCustomerOrders('completed_papers', 1));
        $this->set('arr_cancelled_orders', $this->Task->getCustomerOrders('cancelled_papers', 1));
        $this->set('arr_invites', $this->Task->getPrivateOrders());
        $this->set('arr_task', $this->Task->getTaskDetailsById($task_id));
        $this->set('arr_blog_url', $this->Task->getBlogUrlById(19773));
        
        

        $this->_template->render();
    }

    /* function to get private orders */
    public function invites()
    {
        $post = Syspage::getPostedVar();

        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }

        $arr_orders = $this->Task->getPrivateOrders($page);

        $arr_orders['str_paging'] = generatePagingStringHtml($arr_orders['page'], $arr_orders['pages'], "invites(xxpagexx);");

        $this->set('arr_orders', $arr_orders);

        $this->_template->render(false, false);
    }

    public function bids()
    {
        $post = Syspage::getPostedVar();

        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }

        $filter = $post['filter'];
        if (!in_array($filter, array('mine', 'ongoing', 'completed', 'cancelled', 'invites'))) {
            $filter = 'mine';
        }

        $arr_orders = (array) $this->Task->getCustomerOrders($filter, $page);
		foreach($arr_orders['orders'] as $key=>$arr)
		{
            if (!User::isWriter()) {
                $first_order = Common::is_first_order_done();
                if ($first_order == false) {
                    //echo '<td><a href="'.generateUrl("task","add",array($arr["task_id"])).'">#'. $arr['task_ref_id'].'</td>';
                    $action = 1;
                //$action = '<td class="dataTable"><a href="'.generateUrl("task","add",array($arr["task_id"])).'" class="theme-btn">View Order</a></td>';
                } else {
                    if (!User::isUserActive()) {
                    } else {
                        $action = 1;
                        //$action = '<td class="dataTable"><a href="'.generateUrl('task', 'order_bids', array($arr['task_id'])).'" class="theme-btn">View Order</a></td>';
                    }
                }
            }
            $arr_orders['orders'][$key]['action'] = $action;
        }

        $arr_orders['str_paging'] = generatePagingStringHtml($arr_orders['page'], $arr_orders['pages'], "bids('" . $filter . "',xxpagexx);");

        $this->set('arr_orders', $arr_orders);

        $this->_template->render(false, false);
    }

    /* Function to list bids for a particular order */
    public function order_bids($task_id)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            redirectUser(generateUrl('page_not_found'));
        }/* die( Utilities::getLabel( 'E_Invalid_request' ) ); */
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //echo $task_id;
        $arr_task = $this->Task->getTaskDetailsById($task_id);
        //echo '<pre>'. print_r($arr_task,true);exit;
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_bids = $this->bid->getOrderBids($task_id);

        if (count($arr_bids) > 0) {
            foreach ($arr_bids as $key=>$ele) {
                $arr_bids[$key]['bidder_rating']	= $this->ratings->getAvgUserRating($ele['bid_user_id']);
                $arr_bids[$key]['orders_completed']	= $this->Order->getUserCompletedOrders($ele['bid_user_id']);
            }
        }

        $files = $this->Task->getOrderFiles($task_id, 1);

        $this->set('files', $files);
        $this->set('order', $arr_task);
        $this->set('arr_bids', $arr_bids);

        $this->_template->render();
    }

    public function setup()
    {
        $post = Syspage::getPostedVar();
        $db = &Syspage::getdb();

        if (!User::isUserLogged() || User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($post['task_id']);
        $post['task_paptype_id'] = intval($post['task_paptype_id']);

        $paptype = Common::getPaperTypes();
        $paptype = array_keys($paptype);

        if (isset($post['btn_submit']) && !in_array($post['task_paptype_id'], $paptype)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Paper_Type'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }
        $discipline = Common::getDisciplines();
        $discipline = array_keys($discipline);

        if (isset($post['btn_submit']) && !in_array($post['task_discipline_id'], $discipline)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_discipline'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add($post['task_id']);
            exit;
            return;
        }

        $services = Common::getServices();
        $services = array_keys($services);

        if (isset($post['btn_submit']) && !in_array($post['task_service_type'], $services)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Service_Type'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }

        $citation_style = Common::getCitationStyles();
        $citation_style = array_keys($citation_style);

        if (isset($post['btn_submit']) && !in_array($post['task_citstyle_id'], $citation_style)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Citation_Style'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }

        /* if(isset($post['btn_submit']) && $post['task_due_date']=='') {
            Message::addErrorMessage( Utilities::getLabel( 'E_Deadline_Restriction_Message' ) );
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        } */

        $frm = $this->getTaskForm();

        if ($task_id > 0) {
            $fld = $frm->getField('task_due_date');
            $fld->requirements()->setRequired(false);
		} 
		else{
            if (isset($post['btn_submit']) && ($post['task_due_date']=='' || $post['task_due_date']=='Choose Deadline')) {
                Message::addErrorMessage(Utilities::getLabel('E_Deadline_Restriction_Message'));
                $this->_template = new Template('task', 'add');
                $this->setLegitControllerCommonData('', 'add');
                $this->add();
                return;
            }
        }
        if (isset($data['task_topic']) && $data['task_topic']!='' && $task_id > 0) {
            $fld_set = $frm->setOnSubmit('return confirmation();');
        }


        if ($tid < 1) {
            $cust = $frm->getField('user_first_name');
            $cust->requirements()->setRequired(false);
            $user = $frm->getField('user_last_name');
            $user->requirements()->setRequired(false);
            $country = $frm->getField('user_country_id');
            $country->requirements()->setRequired(false);
            $phone = $frm->getField('user_phone');
            $phone->requirements()->setRequired(false);
            $password = $frm->getField('user_password');
            $password->requirements()->setRequired(false);
        }

        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                Message::addErrorMessage($error);
            }
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }

        $invitee_id = intval($post['invitee_ids']);

        $data = $post;

        $data['task_user_id']	= User::getLoggedUserAttribute('user_id');
        if ($task_id > 0) {
            $srch = new SearchBase('tbl_tasks');
            $srch->addCondition('task_id', '=', $task_id);
            $srch->addFld('task_due_date');
            $srch->addFld('task_status');
            $rs = $srch->getResultSet();

            $row = $db->fetch($rs);

            $data['task_due_date'] = date('Y-m-d H:i:s', strtotime($row['task_due_date']));
            $data['task_status']= 0;

            $srch1 = new SearchBase('tbl_users');
            $srch1->addCondition('user_id', '=', User::getLoggedUserAttribute('user_id'));
            $srch1->addFld('user_ref_id');
            $rs1 = $srch1->getResultSet();
            $row1= $db->fetch($rs1);


            $data['user_ref_id']= $row1['user_ref_id'];
        }

        if ($task_id == 0) {
            $data['task_type']	= $invitee_id > 0 ? 1 : 0;
            $data['task_status']	= 0;//changed on 15/may/2014 by Aksheer
		}
		
		 $task_due_date = array();
            $task_due_date = explode('_',$post['task_due_date']);

            switch ($task_due_date[1]) {
                case 'h' :
                    $post['task_due_date'] = strtotime('+'.$task_due_date[0].' hours',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                    break;
                case 'n' :
                    $data['task_due_date'] = date('Y-m-d H:i:s'.$task_due_date[0].':00',strtotime('tomorrow'));
                    break;
                case 'd' :
                    $post['task_due_date'] = strtotime('+'.$task_due_date[0].' days',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                    break;
                default :
                    $post['task_due_date'] = strtotime('+7 days',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                break;
        }

        /** upload additional material **/
        $arr_uploaded_files = $this->upload_files($_FILES['task_files']);

        if ($arr_uploaded_files === false) {
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add($task_id);
            return;
        }
        //echo "<pre>".print_r($data,true)."</pre>";exit;
        if (!$this->Task->addTask($data)) {
            Message::addErrorMessage($this->Task->getError());
            redirectUser(generateUrl('task', 'my_orders'));
        }
		else {
			if(isset($post['btn_submit']) && isset($post['task_discount_code']) && !empty($post['task_discount_code'])) {
				if(!$this->Task->deleteDiscountCode($data['task_user_id'], $post['task_discount_code'])) {
					echo "failed";
				} else {
					echo "updated";
				}
			}
		}

        //die($invitee_id);
        if ($invitee_id > 0) {
            if ($this->Task->addTaskInvitees(array('inv_task_id'=>$this->Task->getTaskId(), 'inv_writer_id'=>$invitee_id, 'inv_status'=>0))) {
                $objUser = new User($invitee_id);

                $param = array('to'=>$objUser->getAttribute('user_email'),'temp_num'=>7,'user_screen_name'=>$objUser->getAttribute('user_screen_name'),'user_first_name'=>User::getLoggedUserAttribute('user_screen_name'),'order_page_link'=>generateAbsoluteUrl('bid', 'add', array($this->Task->getTaskId())));
                $this->Task->sendEmail($param);
            }
        }

        //add files into db
        if (!empty($arr_uploaded_files)) {
            foreach ($arr_uploaded_files as $ele) {
                if (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name'])) {
                    if (!$this->Task->addFile(array('file_task_id'=>$this->Task->getTaskId(), 'file_name'=>$ele['file_name'], 'file_download_name'=>$ele['file_download_name'], 'file_type'=>$ele['file_type'],'file_size'=>$ele['file_size'], 'file_class'=>1))) {
                        unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name']);
                        Message::addErrorMessage($this->Task->getError());
                    }
                }
            }
        }

        if ($task_id > 0) {
            Message::addMessage(Utilities::getLabel('S_Order_Updated_Message'));
        } else {
            Message::addMessage(Utilities::getLabel('S_Order_Submitted_For_Approval'));
        }

		$task_id = $this->Task->getTaskId();
		$task_ref_id = $db->fetch($db->query('SELECT task_ref_id from tbl_tasks where task_id='.$task_id));
		$_SESSION['order_no'] = $task_ref_id['task_ref_id'];
		$_SESSION['task_idd'] = $task_id;
		$_SESSION['task_topicc'] = $post['task_topic'];
		$_SESSION['task_pagess'] = $post['task_pages']; 
		$_SESSION['load_amount'] = $post['task_budget'];
        redirectUser(generateUrl('task', 'my_orders'));
    }
    
    public function paperbuyingsetup()
    {
        $post = Syspage::getPostedVar();
        $db = &Syspage::getdb();

        if (!User::isUserLogged() || User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($post['task_id']);
        $post['task_paptype_id'] = intval($post['task_paptype_id']);

        $paptype = Common::getPaperTypes();
        $paptype = array_keys($paptype);

        /*if (isset($post['btn_submit']) && !in_array($post['task_paptype_id'], $paptype)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Paper_Type'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }*/
        $discipline = Common::getDisciplines();
        $discipline = array_keys($discipline);

        /*if (isset($post['btn_submit']) && !in_array($post['task_discipline_id'], $discipline)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_discipline'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add($post['task_id']);
            exit;
            return;
        }*/

        $services = Common::getServices();
        $services = array_keys($services);

        /*if (isset($post['btn_submit']) && !in_array($post['task_service_type'], $services)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Service_Type'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }*/

        $citation_style = Common::getCitationStyles();
        $citation_style = array_keys($citation_style);

        /* if (isset($post['btn_submit']) && !in_array($post['task_citstyle_id'], $citation_style)) {
            Message::addErrorMessage(Utilities::getLabel('E_Invalid_Citation_Style'));
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }

        if(isset($post['btn_submit']) && $post['task_due_date']=='') {
            Message::addErrorMessage( Utilities::getLabel( 'E_Deadline_Restriction_Message' ) );
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        } */

        $frm = $this->getBuyPaperForm();

        if ($task_id > 0) {
            $fld = $frm->getField('task_due_date');
            $fld->requirements()->setRequired(false);
		} 
		else{
            if (isset($post['btn_submit']) && ($post['task_due_date']=='' || $post['task_due_date']=='Choose Deadline')) {
                Message::addErrorMessage(Utilities::getLabel('E_Deadline_Restriction_Message'));
                $this->_template = new Template('task', 'add');
                $this->setLegitControllerCommonData('', 'add');
                $this->add();
                return;
            }
        }
        if (isset($data['task_topic']) && $data['task_topic']!='' && $task_id > 0) {
            $fld_set = $frm->setOnSubmit('return confirmation();');
        }


        if ($tid < 1) {
            $cust = $frm->getField('user_first_name');
            $cust->requirements()->setRequired(false);
            $user = $frm->getField('user_last_name');
            $user->requirements()->setRequired(false);
            $country = $frm->getField('user_country_id');
            $country->requirements()->setRequired(false);
            $phone = $frm->getField('user_phone');
            $phone->requirements()->setRequired(false);
            $password = $frm->getField('user_password');
            $password->requirements()->setRequired(false);
        }

        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                Message::addErrorMessage($error);
            }
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add();
            return;
        }

        $invitee_id = intval($post['invitee_ids']);

        $data = $post;

        $data['task_user_id']	= User::getLoggedUserAttribute('user_id');
        if ($task_id > 0) {
            $srch = new SearchBase('tbl_tasks');
            $srch->addCondition('task_id', '=', $task_id);
            $srch->addFld('task_due_date');
            $srch->addFld('task_status');
            $rs = $srch->getResultSet();

            $row = $db->fetch($rs);

            $data['task_due_date'] = date('Y-m-d H:i:s', strtotime($row['task_due_date']));
            $data['task_status']= 0;

            $srch1 = new SearchBase('tbl_users');
            $srch1->addCondition('user_id', '=', User::getLoggedUserAttribute('user_id'));
            $srch1->addFld('user_ref_id');
            $rs1 = $srch1->getResultSet();
            $row1= $db->fetch($rs1);


            $data['user_ref_id']= $row1['user_ref_id'];
        }

        if ($task_id == 0) {
            $data['task_type']	= $invitee_id > 0 ? 1 : 0;
            $data['task_status']	= 0;//changed on 15/may/2014 by Aksheer
		}
		
		 $task_due_date = array();
            $task_due_date = explode('_',$post['task_due_date']);

            switch ($task_due_date[1]) {
                case 'h' :
                    $post['task_due_date'] = strtotime('+'.$task_due_date[0].' hours',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                    break;
                case 'n' :
                    $data['task_due_date'] = date('Y-m-d H:i:s'.$task_due_date[0].':00',strtotime('tomorrow'));
                    break;
                case 'd' :
                    $post['task_due_date'] = strtotime('+'.$task_due_date[0].' days',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                    break;
                default :
                    $post['task_due_date'] = strtotime('+7 days',time());
                    $data['task_due_date'] = date('Y-m-d H:i:s',$post['task_due_date']);
                break;
        }

        /** upload additional material **/
        $arr_uploaded_files = $this->upload_files($_FILES['task_files']);

        if ($arr_uploaded_files === false) {
            $this->_template = new Template('task', 'add');
            $this->setLegitControllerCommonData('', 'add');
            $this->add($task_id);
            return;
        }
        //echo "<pre>".print_r($data,true)."</pre>";exit;
        if (!$this->Task->addTask($data)) {
            Message::addErrorMessage($this->Task->getError());
            redirectUser(generateUrl('task', 'my_orders'));
        }
		else {
			if(isset($post['btn_submit']) && isset($post['task_discount_code']) && !empty($post['task_discount_code'])) {
				if(!$this->Task->deleteDiscountCode($data['task_user_id'], $post['task_discount_code'])) {
					echo "failed";
				} else {
					echo "updated";
				}
			}
		}

        //die($invitee_id);
        if ($invitee_id > 0) {
            if ($this->Task->addTaskInvitees(array('inv_task_id'=>$this->Task->getTaskId(), 'inv_writer_id'=>$invitee_id, 'inv_status'=>0))) {
                $objUser = new User($invitee_id);

                $param = array('to'=>$objUser->getAttribute('user_email'),'temp_num'=>7,'user_screen_name'=>$objUser->getAttribute('user_screen_name'),'user_first_name'=>User::getLoggedUserAttribute('user_screen_name'),'order_page_link'=>generateAbsoluteUrl('bid', 'add', array($this->Task->getTaskId())));
                $this->Task->sendEmail($param);
            }
        }

        //add files into db
        if (!empty($arr_uploaded_files)) {
            foreach ($arr_uploaded_files as $ele) {
                if (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name'])) {
                    if (!$this->Task->addFile(array('file_task_id'=>$this->Task->getTaskId(), 'file_name'=>$ele['file_name'], 'file_download_name'=>$ele['file_download_name'], 'file_type'=>$ele['file_type'],'file_size'=>$ele['file_size'], 'file_class'=>1))) {
                        unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name']);
                        Message::addErrorMessage($this->Task->getError());
                    }
                }
            }
        }

        if ($task_id > 0) {
            Message::addMessage(Utilities::getLabel('S_Order_Updated_Message'));
        } else {
            Message::addMessage(Utilities::getLabel('S_Order_Submitted_For_Approval'));
        }

		$task_id = $this->Task->getTaskId();
		$task_ref_id = $db->fetch($db->query('SELECT task_ref_id from tbl_tasks where task_id='.$task_id));
		$_SESSION['order_no'] = $task_ref_id['task_ref_id'];
		$_SESSION['task_idd'] = $task_id;
		$_SESSION['task_topicc'] = $post['task_topic'];
		$_SESSION['task_pagess'] = $post['task_pages']; 
		$_SESSION['load_amount'] = $post['task_budget'];
        redirectUser(generateUrl('task', 'my_orders'));
    }

    public function update_project_file()
    {
        $post = Syspage::getPostedVar();

        if (!User::isUserLogged() || !User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $order_details = $this->Task->getOrderDetails($task_id);
        if ($order_details === false || $order_details['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //echo "<pre>";print_r($order_details);exit;
        $arr_uploaded_files = $this->upload_files($_FILES['task_files'], false);

        if ($arr_uploaded_files === false) {
            redirectUser(generateUrl('task', 'order_process', array($task_id)));
        }

        if (!empty($arr_uploaded_files)) {
            foreach ($arr_uploaded_files as $ele) {
                if (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name'])) {
                    if (!$this->Task->addFile(array('file_task_id'=>$task_id, 'file_name'=>$ele['file_name'], 'file_download_name'=>$ele['file_download_name'], 'file_type'=>$ele['file_type'],'file_size'=>$ele['file_size'], 'file_class'=>2, 'cus_req_to_upload_again'=>$order_details['cus_req_to_upload_again']))) {
                        unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name']);
                        Message::addErrorMessage($this->Task->getError());
                    }
                }
            }
            if (!empty($order_details['customer_email'])) {
                $order_page_link = generateAbsoluteUrl('task', 'order_process', array($task_id));

                $this->Task->sendEmail(array('to'=>$order_details['customer_email'],'temp_num'=>38,'user_screen_name'=>$order_details['user_first_name'],'user_first_name'=>$order_details['writer'],'order_ref_id'=>$order_details['task_ref_id'],'order_page_link'=>$order_page_link));
                $this->Task->sendEmail(array('to'=>$order_details['writer_email'],'temp_num'=>39,'user_screen_name'=>$order_details['writer'],'order_ref_id'=>$order_details['task_ref_id'],'order_page_link'=>$order_page_link));

            }
            Message::addMessage(Utilities::getLabel('S_FILE_UPLOADED_MARK_COMPLETE_NOTIFY_ADMIN'));
        } else {
            Message::addErrorMessage(Utilities::getLabel('E_No_File_Selected'));
        }
        if (!User::isEditor()) {
        redirectUser(generateUrl('task', 'order_process', array($task_id)));
        }else {
            redirectUser(generateUrl('task', 'order_editing', array($task_id)));
        }
    }

    private function upload_files($arr_files, $can_upload_images=true)
    {
        $error = 0;
        $total_file_size = 0;
        $num_files_uploaded = count($arr_files['name']);



        $arr_uploaded_files = array();

        if ($num_files_uploaded > 0) {
            $arr_valid_text_file_types	= array(
                                                'text/plain',
                                                'application/msword',
												'application/vnd.ms-powerpoint',
                                                'application/pdf',
                                                'application/vnd.ms-excel',
												'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
												'application/vnd.ms-excel.addin.macroEnabled.12',
												'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
												'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                                'application/msword',
                                                'application/zip',
												'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                                                                                                'application/vnd.openxmlformats-officedocument.presentationml.template',
											'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
											'application/vnd.openxmlformats-officedocument.presentationml.presentation',
											'application/vnd.openxmlformats-officedocument.presentationml.slide',
											'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
											'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
											'application/vnd.ms-excel.addin.macroEnabled.12',
											'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $arr_valid_image_file_types	= array('image/jpeg', 'image/png');

            $arr_valid_file_types = $can_upload_images === false ? $arr_valid_text_file_types : array_merge($arr_valid_text_file_types, $arr_valid_image_file_types);

            /** throw error if total file size exceeds 30MB. **/
            $total_file_size = array_sum($arr_files['size']);

            if ($total_file_size > 32086425) {
                $limit = ini_get('upload_max_filesize');
                Message::addErrorMessage(sprintf(Utilities::getLabel('E_File_Size_Not_Exceed'), $limit));
                return false;
            }

            for ($i=0; $i<$num_files_uploaded; $i++) {
                if (is_uploaded_file($arr_files['tmp_name'][$i])) {
                    $path_parts = pathinfo($arr_files['name'][$i]);

                    $base_name	= $path_parts['basename'];

                    if ($arr_files['error'][$i] != 0) {
                        Message::addErrorMessage(Utilities::getLabel('L_Error_uploading') . ' ' . $base_name);
                        $error = 1;
                        break;
                    }
                    $mime = getMIMETypeInfo($arr_files['tmp_name'][$i]);
                    //echo "<pre>".print_r($mime,true)."</pre>";exit;
                    if (!in_array($mime, $arr_valid_file_types)) {
                        Message::addErrorMessage(sprintf(Utilities::getLabel('L_Could_Not_Upload_File'), $base_name));
                        $error = 1;
                        break;
                    }

                    if ($arr_files['size'][$i] > 10695475) {				//restrict file size to 10MB
                        $limit = ini_get('upload_max_filesize');
                        Message::addErrorMessage(sprintf(Utilities::getLabel('E_Could_Not_Upload_File_Size_Is_Big'), $base_name, $limit));
                        $error = 1;
                        break;
                    }

                    if (in_array($arr_files['type'][$i], $arr_valid_image_file_types)) {
                        $file_type = 2;

                        if (!saveImage($arr_files['tmp_name'][$i], $arr_files['name'][$i], $saved_file_name)) {
                            Message::addErrorMessage($saved_file_name);
                            $error = 1;
                            break;
                        }
					}
					else {
                        $file_type = 1;

                        if (!saveFile($arr_files['tmp_name'][$i], $arr_files['name'][$i], $saved_file_name)) {
                            Message::addErrorMessage($saved_file_name);
                            $error = 1;
                            break;
                        }
                    }

                    $arr_uploaded_files[$i]['file_name']			= $saved_file_name;
                    $arr_uploaded_files[$i]['file_download_name']	= $base_name;
                    $arr_uploaded_files[$i]['file_type']			= $file_type;
                    $arr_uploaded_files[$i]['file_size']			= $arr_files['size'][$i];
                }
            }
        }

        if ($error == 1) {
            return false;
        }

        return $arr_uploaded_files;
    }

    public function download_file($fid)
    {
        $db = &Syspage::getdb();

        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $fid = intval($fid);
        if ($fid < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $rs = $db->query("SELECT * FROM tbl_files WHERE file_id = " . $db->quoteVariable($fid));
        if (!$row = $db->fetch($rs)) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!user::isWriter()) {
            $rows = $this->wallet->getReservedAmount($row['file_task_id']);

            if ($rows['res_amount']!=0) {
                $rs = $db->query('Select file_download_status from tbl_files where file_id='.$fid);
                $status = $db->fetch($rs);

                if ($status['file_download_status'] != 1) {
                    $records = new TableRecord('tbl_files');
                    $records->setFldValue('file_download_status', 1);
                    // $records->setFldValue('file_download_on', 'date('Y-m-d H:i:s')()', true);
                    $records->setFldValue('file_download_on', date('Y-m-d H:i:s'), true);

                    if (!$records->update(array('smt'=>'file_task_id = ? AND file_id = ?', 'vals'=>array($row['file_task_id'],$fid)))) {
                        $this->error = $records->getError();
                        die(convertToJson(array('status'=>'0','msg'=>$error)));
                        return false;
                    }
                }
            }
        }

        $order_details = $this->Task->getOrderDetails($row['file_task_id']);

        if ($order_details !== false && $order_details['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        //$task_details = $this->Task->getTaskDetailsById($row['file_task_id']);

        $file_real_path = CONF_INSTALLATION_PATH . 'user-uploads/' . $row['file_name'];
        if (!is_file($file_real_path)) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $fname = $row['file_download_name'];

        $file_extension = strtolower(strrchr($fname, '.'));

        switch ($file_extension) {
            case '.txt':  $ctype='application/octet-stream'; break;

            case '.pdf': $ctype='application/pdf'; break;

            case '.doc':
                $ctype = 'application/msword';
                break;

            case '.docx':

                $ctype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

                break;

            case '.xls':

            case '.xlsx':

                $ctype = 'application/vnd.ms-excel';

                break;

            case '.gif': $ctype = 'image/gif'; break;

            case '.png': $ctype = 'image/png'; break;

            case '.jpeg':

            case '.jpg': $ctype = 'image/jpg'; break;

            default: $ctype = 'application/force-download';
        }

        header("Pragma: public"); // required

        header("Expires: 0");

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        header("Cache-Control: private", false); // required for certain browsers

        header("Content-Type: $ctype");

        header("Content-Disposition: attachment; filename=\"" . $row['file_download_name']."\";");

        header("Content-Transfer-Encoding: binary");

        header("Content-Length: " . filesize($file_real_path));

        readfile($file_real_path);

        exit();
    }


    public function download_milestone()
    {
        $post = Syspage::getPostedVar();
        //die(convertToJson($post));
        $db = &Syspage::getdb();

        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!user::isWriter()) {
            $rs = $db->query('Select file_download_status from tbl_files where file_id='.$post['file_id']);
            $status = $db->fetch($rs);

            if ($status['file_download_status'] != 1) {
                $records = new TableRecord('tbl_files');
                $records->setFldValue('file_download_status', 1);
                // $records->setFldValue('file_download_on', 'date('Y-m-d H:i:s')()', true);
                $records->setFldValue('file_download_on', date('Y-m-d H:i:s'), true);

                if (!$records->update(array('smt'=>'file_task_id = ? and file_id = ?', 'vals'=>array($post['task_id'],$post['file_id'])))) {
                    $this->error = $records->getError();
                    die(convertToJson(array('status'=>'0','msg'=>$error)));
                    return false;
                }
            }
            $row = $this->wallet->getReservedAmount($post['task_id']);
            if ($row['res_amount']==0) {
                $transfer_status = true;
            } else {
                $transfer_status = false;
            }
            die(convertToJson(array('status'=>'1','data'=>$transfer_status)));

            $this->_template->render(false, false);
        }
        return true;
    }

    /* Function for bid preview for customer by bid_id */
    public function bid_preview($bid_id)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $bid_id = intval($bid_id);
        if ($bid_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_bid = $this->bid->getBidDetailsById($bid_id);
        $arr_bid['bid_preview'] = html_entity_decode($arr_bid['bid_preview']);

        if ($arr_bid === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if ($arr_bid['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $data = $this->Task->getTaskDetailsById($arr_bid['bid_task_id']);
        $files = $this->Task->getOrderFiles($arr_bid['bid_task_id'], 1);
        /* echo "<pre>".print_r($arr_bid,true)."</pre>";
        echo $bid_id;exit; */
        /* if($data['task_status']== 2 ) {
            Message::addMessage( Utilities::getLabel( 'E_Order_Already_Assigned' ) );
            if ($arr_bid['task_user_id'] == User::getLoggedUserAttribute('user_id')) {
                redirectUser('task','order_process',array($arr_bid['bid_task_id']));
            }
            redirectUser(generateUrl('task','my_orders'));
        } */

        if ($data['task_status']== 2 || $data['task_status']== 3) {
            Message::addMessage(Utilities::getLabel('E_Order_Already_Assigned'));
            if ($arr_bid['task_user_id'] == User::getLoggedUserAttribute('user_id')) {
                redirectUser(generateUrl('task', 'order_process', array($arr_bid['bid_task_id'])));
            }
            redirectUser(generateUrl('task', 'my_orders'));
        }

        $writer_activity = $this->Order->getWriterActivityStats($arr_bid['bid_user_id']);
        $writer_rating = $this->ratings->getAvgUserRating($arr_bid['bid_user_id']);

        $customer_activity_stats = $this->Order->getCustomerActivityStats($data['task_user_id']);

        $this->set('writer_completed_orders', $writer_activity['orders_completed']);
        $this->set('writer_rating', $writer_rating);
        $this->set('files', $files);
        $this->set('order', $data);
        $this->set('arr_bid', $arr_bid);
        $this->set('arr_messages', $this->messages->getConversationHistory($bid_id));
        $this->set('customer_rating', $this->ratings->getAvgUserRating($data['task_user_id']));
        $this->set('customer_orders_completed', $customer_activity_stats['orders_completed']);

        $this->_template->render();
    }

    public function order_process($task_id)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter() && !User::writerCanView()) {
            redirectUser(generateUrl('test'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $total_amount_paid = 0;

        $data = $this->Task->getOrderDetails($task_id);

        if ($data === false || ($data['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $data['task_user_id'] != User::getLoggedUserAttribute('user_id') && $data['task_editor_id'] != User::getLoggedUserAttribute('user_id'))) {
            Message::addErrorMessage(Utilities::getLabel('E_Restricted_Access'));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if ($data['task_editor_id'] == User::getLoggedUserAttribute('user_id')) {
           redirectUser(generateUrl('task', 'order_editing', array($task_id)));
        }

        $arr_milestones = $this->Task->getMilestones($task_id);

        $ongoing_milestone = end($arr_milestones);

        if ($ongoing_milestone['mile_status'] == 1 && $ongoing_milestone['mile_page'] < $data['task_pages']) {
            $tier_milestone = array('mile_page'=>(intval($ongoing_milestone['mile_page']) + 1), 'mile_words'=>0, 'mile_amount'=>0.00, 'mile_content'=>'', 'mile_status'=>0);
            array_push($arr_milestones, $tier_milestone);
            $ongoing_milestone = $tier_milestone;
        }

        if (!empty($ongoing_milestone)) {
            $ongoing_milestone['mile_content'] = html_entity_decode($ongoing_milestone['mile_content']);
        }

        $frm = $this->getMilestoneForm();

        $fld_task_id = $frm->getField('mile_task_id');
        $fld_task_id->value = $task_id;

        $fld_task_id = $frm->getField('mile_page');
        $fld_task_id->value = empty($ongoing_milestone) ? 1 : $ongoing_milestone['mile_page'];

        /* $fld_page = $frm->getField('page_num');
        $fld_page->value = empty($ongoing_milestone) ? 1 : $ongoing_milestone['mile_page']; */

        if (count($arr_milestones) > 0) {
            $frm->fill($ongoing_milestone);

            foreach ($arr_milestones as $key=>$ele) {
                $total_amount_paid += getAmountPayableToWriter($ele['mile_amount'], $data['bid_price'], $data['task_pages']);

                if (User::isWriter()) {
                    $arr_milestones[$key]['mile_amount'] = getAmountPayableToWriter($ele['mile_amount'], $data['bid_price'], $data['task_pages']);
                } else {
                    $arr_milestones[$key]['mile_amount'] = $ele['mile_amount'];
                }
            }
        }

        $files = $this->Task->getOrderFiles($task_id, 1);

        if ($data['task_status'] == 3 || $data['task_status'] == 4 || ($data['task_status'] == 2 && $data['task_pages'] == $ongoing_milestone['mile_page'] && $ongoing_milestone['mile_status'] == 1)) {
            $milestones_completed = 1;
		}
		else {
            $milestones_completed = 0;
        }

        $customer_activity_stats	= $this->Order->getCustomerActivityStats($data['task_user_id']);
        $writer_activity_stats		= $this->Order->getWriterActivityStats($data['bid_user_id']);

        $_SESSION['token_rev'] = encryptPassword(getRandomPassword(5));
        $frm_review = $this->getReviewForm($_SESSION['token_rev']);

        $fld = $frm_review->getField('task_id');
        $fld->value = $task_id;

        if (User::isWriter()) {
            $this->set('frm', $frm);
        }
        $fr =$this->Task->getOrderFiles($task_id, 2);
        //echo "<pre>".print_r($fr,true)."</pre>";exit;
        $this->set('file_uploaded', $fr);
        $this->set('files', $files);
        $this->set('order', $data);
        $this->set('arr_milestones', $arr_milestones);
        $this->set('total_amount_paid', $total_amount_paid);
        $this->set('ongoing_milestone', $ongoing_milestone);
        $this->set('milestones_completed', $milestones_completed);
        $this->set('arr_rating', $this->ratings->getTaskRating($task_id));
        $this->set('frm_review', $frm_review);
        $this->set('arr_posted_review', $this->reviews->getTaskReview($task_id));
        $this->set('arr_messages', $this->messages->getConversationHistory($data['bid_id']));
        $this->set('customer_rating', $this->ratings->getAvgUserRating($data['task_user_id']));
        $this->set('writer_rating', $this->ratings->getAvgUserRating($data['bid_user_id']));
        $this->set('customer_orders_completed', $customer_activity_stats['orders_completed']);
        $this->set('writer_orders_completed', $writer_activity_stats['orders_completed']);

        $this->_template->render();
    }
    

        public function order_editing($task_id)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter() && !User::writerCanView()) {
            redirectUser(generateUrl('test'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $total_amount_paid = 0;

        $data = $this->Task->getOrderDetails($task_id);

        if ($data === false || ($data['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $data['task_user_id'] != User::getLoggedUserAttribute('user_id') && $data['task_editor_id'] != User::getLoggedUserAttribute('user_id'))) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_milestones = $this->Task->getMilestones($task_id);

        $ongoing_milestone = end($arr_milestones);

        if ($ongoing_milestone['mile_status'] == 1 && $ongoing_milestone['mile_page'] < $data['task_pages']) {
            $tier_milestone = array('mile_page'=>(intval($ongoing_milestone['mile_page']) + 1), 'mile_words'=>0, 'mile_amount'=>0.00, 'mile_content'=>'', 'mile_status'=>0);
            array_push($arr_milestones, $tier_milestone);
            $ongoing_milestone = $tier_milestone;
        }

        if (!empty($ongoing_milestone)) {
            $ongoing_milestone['mile_content'] = html_entity_decode($ongoing_milestone['mile_content']);
        }

        $frm = $this->getMilestoneForm();

        $fld_task_id = $frm->getField('mile_task_id');
        $fld_task_id->value = $task_id;

        $fld_task_id = $frm->getField('mile_page');
        $fld_task_id->value = empty($ongoing_milestone) ? 1 : $ongoing_milestone['mile_page'];

        /* $fld_page = $frm->getField('page_num');
        $fld_page->value = empty($ongoing_milestone) ? 1 : $ongoing_milestone['mile_page']; */

        if (count($arr_milestones) > 0) {
            $frm->fill($ongoing_milestone);

            foreach ($arr_milestones as $key=>$ele) {
                $total_amount_paid += getAmountPayableToWriter($ele['mile_amount'], $data['bid_price'], $data['task_pages']);

                if (User::isWriter()) {
                    $arr_milestones[$key]['mile_amount'] = getAmountPayableToWriter($ele['mile_amount'], $data['bid_price'], $data['task_pages']);
                } else {
                    $arr_milestones[$key]['mile_amount'] = $ele['mile_amount'];
                }
            }
        }

        $files = $this->Task->getOrderFiles($task_id, 1);

        if ($data['task_status'] == 3 || $data['task_status'] == 4 || ($data['task_status'] == 2 && $data['task_pages'] == $ongoing_milestone['mile_page'] && $ongoing_milestone['mile_status'] == 1)) {
            $milestones_completed = 1;
		}
		else {
            $milestones_completed = 0;
        }

        $customer_activity_stats	= $this->Order->getCustomerActivityStats($data['task_user_id']);
        $writer_activity_stats		= $this->Order->getWriterActivityStats($data['bid_user_id']);

        $_SESSION['token_rev'] = encryptPassword(getRandomPassword(5));
        $frm_review = $this->getReviewForm($_SESSION['token_rev']);

        $fld = $frm_review->getField('task_id');
        $fld->value = $task_id;

        if (User::isWriter()) {
            $this->set('frm', $frm);
        }
        $fr =$this->Task->getOrderFiles($task_id, 2);
        //echo "<pre>".print_r($fr,true)."</pre>";exit;
        $this->set('file_uploaded', $fr);
        $this->set('files', $files);
        $this->set('order', $data);
        $this->set('arr_milestones', $arr_milestones);
        $this->set('total_amount_paid', $total_amount_paid);
        $this->set('ongoing_milestone', $ongoing_milestone);
        $this->set('milestones_completed', $milestones_completed);
        $this->set('arr_rating', $this->ratings->getTaskRating($task_id));
        $this->set('frm_review', $frm_review);
        $this->set('arr_posted_review', $this->reviews->getTaskReview($task_id));
        $this->set('arr_messages', $this->messages->getConversationHistory($data['bid_id']));
        $this->set('customer_rating', $this->ratings->getAvgUserRating($data['task_user_id']));
        $this->set('writer_rating', $this->ratings->getAvgUserRating($data['bid_user_id']));
        $this->set('customer_orders_completed', $customer_activity_stats['orders_completed']);
        $this->set('writer_orders_completed', $writer_activity_stats['orders_completed']);

        $this->_template->render();
    }


    public function order_history($task_id)
    {
        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $order_details = $this->Task->getOrderDetails($task_id);

        if ($order_details === false || ($order_details['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_user_id'] != User::getLoggedUserAttribute('user_id'))) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_milestones = $this->Task->getMilestones($task_id);

        foreach ($arr_milestones as $key=>$ele) {
            $arr_milestones[$key]['mile_content'] = html_entity_decode($ele['mile_content']);
        }

        $this->set('order_details', $order_details);
        $this->set('arr_milestones', $arr_milestones);

        $this->_template->render();
    }

    public function get_order_files()
    {
        $post = Syspage::getPostedVar();

        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $order_details = $this->Task->getOrderDetails($task_id);

        if ($order_details === false || ($order_details['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_editor_id'] != User::getLoggedUserAttribute('user_id') )) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $files = $this->Task->getOrderFiles($task_id, 2);

        die(convertToJson(array('status'=>1, 'msg'=>'', 'data'=>array('files'=>$files))));
    }

    public function add_milestone()
    {
        $post = Syspage::getPostedVar();
        //die(convertToJson(array('status'=>0, 'msg'=>$post)));
        /* Validate posted data */
        if (!User::isWriter()) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $task_id = intval($post['mile_task_id']);
        //$mile_page	= intval($post['mile_page']);

        if ($task_id < 1) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }
        /* if ($task_id < 1 || $mile_page < 1) die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel( 'E_Invalid_request' ) ))); */

        $order_details = $this->Task->getOrderDetails($task_id);

        if ($order_details === false || $order_details['task_status'] != 2 || $order_details['bid_user_id'] != User::getLoggedUserAttribute('user_id') && $order_details['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }
        /* if ($mile_page > $order_details['task_pages']) die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel( 'E_Invalid_request' ) ))); */

        $arr_milestones = $this->Task->getMilestones($task_id);

        /* if (!empty($arr_milestones)) {
            $ongoing_milestone = end($arr_milestones);
            $cur_page = $ongoing_milestone['mile_status'] == 1 ? ($ongoing_milestone['mile_page'] + 1) : $ongoing_milestone['mile_page'];
        }
        else {
            $cur_page = 1;
        }

        if ($mile_page != $cur_page) die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel( 'E_Invalid_request' ) ))); */

        $mile_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $post['mile_content']);

        if (strlen(strip_tags($post['mile_content'])) == 0) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_No_Words_Written') )));
        }


        /* --------- */

        /* $word_count = 0;

        //Calculate the number of words in the string
        $content = str_replace("\n", ' ', $post['mile_content']);
        $content = str_replace("<br>", ' ', $post['mile_content']);
        $content = str_replace("<br/>", ' ', $post['mile_content']);
        $content = str_replace("<br />", ' ', $post['mile_content']);

        $content = strip_tags($content);

        $arr_txt = explode(' ', $content);

        foreach($arr_txt as $key=>$val) {
            if (preg_match('/\p{L}/', $val)) $word_count++;
        } */

        $post['mile_words']		= getWordCount($mile_content);
        $post['mile_content']	= encodeHtmlCharacters($post['mile_content']);
        /* ------ */

        if (!$this->Task->addMilestone($post)) {
            die(convertToJson(array('status'=>0, 'msg'=>$this->Task->getError())));
        }

        die(convertToJson(array('status'=>1, 'msg'=> Utilities::getLabel('L_Content_saved') )));
    }

    private function getMilestoneForm()
    {
        //global $mbs_tinymce_settings;

        $frm = new Form('frmMilestone', 'frmMilestone');

        $frm->setExtra('class="siteForm"');
        $frm->setOnSubmit('setMilestone(); return false;');
        $frm->setTableProperties('  cellspacing="0" cellpadding="0" border="0" class="formTable editor_tbl"');
        $frm->setLeftColumnProperties(' style="text-align: left;" ');
        $frm->setJsErrorDisplay('afterfield');

        //$frm->addHiddenField('', 'mile_id', '', 'mile_id');
        $frm->addHiddenField('', 'mile_task_id', '', 'mile_task_id');
        //$frm->addHiddenField('', 'mile_words', '', 'mile_words');
        $frm->addHiddenField('', 'mile_page', '', 'mile_page');

        //$frm->addRequiredField('Content for page number', 'mile_page', '', 'mile_page', 'style="width:100px;"');
        //$frm->addHTML('Preview of complete content', 'page_num', '', true);
        $frm->addHTML(Utilities::getLabel('L_Send_Customer_Preview_Notes'), '', '');
        //$mbs_tinymce_settings['plugins'] = 'wordcount';

        $fld_prev = $frm->addHtmlEditor('', 'mile_content');
        $fld_prev->merge_caption = true;
        
         $frm->addHTML(Utilities::getLabel('L_Preview_Instructions'), '', '');
        
        $fld_btn = $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Save'), 'btn_submit');
        $fld_btn->merge_caption = true;

        return $frm;
    }
    public function getdiscountamountbyajax($dis_code=0, $customer_id=0)
	{
		if((isset($_GET['dis_code']) && !empty($_GET['dis_code'])) && (isset($_GET['customer_id']) && !empty($_GET['customer_id'])))
		{
			$dis_amount = $this->Task->getDiscountAmount($_GET['dis_code'], $_GET['customer_id']);
			echo $dis_amount;
		}
		else
		{
			echo 0;
		}
		
	}
	
	public function getdiscountamountbyajax1($dis_code, $customer_id) {
		if((isset($_GET['dis_code']) && !empty($_GET['dis_code'])) && (isset($_GET['customer_id']) && !empty($_GET['customer_id'])))
		{
			$dis_amount = $this->Task->getDiscountAmount($_GET['dis_code'], $_GET['customer_id']);
			echo $dis_amount;
		}
		else
		{
			echo 0;
		}
	}
    /* Function for add and edit task */
    public function add($tid= 0, $uid = 0)
    {
        $tid = intval($tid);
        $uid = intval($uid);

        $post = Syspage::getPostedVar();
        global $arr_page_js;
        global $arr_page_css;

        $arr_page_css[]	= 'css/jquery-ui.css';
        $arr_page_js[]	= 'js/jquery-ui.js';

        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if ($tid < 1) {
            $first_order = Common::is_first_order_done();
            if ($first_order == false) {
                redirectUser(generateUrl('task', 'my_orders'));
                return;
            }
        }

        if (User::isUserReqDeactive()) {
            Message::addErrorMessage(Utilities::getLabel('E_Can_Not_Place_New_Order_Due_To_Delete_account_Request'));
            redirectUser(generateUrl('user', 'dashboard'));
        }

		$tid = intval($tid);
		$uid = intval($uid);
        $frm = $this->getTaskForm();
        $frm->addHiddenField('', 'task_ref_id', $arr_task['task_ref_id']);
        $data = $this->Task->getTaskDetailsById($tid);
        $arr_task = array();
        if ($tid > 0 && $arr_task['task_service_type']!=6) {
            $fld_html = $frm->getfield('request');
            $frm->removeField($fld_html);

            $arr_task = $this->Task->getTaskDetailsById($tid);

            if ($arr_task === false || in_array($arr_task['task_status'], array(2,3)) || $arr_task['task_writer_id'] > 0 ) {
                die(Utilities::getLabel('E_Invalid_request'));
            }

            if ($arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
                die(Utilities::getLabel('E_Invalid_request'));
            }
            $task_service_type = $this->common->get_service_type_id($arr_task['task_service_type']);
            $arr_task['task_service_type'] = $task_service_type['service_id'];

        	$t1 = strtotime( $arr_task["task_due_date"] );
            $t2 = strtotime( $arr_task["task_posted_on"] );
            $diff = $t1 - $t2;
            $hours = $diff / ( 60 * 60 );
        
            if($hours <= 3){
                $deadlines = '3_h';
            }else if($hours <= 6){
                $deadlines = '6_h';
            }else if($hours <= 12){
                $deadlines = '12_h';
            }else if($hours <= 24){
                $deadlines = '24_h';
            }else if($hours <= 48){
                $deadlines = '2_d';
            }else if($hours <= 72){
                $deadlines = '3_d';
            }else if($hours <= 96){
                $deadlines = '4_d';
            }else if($hours <= 120){
                $deadlines = '5_d';
            }else if($hours <= 144){
                $deadlines = '6_d';
            }else if($hours <= 168){
                $deadlines = '7_d';
            }else if($hours >= 240){
                $deadlines = '10_d';
            }else{
                $deadlines = '';
            }
			
			$arr_task['task_due_date'] = $deadlines;
            $files = $this->Task->getOrderFiles($tid, 1);

            $this->set('files', $files);
        }

        if ((isset($data['task_topic']) && $data['task_topic']!= '') && $tid > 0) {
            $fld_invitees = $frm->getField('invitees');
            //$frm->removeField($fld_invitees);

            /* $fld_invitee_ids = $frm->getField('invitee_ids');
            $frm->removeField($fld_invitee_ids); */

            $fld_term_cond = $frm->getField('term_cond');
            $fld_term_cond->extra = 'checked="checked"';

// 			$fld_task_due_date = $frm->getField('task_due_date');
// 			$frm->removeField($fld_task_due_date);					

			if($data['task_status'] != 0) $frm->setOnSubmit('return confirmation();');			
        }

        if (intval($data['inv_writer_id']) > 0 || $uid > 0) {
            $fld_invitee_ids = $frm->getField('invitees');
            $frm->removeField($fld_invitee_ids);
        }

        if (User::ProfileComplete()) {
            $fld_fname = $frm->getField('user_first_name');
            $frm->removeField($fld_fname);

            $fld_lname = $frm->getField('user_last_name');
            $frm->removeField($fld_lname);

            $fld_country = $frm->getField('user_country_id');
            $frm->removeField($fld_country);

            $fld_phone = $frm->getField('user_phone');
            $frm->removeField($fld_country);
            
            $fld_password = $frm->getField('user_password');
            $frm->removeField($fld_password);
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $frm->fill($post);
        }
        if ($uid > 0) {
            $arr_task['task_type'] = 1;
        }
        $arr_task['invitee_ids'] = $uid;
        //echo "<pre>".print_r($arr_task,true)."</pre>";exit;
        if (intval($data['inv_writer_id']) > 0) {
            $uid = intval($data['inv_writer_id']);
        }
        //echo $arr_task['task_ref_id'];
        $frm->fill($arr_task);
        $this->set('frm', $frm);
        $this->set('task_id', $tid);
		$this->set('customer_id', User::getLoggedUserAttribute('user_id'));
        $this->set('task_ref_id', $arr_task['task_ref_id']);
        $this->set('invitee_id', $uid);
        $this->set('task_topic', $data['task_topic']);
        $this->set('task_instructions', $data['task_instructions']);

        $this->_template->render();
    }

    private function getTaskForm()
    {
        $service_types = $this->common->getServices();
        $frm = new Form('frmTask');
        $frm->setExtra('enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable blockrepeat"');
        $frm->captionInSameCell(false);
        $frm->setLeftColumnProperties('width="30%" align="right"');
        $frm->setAction(generateUrl('task', 'setup'));
        $frm->setJsErrorDisplay('afterfield');

        $frm->addHiddenField('', 'task_id', '', 'task_id');
        $frm->addHiddenField('', 'invitee_ids', '', 'invitee_ids');
        $frm->addRequiredField(Utilities::getLabel('L_First_name'), 'user_first_name', '', 'user_first_name');
        $frm->addRequiredField(Utilities::getLabel('L_Last_name'), 'user_last_name', '', 'user_last_name');
        $frm->addSelectBox(Utilities::getLabel('L_Country'), 'user_country_id', $this->common->getCountries(), '', '', 'Select Country', 'user_country_id')->requirements()->setRequired();
        $frm->addRequiredField(Utilities::getLabel('L_Cell_Phone'), 'user_phone', '', 'user_phone');
        $frm->addPasswordField(Utilities::getLabel('L_Password'), 'user_password', '', 'user_password')->requirements()->setRequired();
        
        $frm->addSelectBox(Utilities::getLabel('L_Type_of_paper'), 'task_paptype_id', $this->common->getPaperTypes(), '', '', Utilities::getLabel('L_Select'), 'task_paptype_id')->requirements()->setRequired();
        $frm->addRequiredField(Utilities::getLabel('L_Topic'), 'task_topic', '', 'task_topic');

        $fld_pages = $frm->addRequiredField(Utilities::getLabel('L_Pages'), 'task_pages', 2, 'task_pages', '');
        $fld_pages->requirements()->setRange(1, 999);
        
        $frm->addRequiredField(Utilities::getLabel('L_Sources_Needed'), 'task_sources', 2, 'task_sources');
		/*************************************/
		
		$frm->addTextBox('Discount code', 'task_discount_code', '', 'task_discount_code');

		$chk=$frm->addCheckBox('VIP support','task_discount_vipsupport','1','task_discount_vipsupport');
		$chk->html_after_field = '$ 4.99';
		$chkbox_html=$frm->addHTML('','abc','');
		$chkbox_html->attachField($chk);

		$chk=$frm->addCheckBox('Fulfilled by top 10 writers','task_discount_fulfilled','1','task_discount_fulfilled');
		$chk->html_after_field = '$ 9.00';
		$chkbox_html=$frm->addHTML('','abc','');
		$chkbox_html->attachField($chk);
		
		$chk=$frm->addCheckBox('Proofread by editor','task_discount_proofread','1','task_discount_proofread');
		$chk->html_after_field = '$ 5.50';
		$chkbox_html=$frm->addHTML('','abc','');
		$chkbox_html->attachField($chk);

		
		$chk=$frm->addCheckBox('Plagiarism Report','task_discount_vipservice','1','task_discount_vipservice');
		$chk->html_after_field = '$ 5.00';
		$chkbox_html=$frm->addHTML('','abc','');
		$chkbox_html->attachField($chk);

		
		$chk=$frm->addCheckBox('1-page summary of your paper','task_discount_pagesummary','1','task_discount_pagesummary');
		$chk->html_after_field = '$ 12.00';
		$chkbox_html=$frm->addHTML('','abc','');
		$chkbox_html->attachField($chk);

		
		
		
		/*************************************/

		$fld_budget = $frm->addRequiredField('Order Budget<small>(in USD)</small>', 'task_budget', 1, 'task_budget', '');
		$fld_budget->requirements()->setRange(1,10000); 
// 		$db = &Syspage::getdb();
// 			$t = $db->fetch($db->query("INSERT INTO tbl_tasks ('autodebit_on','task_revise_request_date')
// VALUES (NOW(),NOW())"));
// 			die('----'.displayDate(date('Y-m-d H:i:s'),true, true, CONF_TIMEZONE));

			$fld_deadline = $frm->addSelectBox(Utilities::getLabel( 'L_Deadline' ),'task_due_date',array('3_h'=>'3 hours','4_h'=>'4 hours','5_h'=>'5 hours','6_h'=>'6 hours','8_h'=>'8 hours','10_h'=>'10 hours','12_h'=>'12 hours','14_h'=>'14 hours','16_h'=>'16 hours','18_h'=>'18 hours','20_h'=>'20 hours','24_h'=>'1 Day','2_d'=>'2 Days','3_d'=>'3 Days','4_d'=>'4 Days','5_d'=>'5 Days','6_d'=>'6 Days','7_d'=>'7 Days','8_d'=>'8 Days','10_d'=>'10 Days','12_d'=>'12 Days','14_d'=>'14 Days','20_d'=>'20 Days'),'','',Utilities::getLabel( 'L_Choose_Deadline' )); 

// // 		$tomorrow = new DateTime('+3 hours');
// 		$tomorrow = new DateTime('+3 hours', new DateTimeZone("UTC"));
// // $tomorrow = displayDate(date('Y-m-d H:i:s'),true, true, CONF_TIMEZONE);

//         // $tomorrow = $tomorrow->modify("+180 minutes"); 
// 		$end = new DateTime('9999-12-31 23:59:59');
// 		$fld_deadline = $frm->addDateTimeField('Deadline','task_due_date','Choose Deadline', '', ' readonly class="task_due_date_cal"');
// 		$fld_deadline->requirements()->setRequired(); 	
// 		//$fld_deadline->requirements()->setRange(strtotime($tomorrow->format(CONF_DATE_FORMAT_PHP .' H:i:s')),strtotime($end->format(CONF_DATE_FORMAT_PHP .' H:i:s')));
// 		$fld_deadline->requirements()->setRange($tomorrow->format('d/m/Y H:i:s'),$end->format('d/m/Y H:i:s'));
// 		$fld_deadline->requirements()->setCustomErrorMessage('Deadline date should be greater than 3 hours.');
// 		$fld_deadline->html_after_field = '<br><strong>In Timezone ' . get_time_zone_abbr() . ' Now: '.displayDate(date('Y-m-d H:i:s'),true, true, CONF_TIMEZONE) . '</strong>';		

        $frm->addHtml('', '', '<h2 class="bordertitle" align="left">' . Utilities::getLabel('L_Additional_Information') . '</h2>', true);

        $fld = $frm->addSelectBox(Utilities::getLabel('L_Discipline'), 'task_discipline_id', $this->common->getDisciplines(), '', '', Utilities::getLabel('L_Select'), 'task_discipline_id');
        $fld->requirements()->setRequired();
        //$fld->options ="";
        $service = $frm->addSelectBox(Utilities::getLabel('L_Type_of_service'), 'task_service_type', $service_types, '', '', Utilities::getLabel('L_Select'), 'task_service_type');
        $service->requirements()->setRequired();

        

        $cite = $frm->addSelectBox(Utilities::getLabel('L_Format_or_citation_style'), 'task_citstyle_id', $this->common->getCitationStyles(), '', '', Utilities::getLabel('L_Select'), 'task_citstyle_id');
        $cite->requirements()->setRequired();


        $ins = $frm->addTextArea(Utilities::getLabel('L_Paper_instructions'), 'task_instructions', '', 'task_instructions');
        $ins->requirements()->setRequired();
        $fld_files = $frm->addFileUpload(Utilities::getLabel('L_Upload_additional_material'), 'task_files[]', 'task_file');


        $frm->addHtml('', 'request', '<h2 class="bordertitle" align="left">' . Utilities::getLabel('L_Request_a_specific_writer') . '</h2>', true);

        $fld_invitees = $frm->addTextBox(Utilities::getLabel('L_Invite_writer'), 'invitees', '', 'invitees', 'class="ui-autocomplete-input" autocomplete="off"');
        $fld_invitees->html_before_field = '<div class="ui-widget">';
        $fld_invitees->html_after_field = '</div><ul class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all" id="ui-id-1" tabindex="0" style="display: none;"></ul>';

        $chk=$frm->addCheckBox(Utilities::getLabel('L_Term'), 'term_cond', '1', 'term_cond');
        $chk->Requirements()->setRequired();
        $chk->html_after_field = sprintf(Utilities::getLabel('L_I_agree_to_Term_&_Conditions'), generateUrl('cms', 'terms-conditions'));
        $chkbox_html=$frm->addHTML('', 'abc', '');
        $chkbox_html->attachField($chk);

        $fld_save	= $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Publish_for_writers'), '', ' class="greenBtn" ');

        //$fld_save->html_after_field = '<br><br><i>By clicking on "Publish for writers" button you are agreeing to our <a href="' . generateUrl('cms', 'terms_conditions') . '" target="_blank">Terms and Conditions</a></i>';
        return $frm;
    }
    

    private function getReviewForm($token)
    {
        $frm = new Form('frmReview', 'frmReview');

        $frm->setExtra('class="siteForm"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0"');
        $frm->setAction(generateUrl('reviews', 'setup'));
        $frm->setJsErrorDisplay('afterfield');

        $frm->addHiddenField('', 'task_id', '', '');
        $frm->addHiddenField('', 'user_id', '', '');
        $frm->addhiddenfield('', 'token', $token);
        $frm->addRadioButtons(Utilities::getLabel('L_Delivery'), 'delivery', array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>''), '1', 5, 'width="" ', 'class="star"');
        $frm->addRadioButtons(Utilities::getLabel('L_Communication'), 'communication', array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>''), '1', 5, 'width="" ', 'class="star"');
        $frm->addRadioButtons(Utilities::getLabel('L_Quality'), 'quality', array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>''), '1', 5, 'width="" ', 'class="star"');
        //$fld->html_after_field='<span class="star-rating-control"></span>';

        $frm->addHTML('', '', '<h5>' . Utilities::getLabel('L_Write_a_Review') . '</h5>', true);

        $fld_text = $frm->addTextArea('', 'comments', '', 'comments', 'maxlength="300" onkeyup="limitTextCharacters(this,300);"');
        $fld_text->html_after_field = '<br /><span class="caption"><label id="comments_counter">0</label> / ' . Utilities::getLabel('L_300_characters_max') . '</span>';
        $fld_text->requirements()->setRequired();
        $fld_text->merge_caption = true;
        $fld_text->requirements()->setCustomErrorMessage(Utilities::getLabel('L_No_message_written'));

        $fld_btn = $frm->addSubmitButton('', 'btn_post_review', Utilities::getLabel('L_Post'), 'btn_post_review', 'class="theme-btn"');
        $fld_btn->merge_caption = true;

        return $frm;
    }

    /* Form Function for searching new orders */
    private function getSearchForm($data, $page)
    {
        $frm = new Form('frmordersSearch');
        $frm->setAction('orders');
        //$frm->setExtra('class="browseOrderForm"');
        $frm->captionInSameCell(true);
        for ($i=1;$i <= $data['pages']; $i++) {
            $pages[$i] = $i;
        }
        $pap = $this->common->getPaperTypes();
        $frm->addTextBox(Utilities::getLabel('L_filter_By_refid'), 'filter_refid', '', 'filter_refid', 'class="inputbox"');
        $frm->addTextBox(Utilities::getLabel('L_filter_By_Pages'), 'filter_page', '', 'filter_page', 'class="inputbox"');
        //$frm->addSelectBox('Filter By Deadline', 'filter_date', '', 'filter_date');
        $date_formats = array(
                            '3_h'=> Utilities::getLabel('L_3_hours'),
                            '6_h'=> Utilities::getLabel('L_6_hours'),
                            '11_n'=> Utilities::getLabel('L_Next_day_by_11am'),
                            '1_d'=> Utilities::getLabel('L_Next_Day'),
                            '2_d'=> Utilities::getLabel('L_2_Days'),
                            '3_d'=> Utilities::getLabel('L_3_Days'),
                            '4_d'=> Utilities::getLabel('L_4_Days'),
                            '5_d'=> Utilities::getLabel('L_5_Days'),
                            '7_d'=> Utilities::getLabel('L_7_Days'),
                            '14_d'=> Utilities::getLabel('L_14_Days'),
                            '21_d'=> Utilities::getLabel('L_21_Days'),
                            '28_d'=> Utilities::getLabel('L_28_Days')
                        );
        $fld_deadline = $frm->addSelectBox(Utilities::getLabel('L_Filter_By_Deadline'), 'filter_date', $date_formats, 'filter_date', 'class="inputbox"');

        $frm->addSelectBox(Utilities::getLabel('L_Type_of_Papers'), 'paptype_name', $pap, 'paptype_name', 'class="inputbox"');

        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Search'), '', 'class="theme-btn"');
        //$frm->setOnSubmit('searchUser(this);return false;');
        return $frm;
    }

    /* Function to list orders for writers */
    public function orders()
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isWriter()) {
            redirectUser(generateUrl('page', 'error_404'));
        }/* die( Utilities::getLabel( 'E_Invalid_request' ) ); */
        if (!User::writerCanView()) {
            redirectUser(generateUrl('test'));
            return;
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $user_details = $this->user->getUserDetailsById(User::getLoggedUserAttribute('user_id'));
        if ($user_details['user_photo'] == '') {
            Message::addErrorMessage(Utilities::getLabel('Update_Your_Profile_Photo'));
            redirectUser(generateUrl('user', 'update_profile'));
        }
        $post = Syspage::getPostedVar();
        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }

        //$arr_orders = $this->Task->ShowOrders($page);
        $data = $post;
        $arr_orders = $this->Task->OrdersListing($data, $page);
        //echo "<pre>".print_r($arr_orders,true)."</pre>";

        $frm = $this->getSearchForm($arr_orders, $page);
        $frm->fill($post);
        $this->set('frm', $frm);
        $this->set('arr_orders', $arr_orders);

        $this->_template->render();
    }

    /* Function to list editing orders for editors */
    public function editing()
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isEditor()) {
            Message::addErrorMessage(Utilities::getLabel('E_Restricted_Access'));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if (!User::writerCanView()) {
            redirectUser(generateUrl('test'));
            return;
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();
        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }

        //$arr_orders = $this->Task->ShowOrders($page);
        $data = $post;
        $arr_orders = $this->Task->EditingOrdersListing($data, $page);
        //echo "<pre>".print_r($arr_orders,true)."</pre>";

        $frm = $this->getSearchForm($arr_orders, $page);
        $frm->fill($post);
        $this->set('frm', $frm);
        $this->set('arr_orders', $arr_orders);

        $this->_template->render();
    }
    
    /* Function for rejection of bid by Customer */
    public function reject_writer($bid_id)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $bid_id = intval($bid_id);
        if ($bid_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_bid = $this->bid->getBidDetailsById($bid_id);
        $arr_task = $this->Task->getTaskDetailsById($arr_bid['bid_task_id']);

        if ($arr_bid === false || $arr_bid['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->bid->reject_bid($bid_id)) {
            Message::addError($this->bid->getError());
            redirectUser(generateUrl('task', 'bid_preview', array($bid_id)));
            return false;
        }

        $this->Task->sendEmail(array('to'=>$arr_bid['writer_email'],'temp_num'=>68,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>$arr_bid['writer'],'user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));


        Message::addMessage(Utilities::getLabel('S_Bid_successfully_rejected'));
        redirectUser(generateUrl('task', 'order_bids', array($arr_bid['bid_task_id'])));
        return true;
    }

    public function assign_writer()
    {
        $post = Syspage::getPostedVar();

        if (!User::isUserLogged()) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Must_Logged_In') )));
        }

        if (User::isWriter()) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $bid_id = intval($post['bid_id']);
        if ($bid_id < 1) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $arr_bid = $this->bid->getBidDetailsById($bid_id);
        if ($arr_bid === false || $arr_bid['bid_status'] != 0 || $arr_bid['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $task_amount		= getOrderAmount($arr_bid['bid_price'], $arr_bid['task_pages']);
        $available_amount	= $this->wallet->getAvailableBalance();

        if ($available_amount < $task_amount) {
            die(convertToJson(array('status'=>2, 'msg'=>'Insufficient amount in wallet. Max amount available for use ' . priceFormat($available_amount, 2) . '.')));
        }

        if (!$this->Task->assignWriter(array('task_id'=>$arr_bid['bid_task_id'],'task_ref_id'=>$arr_bid['task_ref_id'], 'bid_id'=>$bid_id, 'writer_id'=>$arr_bid['bid_user_id'], 'task_amount'=>$task_amount,'task_due_date'=>$arr_bid['task_due_date']))) {
            die(convertToJson(array('status'=>0, 'msg'=>$this->Task->getError())));
        }
        $arr_task = $this->Task->getTaskDetailsById($arr_bid['bid_task_id']);
        if ($arr_task['task_order_type'] == 1) {
            //$this->Task->mark_as_finished($arr_bid['bid_task_id']);
            //if (!$this->Task->changeOrderStatus($task_id, 3)) {
            //Message::addErrorMessage($this->Task->getError());
            if (!$this->Task->changeOrderStatus($arr_bid['bid_task_id'], 3)) {
            Message::addErrorMessage($this->Task->getError());
            }
        }
        
        die(convertToJson(array('status'=>1, 'msg'=>'', 'data'=>array('task_id'=>$arr_bid['bid_task_id']))));
    }

    public function assign_task($bid_id)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        if (isset($_SESSION['e_f_pp_return']['refrer_url'])) {
            unset($_SESSION['e_f_pp_return']['refrer_url']);
        }

        $bid_id = intval($bid_id);
        if ($bid_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_bid = $this->bid->getBidDetailsById($bid_id);
        if ($arr_bid === false || $arr_bid['bid_status'] != 0 || $arr_bid['task_status'] != 1 || $arr_bid['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $msg = confirm_payment_loaded($_SERVER['REQUEST_URI']);
        if ($msg !== false) {
            Message::addMessage($msg);
        }

        $writer_rating = $this->ratings->getAvgUserRating($arr_bid['bid_user_id']);
        $writer_activity_stats = $this->Order->getWriterActivityStats($arr_bid['bid_user_id']);

        $db = &Syspage::getdb();
        $srch = new SearchBase('tbl_infotips');
        $srch->addCondition('infotip_id', '=', 1);
        $srch->addFld('infotip_description');
        $rs = $srch->getResultSet();
        $this->set('arr_tip', $db->fetch($rs));

        $this->set('writer_completed_orders', $writer_activity_stats['orders_completed']);
        $this->set('writer_rating', $writer_rating);
        $this->set('arr_bid', $arr_bid);
        $this->set('frm', $this->transactionsController->getLoadWalletForm($arr_bid['bid_task_id'], $arr_bid['bid_price'], $bid_id));

        /* @DD Start */
        $this->set('availableWalletAmount', $this->wallet->getAvailableBalance());
        $this->set('walletLimit', CONF_WALLET_LIMIT);
        $this->set('serviceCharges', CONF_SERVICE_CHARGE);
        /* @DD End */

        $this->_template->render();
    }
    
    public function buy_paper($bid_id)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        if (isset($_SESSION['e_f_pp_return']['refrer_url'])) {
            unset($_SESSION['e_f_pp_return']['refrer_url']);
        }

        $bid_id = intval($bid_id);
        if ($bid_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_bid = $this->bid->getBidDetailsById($bid_id);
        if ($arr_bid === false || $arr_bid['bid_status'] != 0 || $arr_bid['task_status'] != 1 || $arr_bid['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $msg = confirm_payment_loaded($_SERVER['REQUEST_URI']);
        if ($msg !== false) {
            Message::addMessage($msg);
        }

        $writer_rating = $this->ratings->getAvgUserRating($arr_bid['bid_user_id']);
        $writer_activity_stats = $this->Order->getWriterActivityStats($arr_bid['bid_user_id']);

        $db = &Syspage::getdb();
        $srch = new SearchBase('tbl_infotips');
        $srch->addCondition('infotip_id', '=', 1);
        $srch->addFld('infotip_description');
        $rs = $srch->getResultSet();
        $this->set('arr_tip', $db->fetch($rs));

        $this->set('writer_completed_orders', $writer_activity_stats['orders_completed']);
        $this->set('writer_rating', $writer_rating);
        $this->set('arr_bid', $arr_bid);
        $this->set('frm', $this->transactionsController->getLoadWalletForm($arr_bid['bid_task_id'], $arr_bid['bid_price'], $bid_id));

        /* @DD Start */
        $this->set('availableWalletAmount', $this->wallet->getAvailableBalance());
        $this->set('walletLimit', CONF_WALLET_LIMIT);
        $this->set('serviceCharges', CONF_SERVICE_CHARGE);
        /* @DD End */

        $this->_template->render();
    }
 

    /* private function getAssignTaskForm() {
        if (!User::isUserLogged() || User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );

        $frm = new Form('frmcheckout');

        $frm->setOnSubmit("if(!confirm('Are you sure you want to proceed?')) {return false;}");
        $frm->setExtra('class="siteForm"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        $frm->setLeftColumnProperties('width=20%');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setAction(generateUrl('task', 'assign_writer'));

        $frm->addSubmitButton('', 'btn_submit', 'Reserve Money & Start Work Process', 'btn_submit');

        return $frm;
    } */

    public function uploadSetup()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();

        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id') && $arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }


        //$frm = $this->getAdditionalForm();

        /** upload additional material **/
        $arr_uploaded_files = $this->upload_files($_FILES['task_files']);

        if ($arr_uploaded_files === false) {
            redirectUser($_SERVER['HTTP_REFERER']);
        }

        if (!empty($arr_uploaded_files)) {
            foreach ($arr_uploaded_files as $ele) {
                if (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name'])) {
                    if (!$this->Task->addFile(array('file_task_id'=>$task_id, 'file_name'=>$ele['file_name'], 'file_download_name'=>$ele['file_download_name'], 'file_type'=>$ele['file_type'],'file_size'=>$ele['file_size'], 'file_class'=>1))) {
                        unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $ele['file_name']);
                        Message::addErrorMessage($this->Task->getError());
                    }
                }
            }
        }
        /* ####### */

        /* $num_files_uploaded = count($_FILES['task_files']['name']);

        if ($num_files_uploaded > 0) {
            $arr_valid_text_ext		= array('txt', 'pdf', 'doc', 'docx', 'xls');
            $arr_valid_image_ext	= array('jpg', 'jpeg', 'png', 'gif');


            for ($i=0; $i<$num_files_uploaded; $i++) {
                $file_size += $_FILES['task_files']['size'][$i];
                if($file_size > 32086425 ) {
                    Message::addErrorMessage('Could not upload above 30MB.');
                    redirectUser($_SERVER['HTTP_REFERER']);
                }
            }

            for ($i=0; $i<$num_files_uploaded; $i++) {
                if (is_uploaded_file($_FILES['task_files']['tmp_name'][$i])) {
                    $path_parts = pathinfo($_FILES['task_files']['name'][$i]);

                    $file_ext		= strtolower($path_parts['extension']);
                    $base_name		= $path_parts['basename'];

                    if (!in_array($file_ext,$arr_valid_text_ext) && !in_array($file_ext,$arr_valid_image_ext)) {
                        Message::addErrorMessage('Could not upload ' . $base_name . '. Invalid file format.');
                        redirectUser($_SERVER['HTTP_REFERER']);
                    }

                    if ($_FILES['task_files']['size'][$i] > 10695475) {				//restrict file size to 10MB
                        Message::addErrorMessage('Could not upload ' . $base_name . '. File size too big, max allowed size 10MB.');
                        redirectUser($_SERVER['HTTP_REFERER']);
                    }

                    if (in_array($file_ext,$arr_valid_image_ext)) {
                        $file_type = 2;

                        if (!saveImage($_FILES['task_files']['tmp_name'][$i], $_FILES['task_files']['name'][$i], $saved_file_name)){
                            Message::addErrorMessage($saved_file_name);
                            redirectUser($_SERVER['HTTP_REFERER']);
                        }
                    }
                    elseif(in_array($file_ext,$arr_valid_text_ext)) {
                        $file_type = 1;

                        if (!saveFile($_FILES['task_files']['tmp_name'][$i], $_FILES['task_files']['name'][$i], $saved_file_name)){
                            Message::addErrorMessage($saved_file_name);
                            redirectUser($_SERVER['HTTP_REFERER']);
                        }
                    }
                    else {
                        Message::addErrorMessage('Could not upload ' . $base_name . '. Invalid file format.');
                        redirectUser($_SERVER['HTTP_REFERER']);
                    }

                    if (!$this->Task->addFile(array('file_name'=>$saved_file_name, 'file_download_name'=>$base_name, 'file_class'=>1, 'file_type'=>$file_type , 'file_task_id'=>$task_id,'task_status'=>$arr_task['task_status'],'user_ref_id'=>$arr_task['customer_ref_id'],'task_ref_id'=>$arr_task['task_ref_id'],'file_size'=>$_FILES['task_files']['size'][$i]))) {
                        unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $saved_file_name);
                        Message::addErrorMessage($this->Task->getError());
                    }
                }else {
                    Message::addErrorMessage('No File(s) uploaded.');
                    redirectUser($_SERVER['HTTP_REFERER']);
                }
            }
        } */

        if ($arr_task['task_status'] == 2) {
            $this->Task->sendEmail(array('to'=>$arr_task['writer_email'],'temp_num'=>36,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>$arr_task['writer'],'user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));
            $this->Task->sendEmail(array('to'=>CONF_ADMIN_EMAIL_ID,'temp_num'=>36,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>'Administrator','user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));
        }
        
        if ($arr_task['task_status'] == 2 && $arr_task['task_editor_id'] > 0) {
            $this->Task->sendEmail(array('to'=>$arr_task['editor_email'],'temp_num'=>36,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>$arr_task['editor'],'user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));
        }

        if ($arr_task['task_status'] == 1) {
            $arr_bids = $this->bid->getOrderBids($task_id);

            foreach ($arr_bids as $bids) {
                $this->Task->sendEmail(array('to'=>$bids['user_email'],'temp_num'=>36,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>$bids['writer'],'user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));
            }
            $this->Task->sendEmail(array('to'=>CONF_ADMIN_EMAIL_ID,'temp_num'=>36,'user_ref_id'=>$arr_task['customer_ref_id'],'user_screen_name'=>'Administrator','user_first_name'=>$arr_task['customer_first_name'],'order_ref_id'=>$arr_task['task_ref_id']));
        }

        Message::addMessage(Utilities::getLabel('S_File_Uploaded_Successfully'));

        redirectUser($_SERVER['HTTP_REFERER']);
    }

    /* Function to upload additional material by customer on particular task */
    public function upload_additional($tid)
    {
        $post = Syspage::getPostedVar();

        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Must_Logged_In'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $tid = intval($tid);

        $arr_task = $this->Task->getTaskDetailsById($tid);
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id') &&  $arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getAdditionalForm();

        $frm->addHiddenField('', 'task_id', $tid);

        $this->set('frm', $frm);
        $this->set('task_id', $tid);

        $this->_template->render(false, false);
    }

    private function getAdditionalForm()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        $frm = new Form('frmadditionalupload');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        //$frm->setLeftColumnProperties('width="40%"');
        $frm->setAction(generateUrl('task', 'uploadSetup'));
        $frm->setJsErrorDisplay('afterfield');

        $fld_files = $frm->addFileUpload('', 'task_files[]', 'task_file', 'multiple="multiple" class="input-filetype"');
        $fld_files->requirements()->setRequired();
        $fld_files->requirements()->setCustomErrorMessage(Utilities::getLabel('L_No_files_selected'));

        $fld_files->setRequiredStarWith('none');

        $frm->addHTML('', '', '<div class="gap"></div>');

        $frm->addSubmitButton('', 'submit', Utilities::getLabel('L_Upload'));

        return $frm;
    }

    /* Function to Extend deadline for Task. */
    public function extend_deadline($task_id)
    {
        $post = Syspage::getPostedVar();

        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Must_Logged_In'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($task_id);

        $arr_task = $this->Task->getTaskDetailsById($task_id);
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getExtendForm();
        $frm->addHiddenField('', 'task_id', $task_id);
        $frm->addSubmitButton('', 'submit', Utilities::getLabel('L_Update'), ''/* ,'onclick="checkDate('.$task_id.');return false;"' */);
        $this->set('frm', $frm);
        $this->set('arr_task', $arr_task);

        $this->_template->render(false, false);
    }

    private function getExtendForm()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        $frm = new Form('frmextenddeadline');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);

        $frm->setOnSubmit('checkDate(this);return false;');
        $frm->setJsErrorDisplay('afterfield');

        /* $fld_deadline = $frm->addSelectBox('Deadline','task_due_date',array('3_h'=>'3 hours','6_h'=>'6 hours','12_h'=>'12 hours','24_h'=>'1 Day','2_d'=>'2 Days','3_d'=>'3 Days','4_d'=>'4 Days','5_d'=>'5 Days','6_d'=>'6 Days','7_d'=>'7 Days','10_d'=>'10 Days','14_d'=>'14 Days','21_d'=>'21 Days','28_d'=>'28 Days'),'','','Select to add','task_due_date');  */
        $options = array(
                        '30_m'=> Utilities::getLabel('L_30_Minutes'),
                        '1_h'=> Utilities::getLabel('L_1_hour'),
                        '2_h'=> Utilities::getLabel('L_2_hours'),
                        '4_h'=> Utilities::getLabel('L_4_hours'),
                        '6_h'=> Utilities::getLabel('L_6_hours'),
                        '8_h'=> Utilities::getLabel('L_8_hours'),
                        '12_h'=> Utilities::getLabel('L_12_hours'),
                        '14_h'=> Utilities::getLabel('L_14_hours'),
                        '16_h'=> Utilities::getLabel('L_16_hours'),
                        '18_h'=> Utilities::getLabel('L_18_hours'),
                        '24_h'=> Utilities::getLabel('L_24_hours'),
                        '2_d'=> Utilities::getLabel('L_2_Days'),
                        '3_d'=> Utilities::getLabel('L_3_Days'),
                        '4_d'=> Utilities::getLabel('L_4_Days'),
                        '5_d'=> Utilities::getLabel('L_5_Days'),
                        '6_d'=> Utilities::getLabel('L_6_Days'),
                    );
        $fld_deadline = $frm->addSelectBox(Utilities::getLabel('L_Deadline'), 'task_due_date', $options, '', '', Utilities::getLabel('L_Select_to_add'), 'task_due_date');

        $frm->addHTML('', '', '<div class="gap"></div>');



        return $frm;
    }

    public function extendsetup()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Must_Logged_In'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();
        $task_id=intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getExtendForm();

		$deadlineExpired = (time() > strtotime($arr_task['task_due_date'])) ? true : false;
        $task_due_date = array();
        $task_due_date = explode('_', $post['task_due_date']);
        $data = $post;
        switch ($task_due_date[1]) {
            case 'm':
			    $data['task_due_date'] = ($deadlineExpired)? strtotime('+'.$task_due_date[0].' minutes',time()) : strtotime('+'.$task_due_date[0].' minutes',strtotime($arr_task['task_due_date']));
				// $data['task_due_date'] = strtotime('+'.$task_due_date[0].' minutes',strtotime($arr_task['task_due_date']));
                $data['task_due_date'] = date('Y-m-d H:i:s', $data['task_due_date']);
                $deadline_extention  = $task_due_date[0].' ' . Utilities::getLabel('L_minutes');
                break;
            case 'h':
			    $data['task_due_date'] = ($deadlineExpired)? strtotime('+'.$task_due_date[0].' hours',time()) : strtotime('+'.$task_due_date[0].' hours',strtotime($arr_task['task_due_date']));
				// $data['task_due_date'] = strtotime('+'.$task_due_date[0].' hours',strtotime($arr_task['task_due_date']));
                $data['task_due_date'] = date('Y-m-d H:i:s', $data['task_due_date']);
                $deadline_extention  = $task_due_date[0].' ' . Utilities::getLabel('L_hours') ;
                break;
            case 'n':
			 //   $data['task_due_date'] = ($deadlineExpired)? date('Y-m-d H:i:s'.$task_due_date[0].':00',time()); : date('Y-m-d H:i:s'.$task_due_date[0].':00',strtotime('tomorrow'));
                $data['task_due_date'] = date('Y-m-d H:i:s'.$task_due_date[0].':00', strtotime('tomorrow'));
                $deadline_extention  = $task_due_date[0].' ' . Utilities::getLabel('L_hours');
                break;
            case 'd':
			    $data['task_due_date'] = ($deadlineExpired)? strtotime('+'.$task_due_date[0].' days',time()) : strtotime('+'.$task_due_date[0].' days',strtotime($arr_task['task_due_date']));
				// $data['task_due_date'] = strtotime('+'.$task_due_date[0].' days',strtotime($arr_task['task_due_date']));
                $data['task_due_date'] = date('Y-m-d H:i:s', $data['task_due_date']);
                $deadline_extention  = $task_due_date[0].' ' . Utilities::getLabel('L_days');
                break;
            default:
                $data['task_due_date'] = strtotime('+7 days', time());
                $data['task_due_date'] = date('Y-m-d H:i:s', $data['task_due_date']);
                $deadline_extention  = '7 ' . Utilities::getLabel('L_days');
                break;
        }


		echo $data['task_due_date'];
        //$post['task_due_date'] = $post['task_due_date'] . ' ' . intval($post['task_time']) . ':00:00';
        if (strtotime($data[ 'task_due_date' ]) <= time()) {
            //Message::addErrorMessage('Please Select Future Date');
            die(convertToJson(array( 'status' => 0, 'msg' => Utilities::getLabel('L_Please_enter_future_date') )));
            return false;
        }
        if (!$this->Task->updateDeadline($data)) {
            die(convertToJson(array('status'=>0, 'msg'=>$this->Task->getError())));
        //Message::addErrorMessage($this->Task->getError());
		}
		else {
            $this->Task->sendEmail(array('to'=>$arr_task['customer_email'],'temp_num'=>30,'order_ref_id'=>$arr_task['customer_ref_id'],'user_email'=>$arr_task['customer_email'],'user_first_name'=>$arr_task['customer_first_name'],'deadline_extension'=>$deadline_extention,'new_deadline'=>date('Y-m-d H:i:s', strtotime($data['task_due_date']))));
            $this->Task->sendEmail(array('to'=>CONF_ADMIN_EMAIL_ID,'temp_num'=>29,'user_screen_name'=>'Administrator','order_ref_id'=>$arr_task['task_ref_id'],'deadline_extension'=>$deadline_extention,'new_deadline'=>date('Y-m-d H:i:s', strtotime($data['task_due_date']))));
            if ($arr_task{'task_status'} != 2) {
                $bi = $this->bid->getOrderBids($task_id);

                foreach ($bi as $key=>$value) {
                    Task::sendEmail(array('temp_num'=>29,'to'=>$value['user_email'],'user_screen_name'=>$value['user_screen_name'],'order_ref_id'=>$arr_task['task_ref_id'],'deadline_extension'=>$deadline_extention,'new_deadline'=>date('Y-m-d H:i:s', strtotime($data['task_due_date']))));
                }
            } else {
                Task::sendEmail(array('temp_num'=>29,'to'=>$arr_task['writer_email'],'user_screen_name'=>$value['user_screen_name'],'order_ref_id'=>$arr_task['task_ref_id'],'deadline_extension'=>$deadline_extention,'new_deadline'=>date('Y-m-d H:i:s', strtotime($data['task_due_date']))));
            }
            die(convertToJson(array( 'status' => 1, 'msg' => Utilities::getLabel('L_Deadline_extended_successfully') )));
            //Message::addMessage('Deadline extended successfully.');
        }
        return false;
        //redirectUser($_SERVER['HTTP_REFERER']);
    }

    public function mark_as_finished($task_id = 0)
    {
        if (!User::isUserLogged()) {
            redirectUser('user', 'signin');
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        if (!$this->Task->changeOrderStatus($task_id, 3)) {
            Message::addErrorMessage($this->Task->getError());
        }

        redirectUser($_SERVER['HTTP_REFERER']);
    }

    /* Function to cancel task by customer */
    public function cancel_order($tid)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        $tid = intval($tid);
        if ($tid < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($tid);

        //echo "<pre>".print_r($arr_task,true)."</pre>";exit;
        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if ($arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if ($arr_task['task_finished_from_writer'] == 1 || !in_array($arr_task['task_status'], array(0, 1, 2))) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if ($this->Task->cancel_order($tid, User::getLoggedUserAttribute('user_id'), $arr_task['task_ref_id'])) {

            if (!$this->Task->refundUserForPaidOrder($tid, User::getLoggedUserAttribute('user_id'), $arr_task['task_ref_id'])) {
                die($this->Task->getError());
            }

            $this->Task->send_notification_customer($arr_task);

            $this->Task->sendEmail(array('to'=>CONF_ADMIN_EMAIL_ID, 'temp_num'=>16, 'website_url'=>$_SERVER['SERVER_NAME'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_first_name'=>$arr_task['customer_first_name'], 'user_screen_name'=>'Administrator'));

            if ($arr_task['task_status'] != 2) {
                $bi = $this->bid->getOrderBids($tid);
                foreach ($bi as $val) {
                    $this->Task->sendEmail(array('to'=>$val['user_email'], 'temp_num'=>16, 'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$val['writer'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_ref_id'=>$arr_task['customer_ref_id'], 'user_first_name'=>$arr_task['customer_first_name']));
                }
            } else {
                $this->Task->sendEmail(array('to'=>$arr_task['writer_email'], 'temp_num'=>16, 'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$arr_task['writer'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_first_name'=>$arr_task['customer_first_name']));
            }
		}
		else {
            Message::addError($this->Task->getError());
        }

        Message::addMessage(Utilities::getLabel('S_Order_Cancelled_Please_Check_Inbox'));

        redirectUser(generateUrl('task', 'my_orders'));
    }
    
    

    /* Function to request cancel task by writer */
    public function cancel_order_request($tid)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        //if (User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $tid = intval($tid);
        if ($tid < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($tid);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //
        if ($arr_task['task_writer_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getCancelOrderReqFrm();
        $frm->addHiddenField('', 'task_id', $task_id);

        $this->set('frm', $frm);
        $this->set('arr_task', $arr_task);

        $this->_template->render(false, false);
    }
    
    /* Function to assign order to editor */
    public function take_editing_order($tid)
    {
        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        $tid = intval($tid);
        if ($tid < 1 ) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($tid);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!User::isEditor()) {
            die(Utilities::getLabel('E_You_Are_Not_An_Editor'));
            redirectUser(generateUrl('user', 'dashboard'));
        }

        if ($arr_task['task_edited'] == 1 ) {
            die(Utilities::getLabel('E_Order_Already_Edited'));
            redirectUser(generateUrl('task', 'editing'));
        }
        if ($arr_task['task_editor_id'] > 0 && $arr_task['task_editor_id']  != User::getLoggedUserAttribute('user_id')) {
            die( Utilities::getLabel( 'E_Order_Assigned_To_Other_Editor' ));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if ($arr_task['task_writer_id'] == User::getLoggedUserAttribute('user_id')) {
            die( Utilities::getLabel( 'E_You_Cannot_Edit_Own_Tasks' ));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if (in_array($arr_task['task_status'], array(0, 1, 3,4)) ) {
            die(Utilities::getLabel('E_Invalid_request'));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if ($this->Task->take_editing_order($tid, User::getLoggedUserAttribute('user_id'), $arr_task['task_ref_id'])) {


            $this->Task->sendEmail(array('to'=>CONF_ADMIN_EMAIL_ID, 'temp_num'=>78, 'website_url'=>$_SERVER['SERVER_NAME'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_first_name'=>$arr_task['customer_first_name'], 'user_screen_name'=>'Administrator'));

            if ($arr_task['task_status'] != 2) {
                $bi = $this->bid->getOrderBids($tid);
                foreach ($bi as $val) {
                    $this->Task->sendEmail(array('to'=>$val['user_email'], 'temp_num'=>78, 'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$val['writer'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_ref_id'=>$arr_task['customer_ref_id'], 'user_first_name'=>$arr_task['customer_first_name']));
                }
            } else {
                $this->Task->sendEmail(array('to'=>$arr_task['writer_email'], 'temp_num'=>78,'order_ref_id'=>$arr_task['task_ref_id'], 'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$arr_task['writer'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_first_name'=>$arr_task['customer_first_name']));
                $this->Task->sendEmail(array('to'=>$arr_task['customer_email'],'temp_num'=>78,'order_ref_id'=>$arr_task['task_ref_id'],'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$arr_task['customer_first_name'], 'user_email'=>$arr_task['customer_email'],'user_first_name'=>$arr_task['customer_first_name']));
                $this->Task->sendEmail(array('to'=>$arr_task['editor_email'], 'temp_num'=>79,'order_ref_id'=>$arr_task['task_ref_id'], 'website_url'=>$_SERVER['SERVER_NAME'], 'user_screen_name'=>$arr_task['editor'], 'order_ref_id'=>$arr_task['task_ref_id'], 'user_first_name'=>$arr_task['customer_first_name']));
            }
		}
		else {
            Message::addError($this->Task->getError());
        }

        Message::addMessage(Utilities::getLabel('S_You_Have_Taken_This_Editing_Job'));

        redirectUser(generateUrl('task','order_editing', array($arr_task['task_id'])));
    }

    /* Function to reaassign editor task */
    public function reassign_editing_task($tid)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }

        $tid = intval($tid);
        if ($tid < 1 ) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($tid);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isEditor() && $arr_task['task_editor_id']  != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_You_Are_Not_An_Editor'));
            redirectUser(generateUrl('user', 'dashboard'));
        }
        if (in_array($arr_task['task_status'], array(0, 1, 3,4)) ) {
            die(Utilities::getLabel('E_Invalid_request'));
            redirectUser(generateUrl('user', 'dashboard'));
        }

        if ($arr_task['task_editor_id']  != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
            redirectUser(generateUrl('user', 'dashboard'));
        }


        if ($this->Task->reassign_editing_task($tid, User::getLoggedUserAttribute('user_id'), $arr_task['task_ref_id'])) {
        }  
            
            $arr = array(
                    'to'=>CONF_ADMIN_EMAIL_ID,
                    'temp_num'=>70,
                    'user_first_name'=>	'Administrator',
                    'user_screen_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'user_request'=>$post['task_desc_cancel_req'],
                    'order_page_link'=>getUrlScheme().'/manager/orders/order_details/'.$arr_task['task_id']
                    );
            $param = array(
                    'to'=>$arr_task['editor_email'],
                    'temp_num'=>69,
                    'user_first_name'=>	$arr_task['customer_first_name'],
                    'user_screen_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'user_request'=>$post['task_desc_cancel_req'],
                    'order_page_link'=>generateAbsoluteUrl('task','order_editor', array($arr_task['task_id']))
                    );
            $this->Task->sendEmail($arr);
            $this->Task->sendEmail($param);
            
            Message::addMessage(Utilities::getLabel('S_YOU_HAVE_REASSIGNED_EDITING_TASK'));
            redirectUser(generateUrl('task','editing'));

    }
    
    public function cancel_order_request_setup()
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        //if (User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();

        $post['task_id'] = intval($post['task_id']);
        if ($post['task_id'] < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($post['task_id']);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //
        if ($arr_task['task_writer_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->Task->cancelOrderRequest($post)) {
            Message::addErrorMessage($this->Task->getError());
        } else {
            $arr = array(
                    'to'=>CONF_ADMIN_EMAIL_ID,
                    'temp_num'=>57,
                    'user_first_name'=>	'Administrator',
                    'user_screen_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'user_request'=>$post['task_desc_cancel_req'],
                    'order_page_link'=>getUrlScheme().'/manager/orders/order_details/'.$arr_task['task_id']
                    );
            $param = array(
                    'to'=>$arr_task['customer_email'],
                    'temp_num'=>57,
                    'user_first_name'=>	$arr_task['customer_first_name'],
                    'user_screen_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'user_request'=>$post['task_desc_cancel_req'],
                    'order_page_link'=>generateAbsoluteUrl('task','order_process', array($arr_task['task_id']))
                    );
            $this->Task->sendEmail($arr);
            $this->Task->sendEmail($param);
            /* 			$db = &Syspage::getdb();
                        $rs = $db->query("SELECT * FROM tbl_email_templates WHERE tpl_id = 14");
                        $row_tpl = $db->fetch($rs);
                        $subject = $row_tpl['tpl_subject'];
                        $body 	 = $row_tpl['tpl_body'];

                        $arr_replacements = array(
                            '{website_name}'=>CONF_WEBSITE_NAME,
                            '{website_url}'=>$_SERVER['SERVER_NAME'],
                            '{user_name}'=>User::getLoggedUserAttribute('user_screen_name'),
                            '{user_request}'=>$post['task_desc_cancel_req'],
                            '{order_ref_id}'=>$arr_task['task_ref_id'],
                            '{order_page_link}'=>generateAbsoluteUrl('task','order_process',array($post['task_id']))
                        );

                        foreach ($arr_replacements as $key=>$val){
                            $subject	= str_replace($key, $val, $subject);
                            $body		= str_replace($key, $val, $body);
                        }

                        if(sendMail(CONF_ADMIN_EMAIL_ID, $subject, $body)){
             */                Message::addMessage(Utilities::getLabel('S_Request_Sent_To_Administrator'));
            /* }else{
                Message::addErrorMessage('Failed to Send Email to Administrator!');
            }	 */
        }
        redirectUser(generateUrl('task', 'order_process', array($post['task_id'])));
    }

    private function getCancelOrderReqFrm()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $frm = new Form('frmcancelorderreq');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        //$frm->setLeftColumnProperties('width="40%"');
        $frm->setAction(generateUrl('task', 'cancel_order_request_setup'));

        $frm->setJsErrorDisplay('afterfield');


        $fld = $frm->addTextArea(Utilities::getLabel('L_Description_for_Cancel_Order'), 'task_desc_cancel_req', '', 'task_desc_cancel_req');
        $fld->requirements()->setRequired();
        $frm->addHTML('', '', '<div class="gap"></div>');

        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Send_Request'), '');

        return $frm;
    }
    
    /* Function send editing notes to writer */
    public function send_editing_notes($tid)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        //if (User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $tid = intval($tid);
        if ($tid < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($tid);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //
        if ($arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getSendEditingNotesFrm();
        $frm->addHiddenField('', 'task_id', $task_id);

        $this->set('frm', $frm);
        $this->set('arr_task', $arr_task);

        $this->_template->render(false, false);
    }

    public function send_editing_notes_setup()
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        //if (User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();

        $post['task_id'] = intval($post['task_id']);
        if ($post['task_id'] < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($post['task_id']);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //
        if ($arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->Task->sendEditingNotes($post)) {
            Message::addErrorMessage($this->Task->getError());
        } else {
            $arr = array(
                    'to'=>CONF_ADMIN_EMAIL_ID,
                    'temp_num'=>71,
                    'user_screen_name'=>'Administrator',
                    //'user_screen_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'editor_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'editor_notes'=>$post['task_editor_notes'],
                    'order_page_link'=>getUrlScheme().'/manager/orders/order_details/'.$arr_task['task_id']
                    );
            $param = array(
                    'to'=>$arr_task['writer_email'],
                    'temp_num'=>71,
                    'user_screen_name'=>$arr_task['writer'],
                    'order_ref_id'=>$arr_task['task_ref_id'],
                    'editor_name'=>User::getLoggedUserAttribute('user_screen_name'),
                    'editor_notes'=>$post['task_editor_notes'],
                    'editing_fee'=>$post['task_editing_fee'],
                    'order_page_link'=>generateAbsoluteUrl('task','order_process', array($arr_task['task_id']))
                    );
            $this->Task->sendEmail($arr);
            $this->Task->sendEmail($param);
            
            Message::addMessage(Utilities::getLabel('S_Notes_Sent_To_Writer'));
        }
        redirectUser(generateUrl('task', 'order_editing', array($post['task_id'])));
    }

    private function getSendEditingNotesFrm()
    {
        if (!User::isUserLogged()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $frm = new Form('frmsendeditingnotes');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        //$frm->setLeftColumnProperties('width="40%"');
        $frm->setAction(generateUrl('task', 'send_editing_notes_setup'));

        $frm->setJsErrorDisplay('afterfield');


        $fld = $frm->addTextArea(Utilities::getLabel('L_Describe_Writing_Issues'), 'task_editor_notes', '', 'task_editor_notes');
        $fld->requirements()->setRequired();
        
        $fld = $frm->addSelectBox(Utilities::getLabel('L_Editing_Fee'), 'task_editing_fee', $this->common->getEditingFees(), '', '', Utilities::getLabel('L_Select'), 'task_editing_fee');
        $fld->requirements()->setRequired();
        
        $frm->addHTML('', '', '<div class="gap"></div>');

        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Send_Request'), '');

        return $frm;
    }
    
    //To put an order in dispute
    public function dispute_request($task_id)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }


        $frm = $this->getRequestDisputeForm($arr_task);
        $frm->addHiddenField('', 'task_id', $task_id);

        $this->set('frm', $frm);
        $this->set('arr_task', $arr_task);
        
        $this->_template->render(false, false);
    }

    public function dispute_request_setup()
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
 
        $post = Syspage::getPostedVar();
        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        unset($post['btn_submit']);
        $data = $post;
        unset($post);
 
        $arr_task = $this->Task->getTaskDetailsById($task_id);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->Task->Dispute_Request($data)) {
            Message::addErrorMessage($this->Task->getError());
        } else {
            $db = &Syspage::getdb();
            $param = array(
            'to'=>CONF_ADMIN_EMAIL_ID,
            'user_screen_name'=>	'Administrator',
            'user_first_name'=>User::getLoggedUserAttribute('user_screen_name'),
            'dispute_reason'=>$data['task_dispute_reason'],
            'order_ref_id'=>$arr_task['task_ref_id'],
            'user_type'=> User::getLoggedUserAttribute('user_screen_name'),
            'order_page_link'=>generateAbsoluteUrl('manager', 'orders', 'order_details', array($data['task_id'])),
            'temp_num'=>80);

            if ($this->Task->sendEmail($param)) {
                Message::addMessage(Utilities::getLabel('S_Dispute_Notification_Send_Successfully'));
            } else {
                Message::addErrorMessage(Utilities::getLabel('L_Failed_to_Send_Dispute_Email'));
            }

            if (!User::isWriter()) {
            $param = array(
                        'to'=>$arr_task['writer_email'],
                        'temp_num'=>75,
                        'user_screen_name'=>$arr_task['writer'],
                        'user_type'=>	'client',
                        'user_first_name'=>'Client',
                        'user_ref_id'=>$arr_task['customer_ref_id'],
                        'dispute_reason'=>$data['task_dispute_reason'],
                        'order_page_link'=>generateAbsoluteUrl('task', 'order_process', array($data['task_id'])),
                        'order_ref_id'=>$arr_task['task_ref_id'],
                        );

            $this->Task->sendEmail($param);

            }else{
            $arr = array(
                'to'=>$arr_task['customer_email'],
                'temp_num'=>75,
                'user_type'=>	'writer',
                'user_first_name'=>User::getLoggedUserAttribute('user_screen_name'),
                'user_screen_name'=>$arr_task['customer_first_name'],
                'order_ref_id'=>$arr_task['task_ref_id'],
                'dispute_reason'=>$data['task_dispute_reason'],
                'order_page_link'=>generateAbsoluteUrl('task', 'order_process', array($data['task_id'])),
                'order_ref_id'=>$arr_task['task_ref_id'],
                );           
            $this->Task->sendEmail($arr);       
            }
          
        }
        
        redirectUser(generateUrl('task', 'order_process', array($task_id)));
    }

    private function getRequestDisputeForm($arr_task)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $frm = new Form('requestdispute');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        //$frm->setLeftColumnProperties('width="40%"');
        $frm->setAction(generateUrl('task', 'dispute_request_setup'));

        $frm->setJsErrorDisplay('afterfield');
         $frm->addHiddenField('', 'bid_id', $arr_task['bid_id']);

        $fld = $frm->addTextArea(Utilities::getLabel('L_Reason_For_Disputing_Order'), 'task_dispute_reason', '', 'task_dispute_reason');
         $fld->requirements()->setRequired();
        if (!User::isWriter()) { 
        $fld = $frm->addRequiredField(Utilities::getLabel('L_Your_Offer:'), 'task_dispute_offer', '', 'task_dispute_offer' , 'placeholder="' . Utilities::getLabel('L_Your Offer_To_Writer') . '"');
        }
        $frm->addHTML('', '', '<div class="gap"></div>');

        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Send_Request'), '');

        return $frm;
    }

    /* Function to activate cancelled order */
    public function activate_order($tid)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!$this->Task->activate_order($tid)) {
            Message::addError($this->Task->getError());
            redirectUser(generateUrl('task', 'my_orders'));
            return false;
        }
        Message::addMessage(Utilities::getLabel('L_Order_is_activated'));
        redirectUser(generateUrl('task', 'my_orders'));
        return true;
    }

    public function make_task_public()
    {
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $post = Syspage::getPostedVar();

        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);
        if ($arr_task === false || $arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id') || in_array($arr_task['task_status'], array(2,3,4))) {
            die(convertToJson(array('status'=>0, 'msg'=> Utilities::getLabel('E_Invalid_request') )));
        }

        if (!$this->Task->makeTaskPublic($task_id)) {
            die(convertToJson(array('status'=>0, 'msg'=>$this->Task->getError())));
        }

        die(convertToJson(array('status'=>1, 'msg'=>'')));
    }

    public function decline_invitation($task_id)
    {
        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);
        //echo '<pre>' . print_r($arr_task,true) . '</pre>';

        if ($arr_task === false || $arr_task['inv_writer_id'] != User::getLoggedUserAttribute('user_id') || $arr_task['inv_status'] != 0) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->Task->updateInvitationStatus($task_id, 2)) {
            Message::addErrorMessage($this->Task->getError());
        }
        
        $this->Task->makeTaskPublic($task_id);
        
        Message::addMessage(Utilities::getLabel('L_Invitation_declined'));

        redirectUser(generateUrl('bid', 'my_bids', array('invitations')));
    }


    public function revision_request($task_id)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (User::isWriter() && !User::isEditor()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $task_id = intval($task_id);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $arr_task = $this->Task->getTaskDetailsById($task_id);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if ($arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id') && $arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        $frm = $this->getRequestToReviewForm($arr_task);
        $frm->addHiddenField('', 'task_id', $task_id);
        //$frm->addHiddenField('','bid_id',$task_id);

        $this->set('frm', $frm);
        $this->set('arr_task', $arr_task);
        
        $this->_template->render(false, false);
    }


    public function revision_request_setup()
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        //if (User::isWriter()) die( Utilities::getLabel( 'E_Invalid_request' ) );
        $post = Syspage::getPostedVar();
        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        unset($post['btn_submit']);
        $data = $post;
        unset($post);
        //echo '<pre>'.print_r($data,true);exit;
        $arr_task = $this->Task->getTaskDetailsById($task_id);

        if ($arr_task === false) {
            die(Utilities::getLabel('E_Invalid_request'));
        }
        //
        $data['autodebit_on'] = $arr_task['autodebit_on'];
        //echo "<pre>".print_r($data,true)."</pre>";exit;

        if ($arr_task['task_user_id'] != User::getLoggedUserAttribute('user_id') && $arr_task['task_editor_id'] != User::getLoggedUserAttribute('user_id')) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!$this->Task->Revision_Request($data)) {
            Message::addErrorMessage($this->Task->getError());
        } else {
            $db = &Syspage::getdb();
            $param = array('to'=>CONF_ADMIN_EMAIL_ID,'user_screen_name'=>$arr_task['writer'],'user_first_name'=>$arr_task['customer_first_name'],'user_ref_id' => $arr_task['customer_ref_id'],'user_request'=>$data['task_review_description'],'order_ref_id'=>$arr_task['task_ref_id'],'temp_num'=>14);

            /* $rs = $db->query("SELECT * FROM tbl_email_templates WHERE tpl_id = 14");
            $row_tpl = $db->fetch($rs);
            $subject = $row_tpl['tpl_subject'];
            $body 	 = $row_tpl['tpl_body'];

            $arr_replacements = array(
                '{website_name}'=>CONF_WEBSITE_NAME,
                '{website_url}'=>$_SERVER['SERVER_NAME'],
                '{user_screen_name}'=>$arr_task['writer'],
                '{user_first_name}'=>$arr_task['customer_first_name'],
                '{user_ref_id}' => $arr_task['customer_ref_id'],
                '{user_request}'=>$data['task_desc_cancel_req'],
                '{order_ref_id}'=>$arr_task['task_ref_id']
            );

            foreach ($arr_replacements as $key=>$val){
                $subject	= str_replace($key, $val, $subject);
                $body		= str_replace($key, $val, $body);
            } */

            if ($this->Task->sendEmail($param)) {
                Message::addMessage(Utilities::getLabel('S_Email_Notification_For_Debit'));
            } else {
                Message::addErrorMessage(Utilities::getLabel('L_Failed_to_Send_Email_to_Administrator'));
            }

            $param = array(
                        'to'=>$arr_task['writer_email'],
                        'temp_num'=>40,'user_screen_name'=>$arr_task['writer'],
                        'user_first_name'=>$arr_task['customer_first_name'],
                        'user_ref_id'=>$arr_task['customer_ref_id'],
                        'user_request'=>$data['task_review_description'],
                        'request_time'=>'24hrs','order_ref_id'=>$arr_task['task_ref_id']
                        );

            $this->Task->sendEmail($param);
        }
        redirectUser(generateUrl('task', 'order_process', array($task_id)));
    }

    private function getRequestToReviewForm($arr_task)
    {
        if (!User::isUserLogged()) {
            Message::addErrorMessage(Utilities::getLabel('E_Log_in_To_Continue'));
            redirectUser(generateUrl('user', 'signin'));
        }
        $frm = new Form('requesttoreview');

        $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
        $frm->setTableProperties('width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->captionInSameCell(false);
        //$frm->setLeftColumnProperties('width="40%"');
        $frm->setAction(generateUrl('task', 'revision_request_setup'));

        $frm->setJsErrorDisplay('afterfield');
        //$frm->addHiddenField('','autodebit_on',$arr_task['autodebit_on'],'autodebit_on');
        $frm->addHiddenField('', 'bid_id', $arr_task['bid_id']);

        //$fld = $frm->addTextArea('Description to request','task_review_description','','task_review_description');
        $fld = $frm->addTextArea(Utilities::getLabel('L_Describe_in_detail_what_to_revise'), 'task_review_description', '', 'task_review_description');
        $fld->requirements()->setRequired();
        /*if (strtotime($arr_task['task_due_date']) <= time()) {
        $fld_deadline = $frm->addSelectBox('Deadline','task_due_date',array('3 hour'=>'3 hours','4 hour'=>'4 hours','5 hour'=>'5 hours', '6 hour'=>'6 hours', '8 hour'=>'8 hours','10 hour'=>'10 hours', '12 hour'=>'12 hours','16 hour'=>'16 hours','24 hour'=>'1 Day','2 day'=>'2 Days','3 day'=>'3 Days','4 day'=>'4 Days','5 day'=>'5 Days','6 day'=>'6 Days','7 day'=>'7 Days','10 day'=>'10 Days','14 day'=>'14 Days','21 day'=>'21 Days','28 day'=>'28 Days'),'','','Select Deadline','task_due_date'); 
        }*/
        
        $fld_deadline = $frm->addSelectBox('Deadline','revision_add_time',array('1 hour'=>'1 hour','2 hour'=>'2 hours','3 hour'=>'3 hours','4 hour'=>'4 hours','5 hour'=>'5 hours', '6 hour'=>'6 hours', '8 hour'=>'8 hours','10 hour'=>'10 hours', '12 hour'=>'12 hours','16 hour'=>'16 hours','24 hour'=>'1 Day','2 day'=>'2 Days','3 day'=>'3 Days','4 day'=>'4 Days','5 day'=>'5 Days','6 day'=>'6 Days','7 day'=>'7 Days','10 day'=>'10 Days','14 day'=>'14 Days','21 day'=>'21 Days','28 day'=>'28 Days'),'','','Select Deadline','revision_add_time'); 
        $fld_deadline->requirements()->setRequired();
        $frm->addHTML('', '', '<div class="gap"></div>');

        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Send_Request'), '');

        return $frm;
    }

	public function markOrderCompleteByWtr() {
        $post = Syspage::getPostedVar();
        $task_id = intval($post['task_id']);
        if ($task_id < 1) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!User::isUserLogged()) {
            redirectUser(generateUrl('user', 'signin'));
        }

        if (!User::isWriter()) {
            die(Utilities::getLabel('E_Invalid_request'));
        }

        if (!User::isUserActive()) {
            Message::addErrorMessage(Utilities::getLabel('E_User_Deactivate_Notification'));
            redirectUser(generateUrl('user', 'signin'));
        }
        if (!$this->Task->taskFinishedFromWriter($task_id)) {
            die('<div class="popup_msg failure">'.$this->Task->getError().'</div>');
        }
        die('<div class="popup_msg success">' . Utilities::getLabel('L_Order_is_marked_as_finished_by_you') . '</div>');
    }

    public function test()
    {
        echo Utilities::getLabel('L_Hello');

        while (1) {
            echo Utilities::getLabel('L_In_loop') . '\n';
        }
        $this->_template->render();
    }

    /* Cron functions ---------------------------------------------------------------*/

    public function update_order_status()
    {
        $this->Task->updateTaskStatus();
    }

    public function send_notification_to_cust_about_upoaled_file()
    {           // function created for those orders where 24hrs are completed after writer has uploaded project files.

        $data = $this->Task->getLast24hrs();

        foreach ($data as $key=>$val) {
            $arr_task = $this->Task->getTaskDetailsById($val['task_id']);
            $arr_email = array(
                            'temp_num'=>60,
                            'to'=>$arr_task['customer_email'],
                            'order_ref_id'=>$arr_task['task_ref_id'],
                            'user_first_name'=>$arr_task['customer_first_name']
                            );

            $this->Task->sendEmail($arr_email);
        }
    }

    public function extend_autodebit_time_24hrs()
    {
        $row = $this->Task->get_orders_after_request_24hrs();
        foreach ($row as $key=>$val) {
            if (!$this->Task->getExtend24hrs($val['task_id'], $row['bid_id'])) {
                return false;
            } else {
                $arr_task = $this->Task->getTaskDetailsById($val['task_id']);

                $arr_email = array(
                            'temp_num'=>61,
                            'to'=>$arr_task['writer_email'],
                            'order_ref_id'=>$arr_task['task_ref_id'],
                            'user_screen_name'=>$arr_task['writer']
                            );

                $this->Task->sendEmail($arr_email);
            }
        }
    }

    public function latest_orders()
    {
        if (User::isUserLogged()) {
            if (User::isWriter()) {
                redirectUser(generateUrl('task', 'orders'));
            } else {
                redirectUser(generateUrl('user', 'dashboard'));
            }
        }

        $post = Syspage::getPostedVar();
        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }


        $data = $post;
        $arr_orders = $this->Task->OrdersListing($data, $page);


        $frm = $this->getSearchForm($arr_orders, $page);
        $frm->fill($post);
        $this->set('frm', $frm);
        $this->set('paper_types', $this->common->getPaperTypes());
        $this->set('arr_orders', $arr_orders);


        $latest_content = Cmsblock::getContent('latest_writer_1', 'content');
        $this->set('page_content', $latest_content);

        $latest_title = Cmsblock::getContent('latest_writer_1', 'title');
        $this->set('page_title', $latest_title);

        $Meta = new Meta();
        $meta = $Meta->getMetaById(3);

        $arr_listing=array('meta_title'=>$meta['meta_title'],
        'meta_keywords'=>$meta['meta_keywords'],
        'meta_description'=>$meta['meta_description']);

        $this->set('arr_listing', $arr_listing);

        $this->_template->render();
    }
    
    public function solutions()
    {
        $post = Syspage::getPostedVar();

        $page = (intval($post['page']) > 0) ? intval($post['page']) : 1 ;
        $uri=$_SERVER['REQUEST_URI'];
        $arr=explode('/', $uri);

        $data = $post;
        $arr_orders = $this->Task->getSolutionsOrders($data, $page);


        $frm = $this->getSearchForm($arr_orders, $page);
        $frm->fill($post);
        $this->set('frm', $frm);
        $this->set('paper_types', $this->common->getPaperTypes());
        $this->set('arr_orders', $arr_orders);


        $latest_content = Cmsblock::getContent('order_solutions', 'content');
        $this->set('page_content', $latest_content);

        $latest_title = Cmsblock::getContent('order_solutions', 'title');
        $this->set('page_title', $latest_title);

        $Meta = new Meta();
        $meta = $Meta->getMetaById(9);

        $arr_listing=array('meta_title'=>$meta['meta_title'],
        'meta_keywords'=>$meta['meta_keywords'],
        'meta_description'=>$meta['meta_description']);

        $this->set('arr_listing', $arr_listing);

        $this->_template->render();
    }

    /* Function to show solutions page */
    public function solution($task_ref_id )
    {

        $uri=$_SERVER['REQUEST_URI'];
        $arr=explode('/', $uri);
        //$arr[2] = str_replace('-', '.', $arr[2]);
        $arr_task_id=$this->Task->getTaskIdByTopic($arr[2]);
        $task_id=$arr_task_id['task_id'];
        if (empty($task_id)) {
            Message::addErrorMessage(Utilities::getLabel('E_Solution_Does_Not_Exists'));
            redirectUser(generateUrl('solutions'));
        }
        

        //echo $task_id;
        $arr_task = $this->Task->getTaskDetailsById($task_id);
        //echo '<pre>'. print_r($arr_task,true);exit;


        $arr_bids = $this->bid->getOrderBids($task_id);

        if (count($arr_bids) > 0) {
            foreach ($arr_bids as $key=>$ele) {
                $arr_bids[$key]['bidder_rating']	= $this->ratings->getAvgUserRating($ele['bid_user_id']);
                $arr_bids[$key]['orders_completed']	= $this->Order->getUserCompletedOrders($ele['bid_user_id']);
            }
        }

        $files = $this->Task->getOrderFiles($task_id, 1);

        $this->set('files', $files);
        $this->set('order', $arr_task);
        $this->set('arr_bids', $arr_bids);
        
        $meta_data = array(
		'meta_title'=>$arr_task['task_topic']. ' ' .$arr_task['paptype_name']. ' Help',
		'meta_description'=>$arr_task['task_details'],
		);
		$this->set('arr_listing', $meta_data);


        $this->_template->render();
    }


    public function search_orders()
    {
        $post = Syspage::getPostedVar();
        $page = intval($post['page']);
        if ($page < 1) {
            $page = 1;
        }

        $data = $post;
        $arr_orders = $this->Task->getLatestOrders($data, $page);

        //$frm = $this->getSearchForm($arr_orders,$page);
        //$frm->fill($post);
        $this->set('filter', $post['filter']);
        $this->set('filter_value', $post['filter_value']);
        $this->set('arr_orders', $arr_orders);
        $this->_template->render(false, false);
    }

    public function request_writer($user_id)
    {
        $post = Syspage::getPostedVar();
        if (User::isUserLogged()) {
            redirectUser(generateUrl('user', 'dashboard'));
        }

        $user_id = intval($user_id);

        $frm = $this->userController->getLoginForm();
        if ($user_id > 0) {
            $fld = $frm->getField('invite_user_id');
            $fld->value = $user_id;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            unset($post['btn_login']);
            $frm->fill($post);
        }

        $this->set('frm', $frm);
        //$this->set('status', $task_id);
        $this->_template->render(false, false);
    }
    
	public function remove_additional_material()
	{
        if (!User::isUserLogged()) {
			die(convertToJson(array('status'=>0,'msg'=>Utilities::getLabel('E_Invalid_request'))));
        }
		$post = Syspage::getPostedVar();
        $file_id = intval($post['file_id']);
        $task_id = intval($post['task_id']);

		if($file_id < 1 || $task_id < 1){
			die(convertToJson(array('status'=>0,'msg'=>Utilities::getLabel('E_Invalid_request'))));
		}

		$db = Syspage::getdb();
		$srch = new SearchBase('tbl_tasks');
		$srch->addCondition('task_id', '=', $task_id);
		$srch->addCondition('task_user_id', '=', User::getLoggedUserAttribute('user_id'));
		$srch->addFld('task_status');
		$rs = $srch->getResultSet();

		$row = $db->fetch($rs);

		if(!$row){
			die(convertToJson(array('status'=>0,'msg'=>Utilities::getLabel('E_Invalid_request'))));
		}

		$srch = new SearchBase('tbl_files');

		$srch->addCondition('file_task_id', '=', $task_id);
		$srch->addCondition('file_id', '=', $file_id);
		$srch->addFld('file_name');
		$rs = $srch->getResultSet();

		$file = $db->fetch($rs);
		if(!$row){
			die(convertToJson(array('status'=>0,'msg'=>Utilities::getLabel('E_Invalid_request'))));
		}

		if (!file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $file['file_name'])) {
			die(convertToJson(array('status'=>0,'msg'=>Utilities::getLabel('E_File_does_not_exist.'))));
		}

		$db->deleteRecords('tbl_files', array('smt'=>'file_task_id = ? and file_id = ?', 'vals'=>array($task_id, $file_id)));

		unlink(CONF_INSTALLATION_PATH . 'user-uploads/' . $file['file_name']);

		die(convertToJson(array('status'=>1,'msg'=>Utilities::getLabel('E_File_Removed_Successfully.'))));
	}
}
